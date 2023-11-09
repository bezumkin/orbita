<?php

namespace App\Controllers;

use App\Models\Video;

class Poster extends Image
{
    protected function getPrimaryKey(): null|string|array
    {
        $uuid = $this->getProperty('uuid');
        /** @var Video $video */
        if ($uuid && $video = Video::query()->find($uuid)) {
            return $video->image_id;
        }

        return null;
    }
}