<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;

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
}