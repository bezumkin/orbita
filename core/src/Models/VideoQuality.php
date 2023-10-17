<?php

namespace App\Models;

use App\Models\Traits\CompositeKey;
use App\Services\TempStorage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $quality
 * @property int $video_id
 * @property int $file_id
 * @property float $progress
 * @property bool $processed
 * @property bool $moved
 * @property string $bandwidth
 * @property string $resolution
 * @property string $manifest
 * @property Carbon $created_at
 * @property Carbon $processed_at
 * @property Carbon $moved_at
 *
 * @property-read Video $video
 * @property-read File $file
 */
class VideoQuality extends Model
{
    use CompositeKey;

    public $timestamps = false;
    protected $primaryKey = ['quality', 'video_id'];
    protected $fillable = ['progress', 'processed', 'moved', 'manifest', 'processed_at'];
    protected $casts = [
        'processed' => 'boolean',
        'moved' => 'boolean',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'moved_at' => 'datetime',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function finishProcessing(): void
    {
        $this->progress = 100;
        $this->processed = true;
        $this->processed_at = time();
        $this->save();

        $storage = new TempStorage();
        $basename = $this->quality . '_' . $this->quality . 'p';
        $videoFile = $this->video_id . '/' . $basename . '.m4s';
        $mainFile = $this->video_id . '/' . $this->quality . '.m3u8';
        $manifestFile = $this->video_id . '/' . $basename . '.m3u8';
        $tempFs = $storage->getBaseFilesystem();

        echo get_class($this->file) . PHP_EOL;

        $path = $this->file->getFullFilePathAttribute();
        if (str_starts_with($path, getenv('UPLOAD_DIR'))) {
            $this->file->size = filesize($storage->getFullPath($videoFile));
            rename($storage->getFullPath($videoFile), $path);
        } else {
            $this->file->size = $tempFs->fileSize($videoFile);
            $this->file->getFilesystem()
                ->getBaseFilesystem()
                ->writeStream($this->file->getFilePathAttribute(), $tempFs->readStream($videoFile));
            $tempFs->delete($videoFile);
        }

        $main = explode(PHP_EOL, $tempFs->read($mainFile));
        if (preg_match('#BANDWIDTH=(\d+),RESOLUTION=(\d+x\d+),#', $main[2], $matches)) {
            $this->bandwidth = $matches[1];
            $this->resolution = $matches[2];
            [$this->file->width, $this->file->height] = explode('x', $this->resolution);
        }
        $this->file->save();

        $this->manifest = str_replace($basename . '.m4s', $this->quality, $tempFs->read($manifestFile));

        $tempFs->delete($mainFile);
        $tempFs->delete($manifestFile);

        $this->moved = true;
        $this->moved_at = time();
        $this->save();
    }
}