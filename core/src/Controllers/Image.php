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

        if (!$this->mayViewImage($file)) {
            return $this->response->withStatus(403);
        }

        return parent::get();
    }

    /**
     * Images that are inline content of a topic or comment are only served to viewers who may
     * access that topic (same rule the File controller applies). Images not tied to gated
     * content (topic covers, page images, avatars, category images, images of free topics)
     * remain public.
     */
    protected function mayViewImage(File $file): bool
    {
        if ($this->user && $this->user->hasScope('topics/patch')) {
            return true;
        }

        $topicFiles = $file->topicFiles()->where('type', 'image')->get();
        $commentFiles = $file->commentFiles()->where('type', 'image')->get();

        // Not inline topic/comment content (cover, page, avatar, category, ...) => public
        if ($topicFiles->isEmpty() && $commentFiles->isEmpty()) {
            return true;
        }

        // Also public when the same image is used on a (public) page
        if ($file->pageFiles()->where('type', 'image')->exists()) {
            return true;
        }

        /** @var TopicFile $topicFile */
        foreach ($topicFiles as $topicFile) {
            if ($topicFile->topic && $topicFile->topic->hasAccess($this->user)) {
                return true;
            }
        }

        /** @var CommentFile $commentFile */
        foreach ($commentFiles as $commentFile) {
            if ($commentFile->comment
                && $commentFile->comment->topic
                && $commentFile->comment->topic->hasAccess($this->user)) {
                return true;
            }
        }

        return false;
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
