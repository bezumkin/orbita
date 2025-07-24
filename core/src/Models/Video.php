<?php

namespace App\Models;

use App\Services\Log;
use App\Services\Socket;
use App\Services\TempStorage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property-read VideoQuality[] $processedQualities
 * @property-read VideoUser[] $videoUsers
 * @property-read TopicFile[] $topicFiles
 * @property-read PageFile[] $pageFiles
 */
class Video extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['title', 'description', 'active'];
    protected $casts = [
        'active' => 'boolean',
        'chapters' => 'array',
        'thumbnails' => 'array',
        'processed' => 'boolean',
        'moved' => 'boolean',
        'processed_at' => 'datetime',
        'moved_at' => 'datetime',
    ];
    private array $transcodeStatus = [];

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

    public function processedQualities(): HasMany
    {
        return $this->hasMany(VideoQuality::class)
            ->where('processed', true);
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
        $this->progress = 0;
        $this->processed = null;
        $this->error = null;
        $this->save();

        $media = new TempStorage();
        try {
            $props = $media->getVideoProperties($this->id);
            $qualities = $media::getQualities($props['width'], $props['height'], $props['bitrate']);

            if (!$this->file->height || !$this->file->width) {
                $this->file->width = $props['width'];
                $this->file->height = $props['height'];
                $this->file->save();
            }

            if (!$this->duration) {
                $this->duration = $props['duration'];
            }

            if (!$this->image && $image = $media->getPreview($this->id)) {
                $this->image_id = $image->id;
            }
        } catch (Throwable $e) {
            $this->processed = false;
            $this->error = $e->getMessage();
            $this->save();

            return;
        }

        if (!$this->audio && getenv('EXTRACT_VIDEO_AUDIO_ENABLED')) {
            try {
                if (PHP_SAPI === 'cli') {
                    $time = microtime(true);
                    echo 'Extracting audio... ';
                }
                $audio = $media->getAudio($this->id);
                $this->audio_id = $audio->id;
                if (PHP_SAPI === 'cli') {
                    echo 'Done in ' . microtime(true) - $time . ' s.' . PHP_EOL;
                }
                $this->save();
                $this->refresh()->updateContentBlocks();
            } catch (Throwable $e) {
                Log::error($e);
            }
        }

        $steps = count($qualities);
        $stepSize = floor(100 / $steps);
        $startTime = time();
        $step = 1;

        if (PHP_SAPI === 'cli') {
            echo PHP_EOL;
        }
        /** @var \App\Models\VideoQuality $quality */
        foreach ($qualities as $quality) {
            try {
                /** @var VideoQuality $oldQuality */
                if ($oldQuality = $this->qualities()->where('quality', $quality->quality)->first()) {
                    $oldQuality->delete();
                }

                $quality->video_id = $this->id;
                $quality->file_id = TempStorage::getFakeFile($this->file->title, 'video/mp2t')->id;
                $quality->save();

                $stepTime = time();
                $callback = function ($percentage) use ($quality, $startTime, $stepTime, $step, $stepSize) {
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

                    $status = number_format($percentage, 2);
                    if ($this->transcodeStatus[$quality->quality] !== $status) {
                        $this->transcodeStatus[$quality->quality] = $status;

                        $quality->progress = $status;
                        $quality->save();
                        $this->progress = number_format($progress, 2);
                        $this->save();

                        $this->sendInfoToSocket();
                    }
                };

                $this->transcodeStatus[$quality->quality] = 0;
                $media->transcode($quality, $callback);
                $quality->finishProcessing();
                $step++;
                if (PHP_SAPI === 'cli') {
                    echo PHP_EOL;
                }
            } catch (Throwable $e) {
                $quality?->delete();
                $this->processed = false;
                $this->error = $e->getMessage();
                $this->save();

                return;
            }
        }

        if (!$this->thumbnail && getenv('EXTRACT_VIDEO_THUMBNAILS_ENABLED')) {
            try {
                if (PHP_SAPI === 'cli') {
                    $time = microtime(true);
                    echo 'Extracting thumbnails... ';
                }
                $thumbnail = $media->getThumbnail($this->id);
                $this->thumbnail_id = $thumbnail->id;
                if (PHP_SAPI === 'cli') {
                    echo 'Done in ' . microtime(true) - $time . ' s.' . PHP_EOL;
                }
            } catch (Throwable $e) {
                Log::error($e);
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
        $this->updateContentBlocks();

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
        $manifest = ['#EXTM3U'];

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

        [$width, $height] = array_map('intval', explode('x', getenv('EXTRACT_VIDEO_THUMBNAILS_SIZE') ?: '213x120'));
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

        foreach ($this->qualities()->cursor() as $quality) {
            $quality->delete();
        }
        $this->file->delete();
        $this->image?->delete();
        $this->audio?->delete();
        $this->thumbnail?->delete();

        return parent::delete();
    }

    protected function sendInfoToSocket(): void
    {
        $data = $this
            ->fresh([
                'file:id,uuid,width,height,size,updated_at',
                'image:id,uuid,width,height,size,updated_at',
                'qualities',
                'qualities.file:id,uuid,width,height,size,updated_at',
            ])
            ?->toArray();

        if (!empty($data['qualities'])) {
            $data['processed_qualities'] = [];
            foreach ($data['qualities'] as $quality) {
                if ($quality['processed']) {
                    $data['processed_qualities'][] = $quality['quality'];
                }
            }
        }

        Socket::send('transcode', $data, 'videos');
    }

    public function updateContentBlocks(): void
    {
        /** @var TopicFile $topicFile */
        foreach ($this->topicFiles()->cursor() as $topicFile) {
            $topicFile->topic->content = $this->processContentBlocks($topicFile->topic->content);
            $topicFile->topic->save();
        }

        /** @var PageFile $pageFile */
        foreach ($this->pageFiles()->cursor() as $pageFile) {
            $pageFile->page->content = $this->processContentBlocks($pageFile->page->content);
            $pageFile->page->save();
        }
    }

    protected function processContentBlocks(array $content): array
    {
        foreach ($content['blocks'] as $idx => $block) {
            if ($block['type'] === 'video' && $block['data']['uuid'] === $this->id) {
                $data = [
                    'id' => $this->file_id,
                    'uuid' => $this->id,
                    'duration' => $this->duration,
                    'size' => $this->file->size,
                    'width' => $this->file->width,
                    'height' => $this->file->height,
                    'moved' => $this->moved,
                    'updated_at' => is_scalar($this->updated_at) ? $this->updated_at : $this->updated_at->toJSON(),
                ];
                if ($this->audio) {
                    $data['audio'] = $this->audio->uuid;
                    $data['audio_size'] = $this->audio->size;
                }

                $content['blocks'][$idx]['data'] = $data;
            }
        }

        return $content;
    }
}