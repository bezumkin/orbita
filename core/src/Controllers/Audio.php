<?php

namespace App\Controllers;

use App\Models\CommentFile;
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

            $allow = $isAdmin || $this->file->pageFiles()->where('type', 'audio')->count();
            if (!$allow) {
                /** @var \App\Models\Video $video */
                if ($video = \App\Models\Video::query()->where('audio_id', $this->file->id)->first()) {
                    // Check if this is the audio version of video
                    $allow = $video->file->pageFiles()->where('type', 'video')->count() > 0;
                    if (!$allow) {
                        $topicFiles = $video->file->topicFiles()->where('type', 'video');
                        /** @var TopicFile $topicFile */
                        foreach ($topicFiles->cursor() as $topicFile) {
                            if ($topicFile->topic->hasAccess($this->user)) {
                                $allow = true;
                                break;
                            }
                        }
                    }
                } else {
                    $topicFiles = $this->file->topicFiles()->where('type', 'audio');
                    /** @var TopicFile $topicFile */
                    foreach ($topicFiles->cursor() as $topicFile) {
                        if ($topicFile->topic->hasAccess($this->user)) {
                            $allow = true;
                            break;
                        }
                    }

                    if (!$allow) {
                        $commentFiles = $this->file->commentFiles()->where('type', 'audio');
                        /** @var CommentFile $commentFile */
                        foreach ($commentFiles->cursor() as $commentFile) {
                            if ($commentFile->comment->topic->hasAccess($this->user)) {
                                $allow = true;
                                break;
                            }
                        }
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
        $fs = $file->getFilesystem();

        $useRemote = method_exists($fs, 'getStreamLink') && $stream = getenv('STREAM_MEDIA_FROM_S3');
        if ($useRemote) {
            if ($stream === '2' && !$this->isFree) {
                $useRemote = false;
            }
        }
        if ($useRemote) {
            return $this->response
                ->withStatus(302)
                ->withHeader('Location', $fs->getStreamLink($file->getFilePathAttribute()))
                ->withHeader(
                    'Access-Control-Allow-Origin',
                    getenv('CORS') ? $this->request->getHeaderLine('HTTP_ORIGIN') : ''
                );
        }

        if ($range = $this->request->getHeaderLine('Range')) {
            return $this->getRange($file, $range);
        }

        return getenv('DOWNLOAD_MEDIA_ENABLED')
            ? $this->download($file)
            : $this->failure('Range Not Satisfiable', 416);
    }
}