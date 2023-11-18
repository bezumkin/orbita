<?php

namespace App\Controllers\Admin\Videos;

use App\Controllers\Traits\UploadController;
use App\Models\Video;
use Vesp\Controllers\Controller;

class Upload extends Controller
{
    use UploadController;

    protected string|array $scope = 'videos';

    protected function getTempName(string $uuid, string $filename): string
    {
        $tmp = explode('.', $filename);
        $ext = count($tmp) > 1 ? end($tmp) : 'mp4';

        return $uuid . '/video.' . $ext;
    }

    protected function finishUpload(string $uuid): void
    {
        $meta = $this->storage->getMeta($uuid);
        $tmp = explode('.', $meta['filename']);
        array_pop($tmp);

        $video = new Video();
        $video->id = $uuid;
        $video->title = implode('.', $tmp);
        $video->file_id = $this->storage::getFakeFile($meta['filename'], $meta['filetype'], $meta['size'])->id;
        $video->save();
    }
}