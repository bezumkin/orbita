<?php

namespace App\Controllers\Admin\Videos;

use App\Models\Video;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Data\Image;
use Vesp\Models\File;

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

        return $this->outputFile($video->file);
    }

    protected function outputFile($file): ResponseInterface
    {
        /** @var File $file */
        $title = $file->title ?: $file->file;
        try {
            $fs = $file->getFilesystem();
            if (getenv('DOWNLOAD_MEDIA_FROM_S3') && method_exists($fs, 'getDownloadLink')) {
                $link = $fs->getDownloadLink($file->getFilePathAttribute(), $title);

                return $this->response
                    ->withStatus(302)
                    ->withHeader('Location', $link);
            }
        } catch (\Throwable $e) {
        }

        return parent::outputFile($file)
            ->withHeader('Content-Disposition', 'attachment; filename=' . $title);
    }
}