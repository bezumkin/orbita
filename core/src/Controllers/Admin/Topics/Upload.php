<?php

namespace App\Controllers\Admin\Topics;

use App\Models\File;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;

class Upload extends \App\Controllers\Admin\Videos\Upload
{

    protected function getTempName(string $uuid, string $filename): string
    {
        $tmp = explode('.', $filename);
        $ext = count($tmp) > 1 ? end($tmp) : 'jpg';

        return $uuid . '.' . $ext;
    }

    protected function finishUpload(string $uuid): void
    {
        $meta = $this->storage->getMeta($uuid);

        $stream = new Stream(fopen($this->storage->getFullPath($meta['file']), 'rb'));
        $data = new UploadedFile($stream, $meta['filename'], $meta['filetype'], $meta['size']);

        $file = new File();
        $file->uuid = $uuid;
        $file->temporary = true;
        $file->uploadFile($data);
        $file->save();
    }
}