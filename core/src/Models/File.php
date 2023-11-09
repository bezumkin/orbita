<?php

namespace App\Models;

use App\Services\CloudStorage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

/**
 * @property string $uuid
 * @property ?bool $temporary
 *
 * @property-read TopicFile[] $topicFiles
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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (getenv('S3_ENABLED')) {
            $this->filesystem = new CloudStorage();
        }
    }

    public function topicFiles(): HasMany
    {
        return $this->hasMany(TopicFile::class);
    }
}