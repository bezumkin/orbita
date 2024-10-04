<?php

namespace App\Controllers\Web\Comments;

use App\Controllers\Traits\UploadController;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Upload extends Controller
{
    use UploadController;

    protected string|array $scope = 'comments';

    protected function checkMetaBeforeStart(array $meta): ?ResponseInterface
    {
        if (str_starts_with($meta['filetype'], 'image/')) {
            $limit = (int)getenv('COMMENTS_UPLOAD_IMAGE_LIMIT');
        } elseif (str_starts_with($meta['filetype'], 'audio/')) {
            $limit = (int)getenv('COMMENTS_UPLOAD_AUDIO_LIMIT');
        } else {
            $limit = (int)getenv('COMMENTS_UPLOAD_FILE_LIMIT');
        }

        $size = $meta['size'] / 1024 / 1024;
        if ($limit && $size > $limit) {
            return $this->failure('errors.upload.limit_size');
        }

        $extensions = array_map(static function ($val) {
            return strtolower(trim($val));
        }, explode(',', getenv('COMMENTS_UPLOAD_EXTENSIONS')));
        if ($extensions) {
            $extension = strtolower(pathinfo($meta['filename'], PATHINFO_EXTENSION));
            if (!in_array($extension, $extensions, true)) {
                return $this->failure('errors.upload.limit_extension');
            }
        }

        return null;
    }

    // @TODO Check access to topic
}