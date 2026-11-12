<?php

namespace App\Controllers;

use App\Models\CommentFile;
use App\Models\File;
use App\Models\TopicFile;
use Intervention\Image\Drivers\Vips\Driver;
use Psr\Http\Message\ResponseInterface;

class Image extends \Vesp\Controllers\Data\Image
{
    protected string $model = File::class;
    protected string|array $primaryKey = ['uuid'];

    public function get(): ResponseInterface
    {
        $uuid = $this->getProperty('uuid');
        /** @var File $file */
        if (!$uuid || !$file = File::query()->where('uuid', $uuid)->first()) {
            return $this->response->withStatus(404);
        }

        $isAdmin = $this->user && $this->user->hasScope('topics/patch');

        $allow = $isAdmin || $file->pageFiles()->where('type', 'image')->exists();
        if (!$allow) {
            $topicFiles = $file->topicFiles()->where('type', 'image');
            $commentFiles = $file->commentFiles()->where('type', 'image');
            $allow = !$topicFiles->exists() && !$commentFiles->exists();

            if (!$allow) {
                /** @var TopicFile $topicFile */
                foreach ($topicFiles->cursor() as $topicFile) {
                    if ($topicFile->topic->hasAccess($this->user)) {
                        $allow = true;
                        break;
                    }
                }
            }

            if (!$allow) {
                /** @var CommentFile $commentFile */
                foreach ($commentFiles->cursor() as $commentFile) {
                    if ($commentFile->comment->topic->hasAccess($this->user)) {
                        $allow = true;
                        break;
                    }
                }
            }
        }
        if (!$allow) {
            return $this->failure('Access Denied', 403);
        }

        return parent::get();
    }

    protected function handleFile($file): ?ResponseInterface
    {
        if (getenv('IMAGE_DRIVER') === 'vips') {
            putenv('IMAGE_DRIVER=' . Driver::class);
        }

        // Replace any SVG to black square for security reasons
        if (str_starts_with($file->type, 'image/svg')) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="black"/></svg>';
            $body = $this->response->getBody();
            $body->write($svg);

            return $this->response
                ->withBody($body)
                ->withHeader('Content-Type', 'image/svg+xml')
                ->withHeader('Content-Length', strlen($svg));
        }

        /** @var File $file */
        if ($crop = @$file->metadata['crop']) {
            $data = implode(',', [$crop['width'], $crop['height'], $crop['x'], $crop['y']]);
            $this->setProperty('crop', $data);
        } elseif ($file->width && $this->getProperty('fit') === 'crop-center') {
            $this->setProperty('w', (string)$file->width);
        }

        return parent::handleFile($file);
    }
}
