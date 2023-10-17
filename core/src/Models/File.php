<?php

namespace App\Models;

use App\Services\CloudStorage;
use Ramsey\Uuid\Uuid;
use Vesp\Services\Filesystem;

/**
 * @property string $uuid
 */
class File extends \Vesp\Models\File
{
    protected $guarded = ['created_at', 'updated_at'];

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
        $this->filesystem = getenv('S3_ENABLED') ? new CloudStorage() : new Filesystem();
    }
}