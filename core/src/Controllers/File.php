<?php

namespace App\Controllers;

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

        if ($this->user && $this->user->hasScope('topics/patch')) {
            $allowDownload = true;
        } else {
            $allowDownload = false;
            $topicFiles = $file->topicFiles()->where('type', 'file');
            /** @var TopicFile $topicFile */
            foreach ($topicFiles->cursor() as $topicFile) {
                // $topic = $topicFile->topic;
                // @TODO check topic permissions
                if (true) {
                    $allowDownload = true;
                    break;
                }
            }
        }

        if (!$allowDownload) {
            return $this->failure('Access Denied', 403);
        }

        return $this->outputFile($file)
            ->withHeader('Content-Disposition', 'attachment; filename=' . $file->title ?: $file->file);
    }
}