<?php

namespace App\Controllers\Admin\Videos;

use App\Models\Video;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Data\Image;

class Download extends Image
{
    protected string|array $scope = 'videos';
    protected string $model = Video::class;

    public function get(): ResponseInterface
    {
        $key = $this->getPrimaryKey();
        /** @var Video $video */
        if (!$key || !$video = Video::query()->find($key)) {
            return $this->failure('Not Found', 404);
        }
        if (!$video->moved) {
            return $this->failure('Not Ready', 425);
        }

        return $this->outputFile($video->file)
            ->withHeader('Content-Disposition', 'attachment; filename=' . $video->file->title);
    }
}