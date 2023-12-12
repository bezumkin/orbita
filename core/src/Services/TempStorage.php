<?php

namespace App\Services;

use App\Models\File;
use FFMpeg\Coordinate\TimeCode;
use League\Flysystem\StorageAttributes;
use RuntimeException;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;
use Streaming\FFMpeg;
use Streaming\Format\StreamFormat;
use Streaming\Representation;
use Throwable;
use Vesp\Services\Filesystem;

class TempStorage extends Filesystem
{
    protected FFMpeg $ffmpeg;
    protected Redis $redis;
    protected array $meta = [];

    public function __construct()
    {
        parent::__construct();

        $config = [
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'ffmpeg.threads' => 0,
            'timeout' => 0,
        ];

        $logger = Log::getLogger(getenv('LOG_LEVEL') ?: 'error', 'ffmpeg');
        $this->ffmpeg = FFMpeg::create($config, $logger);
        $this->redis = new Redis();
    }

    protected function getRoot(): string
    {
        $dir = implode(DIRECTORY_SEPARATOR, [rtrim(getenv('CACHE_DIR'), DIRECTORY_SEPARATOR), 'video']);
        if (!is_dir($dir) && !mkdir($dir) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $dir;
    }

    public function transcode(string $uuid, StreamFormat $format, Representation $representation): ?array
    {
        if (!$meta = $this->getMeta($uuid)) {
            throw new RuntimeException('Could not load meta file for ' . $uuid);
        }

        $path = $this->getFullPath($uuid);
        $video = $this->ffmpeg->open($this->getFullPath($meta['file']));

        $result = $video->hls()->setFormat($format);
        $result->addRepresentation($representation);
        $result->save($path . '/' . $representation->getHeight());

        $fs = $this->getBaseFilesystem();
        $files = $fs->listContents($uuid);
        /** @var StorageAttributes $file */
        foreach ($files as $file) {
            $path = $file->path();
            if (str_ends_with($path, '%04d.ts')) {
                $fs->move($path, str_replace('_%04d.ts', '.ts', $path));
            } elseif (str_ends_with($path, '.m3u8')) {
                $tmp = $fs->read($path);
                $fs->write($path, str_replace('_%04d.ts', '.ts', $tmp));
            }
        }

        return $result->metadata()->get();
    }

    public function getMeta(string $uuid): ?array
    {
        if (!$uuid || !$data = $this->redis->get('meta-' . $uuid)) {
            return null;
        }
        $this->meta[$uuid] = json_decode($data, true);

        return $this->meta[$uuid];
    }

    public function setMeta(string $uuid, array $data): void
    {
        $this->meta[$uuid] = json_encode($data);
        $this->redis->set('meta-' . $uuid, $this->meta[$uuid]);
    }

    public function deleteMeta(mixed $uuid): void
    {
        $this->redis->del('meta-' . $uuid);
    }

    public function writeFile(string $uuid, array $meta): ?array
    {
        $input = fopen('php://input', 'rb');
        $file = fopen($this->getFullPath($meta['file']), 'ab');
        fseek($file, $meta['offset']);
        while ($data = fread($input, 8192)) {
            if ($written = fwrite($file, $data, 8192)) {
                $meta['offset'] += $written;
            }
        }
        fclose($file);
        fclose($input);

        $this->setMeta($uuid, $meta);

        return $meta;
    }

    public function deleteFile(string $uuid): void
    {
        try {
            $this->getBaseFilesystem()->deleteDirectory($uuid);
            $this->deleteMeta($uuid);
        } catch (Throwable $e) {
            Log::info($e);
        }
    }

    public function getQualities(?int $width = null, ?int $height = null): array
    {
        $result = [];
        $settings = array_map('trim', explode(',', getenv('TRANSCODE_SET') ?: '256x144x128'));
        foreach ($settings as $tmp) {
            [$w, $h, $b] = array_map('trim', explode('x', $tmp));
            $result[] = (new Representation())->setKiloBitrate($b)->setResize($w, $h);
        }

        if ($width && $height) {
            $result = array_filter($result, static function (Representation $rep) use ($width, $height) {
                return $rep->getWidth() <= $width && $rep->getHeight() <= $height;
            });
        } elseif ($height) {
            $result = array_filter($result, static function (Representation $rep) use ($height) {
                return $rep->getHeight() <= $height;
            });
        } elseif ($width) {
            $result = array_filter($result, static function (Representation $rep) use ($width) {
                return $rep->getWidth() <= $width;
            });
        }

        return $result;
    }

    public function getPreview(string $uuid): ?string
    {
        if (!$meta = $this->getMeta($uuid)) {
            throw new RuntimeException('Could not load meta file for ' . $uuid);
        }

        $video = $this->ffmpeg->open($this->getFullPath($meta['file']));

        if ($frame = $video->frame(TimeCode::fromSeconds(1))) {
            $path = $this->getFullPath($uuid . '/preview.jpg');

            return (string)$frame->save($path, true, true);
        }

        return null;
    }

    public static function getFakeFile(string $name, string $type, ?int $size = null): File
    {
        if ($type === 'video/mp2t') {
            $name = preg_replace('/\.\w+$/', '.ts', $name);
        }

        $file = new File();
        $stream = new Stream(fopen('data:image/png;base64, ', 'rb'));
        $data = new UploadedFile($stream, $name, $type);
        $file->uploadFile($data);
        $file->title = $name;
        $file->type = $type;
        $file->size = $size;
        $file->save();

        return $file;
    }
}