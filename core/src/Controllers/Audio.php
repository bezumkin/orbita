<?php

namespace App\Controllers;

use App\Models\File;
use App\Models\TopicFile;
use Psr\Http\Message\ResponseInterface;

class Audio extends Video
{
    protected ?File $file = null;

    protected function loadFile(): ?ResponseInterface
    {
        $uuid = $this->getProperty('uuid');
        $this->file = File::query()
            ->where('type', 'LIKE', 'audio/%')
            ->where('uuid', $uuid)
            ->first();
        if (!$this->file) {
            return $this->failure('Not Found', 404);
        }

        $cacheTTL = (int)getenv('CACHE_MEDIA_ACCESS_TIME') ?: 3600;
        $key = implode(':', ['audio', $uuid, $this->user?->id ?: 'null']);

        $allow = $this->redis->get($key);
        if ($allow === null) {
            // Check permissions
            $isAdmin = $this->user && $this->user->hasScope('topics/patch');

            $allow = $isAdmin || $this->file->pageFiles()->count();
            if (!$allow) {
                $topicFiles = $this->file->topicFiles();
                /** @var TopicFile $topicFile */
                foreach ($topicFiles->cursor() as $topicFile) {
                    if ($topicFile->topic->hasAccess($this->user)) {
                        $allow = true;
                        break;
                    }
                }
            }
            $this->redis->set($key, $allow, 'EX', $cacheTTL);
        }
        if (!$allow) {
            return $this->failure('Access Denied', 403);
        }

        return null;
    }

    public function get(): ResponseInterface
    {
        $file = $this->file;

        if ($range = $this->request->getHeaderLine('Range')) {
            return $this->getRange($file, $range);
        }

        return getenv('DOWNLOAD_MEDIA_ENABLED')
            ? $this->download($file)
            : $this->failure('Range Not Satisfiable', 416);
    }
}