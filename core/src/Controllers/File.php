<?php

namespace App\Controllers;

use App\Models\CommentFile;
use App\Models\File as FileModel;
use App\Models\TopicFile;
use Psr\Http\Message\ResponseInterface;

class File extends \Vesp\Controllers\Data\Image
{
    public function get(): ResponseInterface
    {
        $uuid = $this->getProperty('uuid');
        /** @var FileModel $file */
        if (!$uuid || !$file = FileModel::query()->where('uuid', $uuid)->first()) {
            return $this->failure('Not Found', 404);
        }

        $isAdmin = $this->user && $this->user->hasScope('topics/patch');

        $allow = $isAdmin || $file->pageFiles()->where('type', 'file')->count();
        if (!$allow) {
            $topicFiles = $file->topicFiles()->where('type', 'file');
            /** @var TopicFile $topicFile */
            foreach ($topicFiles->cursor() as $topicFile) {
                if ($topicFile->topic->hasAccess($this->user)) {
                    $allow = true;
                    break;
                }
            }

            if (!$allow) {
                $commentFiles = $file->commentFiles()->where('type', 'file');
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

        return $this->outputFile($file)
            ->withHeader('Content-Disposition', 'attachment; filename=' . $file->title ?: $file->file);
    }
}