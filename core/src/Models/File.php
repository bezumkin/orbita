<?php

namespace App\Models;

use App\Services\CloudStorage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Ramsey\Uuid\Uuid;
use Vesp\Services\Filesystem;

/**
 * @property string $uuid
 * @property ?bool $temporary
 *
 * @property-read TopicFile[] $topicFiles
 * @property-read CommentFile[] $commentFiles
 * @property-read PageFile[] $pageFiles
 * @property-read Video $video
 */
class File extends \Vesp\Models\File
{
    protected $guarded = ['created_at', 'updated_at'];
    protected $casts = [
        'temporary' => 'bool',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $model) {
            if (!$model->uuid) {
                $model->uuid = Uuid::uuid4();
            }
        });
    }

    public function getFilesystem(): Filesystem
    {
        if (!$this->filesystem && getenv('S3_ENABLED')) {
            $this->filesystem = new CloudStorage();
        }

        return parent::getFilesystem();
    }

    public function topicFiles(): HasMany
    {
        return $this->hasMany(TopicFile::class);
    }

    public function commentFiles(): HasMany
    {
        return $this->hasMany(CommentFile::class);
    }

    public function pageFiles(): HasMany
    {
        return $this->hasMany(PageFile::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(Video::class);
    }
}