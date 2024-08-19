<?php

namespace App\Models;

use App\Services\Log;
use App\Services\Socket;
use App\Services\TempStorage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Streaming\Format\X264;
use Streaming\Representation;
use Throwable;

/**
 * @property string $id
 * @property int $file_id
 * @property ?int $image_id
 * @property ?int $audio_id
 * @property ?int $thumbnail_id
 * @property ?string $title
 * @property ?string $description
 * @property ?int $duration
 * @property bool $active
 * @property bool $processed
 * @property bool $moved
 * @property int $progress
 * @property string $manifest
 * @property ?array $chapters
 * @property ?array $thumbnails
 * @property string $error
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $processed_at
 * @property Carbon $moved_at
 *
 * @property-read File $file
 * @property-read File $image
 * @property-read File $audio
 * @property-read File $thumbnail
 * @property-read VideoQuality[] $qualities
 * @property-read VideoUser[] $videoUsers
 * @property-read TopicFile[] $topicFiles
 * @property-read PageFile[] $pageFiles
 */
class Video extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['title', 'description', 'duration', 'progress', 'active', 'processed_at'];
    protected $casts = [
        'active' => 'boolean',
        'chapters' => 'array',
        'thumbnails' => 'array',
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

    public function audio(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function thumbnail(): BelongsTo
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

    public function topicFiles(): HasMany
    {
        return $this->hasMany(TopicFile::class, 'file_id', 'file_id');
    }

    public function pageFiles(): HasMany
    {
        return $this->hasMany(PageFile::class, 'file_id', 'file_id');
    }

    public function transcode(): void
    {
        $media = new TempStorage();

        try {
            if (!$this->image && $image = $media->getPreview($this->id)) {
                $this->image_id = $image->id;
                $this->save();

                if (!$this->file->height || !$this->file->width) {
                    $this->file->height = $image->height;
                    $this->file->width = $image->width;
                    $this->file->save();
                }
            }

            $representations = $media::getQualities($this->file->width, $this->file->height);

            if (!$this->audio && getenv('EXTRACT_VIDEO_AUDIO_ENABLED')) {
                if (PHP_SAPI === 'cli') {
                    $time = microtime(true);
                    echo 'Extracting audio... ';
                }
                $audio = $media->getAudio($this->id);
                $this->audio_id = $audio->id;
                $this->save();
                if (PHP_SAPI === 'cli') {
                    echo 'Done in ' . microtime(true) - $time . ' s.' . PHP_EOL;
                }
            }
            if (!$this->thumbnail && getenv('EXTRACT_VIDEO_THUMBNAILS_ENABLED')) {
                if (PHP_SAPI === 'cli') {
                    $time = microtime(true);
                    echo 'Extracting thumbnails... ';
                }
                $thumbnail = $media->getThumbnail($this->id, $representations[0]);
                $this->thumbnail_id = $thumbnail->id;
                $this->save();
                if (PHP_SAPI === 'cli') {
                    echo 'Done in ' . microtime(true) - $time . ' s.' . PHP_EOL;
                }
            }
        } catch (Throwable $e) {
            $this->processed = false;
            $this->error = $e->getMessage();
            $this->save();

            return;
        }

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
                $quality->file_id = TempStorage::getFakeFile($this->file->title, 'video/mp2t')->id;
                $quality->quality = $rep->getHeight();
                $quality->save();

                $format->on(
                    'progress',
                    function ($video, $format, $percentage) use ($quality, $startTime, $stepTime, $step, $stepSize) {
                        $progress = ($stepSize / 100 * $percentage) + ($step - 1) * $stepSize;
                        $round = ceil($progress / 4);
                        if (PHP_SAPI === 'cli') {
                            echo sprintf(
                                "\r%s %s => %s%%, %s (%s) [%s%s]",
                                $this->id,
                                $quality->quality . 'p',
                                number_format($progress, 2),
                                Carbon::now()->subSeconds($stepTime)->timezone('UTC')->format('H:i:s'),
                                Carbon::now()->subSeconds($startTime)->timezone('UTC')->format('H:i:s'),
                                str_repeat('#', $round),
                                str_repeat('-', (25 - $round))
                            );
                        }

                        $quality->progress = number_format($percentage, 2);
                        $quality->save();
                        $this->progress = number_format($progress, 2);
                        $this->save();

                        $this->sendInfoToSocket();
                    }
                );

                $info = $media->transcode($this->id, $format, $rep);
                if (!$this->duration) {
                    $this->duration = (int)($info['video']['format']['duration'] ?? 0);
                    $this->save();
                }
                $step++;
                $quality->finishProcessing();
                if (PHP_SAPI === 'cli') {
                    echo PHP_EOL;
                }
            } catch (Throwable $e) {
                $this->processed = false;
                $this->error = $e->getMessage();
                $this->save();

                return;
            }
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
        $this->sendInfoToSocket();

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

        $this->sendInfoToSocket();
    }

    protected function stopProcessing(): void
    {
        exec("ps ax | grep [f]fmpeg | grep $this->id", $processes);
        if (($process = current($processes)) && preg_match('#\d+#', trim($process), $matches)) {
            exec("kill $matches[0]");
        }
    }

    public function getManifest(): string
    {
        $manifest = [
            '#EXTM3U',
            '#EXT-X-VERSION:7',
            '#EXT-X-PLAYLIST-TYPE:VOD',
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

    public function getThumbnails(): ?array
    {
        if (!$thumbnail = $this->thumbnail) {
            return null;
        }
        /** @var VideoQuality $quality */
        if (!$quality = $this->qualities()->orderBy('quality')->first()) {
            return null;
        }

        $tmp = explode('x', $quality->resolution);
        $width = $tmp[0] / 2;
        $height = $tmp[1] / 2;

        $storyboard = [
            'file' => $thumbnail->only('id', 'uuid', 'updated_at'),
            'tileWidth' => $width,
            'tileHeight' => $height,
            'tiles' => [],
        ];

        $chunk = ceil($this->duration / 3600) * 5; // every 25 seconds for 5 hours video
        $x = $y = 0;
        for ($i = 0; $i < $this->duration; $i += $chunk) {
            $storyboard['tiles'][] = ['startTime' => $i, 'x' => $x, 'y' => $y];
            $x += $width;
            if ($x >= $thumbnail->width) {
                $x = 0;
                $y += $height;
            }
        }

        return $storyboard;
    }

    public function delete(): bool
    {
        $this->stopProcessing();
        try {
            $fs = (new TempStorage())->getBaseFilesystem();
            if ($fs->directoryExists($this->id)) {
                $fs->deleteDirectory($this->id);
            }
        } catch (Throwable $e) {
            Log::error($e);
        }

        foreach ($this->qualities as $quality) {
            $quality->file->delete();
        }
        $this->file->delete();
        if ($this->image) {
            $this->image->delete();
        }
        if ($this->audio) {
            $this->audio->delete();
        }
        if ($this->thumbnail) {
            $this->thumbnail->delete();
        }

        return parent::delete();
    }

    public function getProcessedQualitiesAttribute(): array
    {
        return $this->qualities()->where('processed', true)->pluck('quality')->toArray();
    }

    protected function sendInfoToSocket(): void
    {
        $data = $this
            ->fresh([
                'file:id,uuid,width,height,size,updated_at',
                'image:id,uuid,width,height,size,updated_at',
                'qualities', 'qualities.file:id,uuid,width,height,size,updated_at',
            ])
            ?->toArray();

        Socket::send('transcode', $data);
    }
}