<?php

namespace App\Models;

use App\Services\Log;
use App\Services\Socket;
use App\Services\TempStorage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;
use Streaming\Format\X264;
use Streaming\Representation;

/**
 * @property string $id
 * @property int $file_id
 * @property ?int $image_id
 * @property ?string $title
 * @property ?string $description
 * @property ?int $duration
 * @property bool $active
 * @property bool $processed
 * @property bool $moved
 * @property int $progress
 * @property string $manifest
 * @property array $chapters
 * @property string $error
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $processed_at
 * @property Carbon $moved_at
 *
 * @property-read File $file
 * @property-read File $image
 * @property-read VideoQuality[] $qualities
 * @property-read VideoUser[] $videoUsers
 */
class Video extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['title', 'description', 'duration', 'progress', 'active', 'processed_at'];
    protected $casts = [
        'active' => 'boolean',
        'processed' => 'boolean',
        'moved' => 'boolean',
        'processed_at' => 'datetime',
        'moved_at' => 'datetime',
    ];
    protected $appends = ['processed_qualities'];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function qualities(): HasMany
    {
        return $this->hasMany(VideoQuality::class);
    }

    public function videoUsers(): HasMany
    {
        return $this->hasMany(VideoUser::class);
    }

    public function transcode(): void
    {
        $media = new TempStorage();

        if (!$this->image && $image = $media->getPreview($this->id)) {
            $stream = new Stream(fopen('data:image/jpeg;base64,' . base64_encode($image), 'rb'));
            $file = new File();
            $file->uploadFile(new UploadedFile($stream, $this->title . '.jpg', 'image/jpeg'));
            $this->image_id = $file->id;
            $this->save();

            if (!$this->file->height || !$this->file->width) {
                $this->file->height = $file->height;
                $this->file->width = $file->width;
                $this->file->save();
            }
        }

        $representations = $media->getQualities($this->file->width, $this->file->height);
        $steps = count($representations);
        $stepSize = floor(100 / $steps);
        $startTime = time();
        $step = 1;

        if (PHP_SAPI === 'cli') {
            echo PHP_EOL;
        }
        /** @var Representation $rep */
        foreach ($representations as $rep) {
            try {
                $stepTime = time();

                $params = ['hls_flags' => 'single_file'];
                if ($preset = getenv('TRANSCODE_PRESET')) {
                    $params['preset'] = strtolower($preset);
                }
                $format = new X264();
                $format->setAdditionalParameters($params);

                if ($oldQuality = $this->qualities()->where('quality', $rep->getHeight())) {
                    $oldQuality->delete();
                }

                $quality = new VideoQuality();
                $quality->video_id = $this->id;
                $quality->file_id = TempStorage::getFakeFile($this->file->title, 'video/mp4')->id;
                $quality->quality = $rep->getHeight();
                $quality->save();

                $format->on(
                    'progress',
                    function ($video, $format, $percentage) use ($quality, $startTime, $stepTime, $step, $stepSize) {
                        $progress = ($stepSize / 100 * $percentage) + ($step - 1) * $stepSize;
                        $round = ceil($progress / 4);
                        $elapsed = date('H:i:s', time() - $stepTime);
                        if (PHP_SAPI === 'cli') {
                            echo sprintf(
                                "\r%s %s => %s%%, %s (%s) [%s%s]",
                                $this->id,
                                $quality->quality . 'p',
                                number_format($progress, 2),
                                $elapsed,
                                date('H:i:s', time() - $startTime),
                                str_repeat('#', $round),
                                str_repeat('-', (25 - $round))
                            );
                        }

                        $quality->progress = number_format($percentage, 2);
                        $quality->save();
                        $this->progress = number_format($progress, 2);
                        $this->save();

                        Socket::send('transcode', $this->toArray());
                    }
                );

                $info = $media->transcode($this->id, $format, $rep);
                if (!$this->duration) {
                    $this->duration = (int)($info['video']['format']['duration'] ?? 0);
                    $this->save();
                }
                $step++;
                $quality->finishProcessing();
            } catch (\Throwable $e) {
                $this->processed = false;
                $this->error = $e->getMessage();
                $this->save();
            }
        }
        if (PHP_SAPI === 'cli') {
            echo PHP_EOL;
        }
        $this->finishProcessing();
    }

    public function getPoster(): ?string
    {
        return $this->image ? $this->image->getFile() : null;
    }

    protected function finishProcessing(): void
    {
        $this->timestamps = false;
        $this->progress = 100;
        $this->processed = true;
        $this->processed_at = time();
        $this->save();
        Socket::send('transcode', $this->toArray());

        $storage = new TempStorage();
        $meta = $storage->getMeta($this->id);
        $tempFs = $storage->getBaseFilesystem();

        $path = $this->file->getFullFilePathAttribute();
        if (str_starts_with($path, getenv('UPLOAD_DIR'))) {
            rename($storage->getFullPath($meta['file']), $path);
        } else {
            $this->file->getFilesystem()
                ->getBaseFilesystem()
                ->writeStream($this->file->getFilePathAttribute(), $tempFs->readStream($meta['file']));
        }
        $tempFs->deleteDirectory($this->id);
        $storage->deleteMeta($this->id);

        $this->manifest = $this->getManifest();
        $this->moved = true;
        $this->moved_at = time();
        $this->save();

        Socket::send('transcode', $this->toArray());
    }

    public function getManifest(): string
    {
        $manifest = [
            '#EXTM3U',
            '#EXT-X-VERSION:7',
        ];

        /** @var VideoQuality $quality */
        foreach ($this->qualities()->orderBy('quality')->cursor() as $quality) {
            if ($quality->processed && $quality->moved) {
                $manifest[] = '#EXT-X-STREAM-INF:BANDWIDTH=' . $quality->bandwidth . ',RESOLUTION=' . $quality->resolution . ',NAME="' . $quality->quality . '"';
                $manifest[] = $this->id . '/' . $quality->quality;
            }
        }

        return implode(PHP_EOL, $manifest);
    }

    public function delete(): bool
    {
        try {
            $fs = (new TempStorage())->getBaseFilesystem();
            if ($fs->directoryExists($this->id)) {
                $fs->deleteDirectory($this->id);
            }
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }

        foreach ($this->qualities as $quality) {
            $quality->file->delete();
        }
        $this->file->delete();
        if ($this->image) {
            $this->image->delete();
        }

        return parent::delete();
    }

    public function getProcessedQualitiesAttribute(): array
    {
        return $this->qualities()->where('processed', true)->pluck('quality')->toArray();
    }
}