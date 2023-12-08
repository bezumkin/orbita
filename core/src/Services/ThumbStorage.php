<?php

namespace App\Services;

use Vesp\Services\Filesystem;

class ThumbStorage extends Filesystem
{
    protected function getRoot(): string
    {
        return rtrim(getenv('CACHE_DIR') ?: sys_get_temp_dir(), '/') . '/thumbnails';
    }
}