<?php

namespace App\Services;

use App\Models\File;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Audio\Mp3;
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

    public static function getQualities(int $srcWidth, int $srcHeight, ?int $maxBitrate = null): array
    {
        $orientation = $srcHeight > $srcWidth ? 'portrait' : 'landscape';

        $result = [];
        $settings = array_map('trim', explode(',', getenv('TRANSCODE_SET') ?: '256x144x128'));
        foreach ($settings as $tmp) {
            if ($orientation === 'portrait') {
                [$dstHeight, $dstWidth, $dstBitrate] = array_map('intval', array_map('trim', explode('x', $tmp)));
                $ratio = $srcHeight / $srcWidth;
                if (round($ratio, 2) !== 1.78) {
                    $dstWidth = round($dstHeight / $ratio);
                }
            } else {
                [$dstWidth, $dstHeight, $dstBitrate] = array_map('intval', array_map('trim', explode('x', $tmp)));
                $ratio = $srcWidth / $srcHeight;
                if (round($ratio, 2) !== 1.78) {
                    $dstHeight = round($dstWidth / $ratio);
                }
            }

            if ($maxBitrate && $dstBitrate > $maxBitrate) {
                $dstBitrate = $maxBitrate;
            }
            if ($srcWidth >= $dstWidth && $srcHeight >= $dstHeight) {
                $result[] = (new Representation())->setKiloBitrate($dstBitrate)->setResize($dstWidth, $dstHeight);
            }
        }
        usort($result, static function ($a, $b) {
            return $a->getWidth() <=> $b->getWidth();
        });

        return $result;
    }

    public function getPreview(string $uuid, ?int $second = 1): File
    {
        if ($meta = $this->getMeta($uuid)) {
            $source = $meta['file'];
        } else {
            $source = $uuid . '/video.mp4';
        }
        if (!$video = $this->ffmpeg->open($this->getFullPath($source))) {
            throw new RuntimeException('Could not load source file ' . $source);
        }

        $frame = $video->frame(TimeCode::fromSeconds($second));
        /** @var String $image */
        $image = $frame->save($this->getFullPath($uuid . '/preview.jpg'), true, true);

        $stream = new Stream(fopen('data:image/jpeg;base64,' . base64_encode($image), 'rb'));
        $file = new File();
        $file->uploadFile(new UploadedFile($stream, 'preview.jpg', 'image/jpeg', $stream->getSize()));

        return $file;
    }

    public function getAudio(string $uuid): File
    {
        if ($meta = $this->getMeta($uuid)) {
            $source = $meta['file'];
        } else {
            $source = $uuid . '/video.mp4';
        }
        if (!$video = $this->ffmpeg->open($this->getFullPath($source))) {
            throw new RuntimeException('Could not load source file ' . $source);
        }

        $path = $this->getFullPath($uuid . '/audio.mp3');
        $video->save(new Mp3(), $path);

        $stream = new Stream(fopen($path, 'rb'));
        $file = new File();
        $file->uploadFile(new UploadedFile($stream, 'audio.mp3', 'audio/mpeg', $stream->getSize()));

        return $file;
    }

    public function getThumbnail(string $uuid, Representation $rep): File
    {
        if ($meta = $this->getMeta($uuid)) {
            $source = $meta['file'];
        } else {
            $source = $uuid . '/video.mp4';
        }
        if (!$video = $this->ffmpeg->open($this->getFullPath($source))) {
            throw new RuntimeException('Could not load source file ' . $source);
        }
        if (!$stream = $video->getStreams()->videos()->first()) {
            throw new RuntimeException('Could not open video stream for ' . $uuid);
        }

        $tileWidth = 10;
        $duration = $stream->get('duration');
        $chunk = ceil($duration / 3600) * 5;
        $totalFrames = $stream->get('nb_frames');
        $framerate = round($totalFrames / $duration);
        $extractEvery = $chunk * $framerate; // extract image every n frames
        $tileHeight = ceil(ceil($totalFrames / $extractEvery) / $tileWidth);

        $width = $rep->getWidth() / 2;
        $height = $rep->getHeight() / 2;

        $path = $this->getFullPath($uuid . '/thumbnails.webp');
        $params = [
            'ffmpeg -y -i '. $this->getFullPath($meta['file']),
            '-loglevel error',
            '-filter_complex',
            '"select=\'not(mod(n,' . $extractEvery . '))\',scale=' . $width . ':' . $height . ',tile=' . $tileWidth . 'x' . $tileHeight . '"',
            '-frames:v 1',
            '-qscale:v 50', // image quality
            '-an',
            $path,
        ];

        $command = implode(' ', $params);
        $output = system($command, $status);
        if ($status) {
            Log::error('Thumbnails error for ' . $uuid, [(string)$output]);
            throw new RuntimeException('Could not generate thumbnails for ' . $uuid);
        }

        $stream = new Stream(fopen($path, 'rb'));
        $file = new File();
        $file->uploadFile(new UploadedFile($stream, 'thumbnails.webp', 'image/webp', $stream->getSize()));

        return $file;
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