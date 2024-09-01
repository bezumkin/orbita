<?php

namespace App\Controllers;

use App\Models\File;
use App\Models\TopicFile;
use App\Models\Video as VideoFile;
use App\Models\VideoQuality;
use App\Services\Log;
use App\Services\Redis;
use App\Services\VideoCache;
use Illuminate\Database\Capsule\Manager;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Stream;
use Throwable;
use Vesp\Controllers\Controller;

class Video extends Controller
{
    protected ?VideoFile $video = null;
    protected VideoCache $cache;
    protected Redis $redis;

    public function __construct(Manager $eloquent, VideoCache $cache, Redis $redis)
    {
        parent::__construct($eloquent);
        $this->cache = $cache;
        $this->redis = $redis;
    }

    public function checkScope(string $method): ?ResponseInterface
    {
        if ($method === 'options') {
            return parent::checkScope('options');
        }

        return $this->loadFile();
    }

    protected function loadFile(): ?ResponseInterface
    {
        $uuid = $this->getProperty('uuid');
        if (!$this->video = VideoFile::query()->find($uuid)) {
            return $this->failure('Not Found', 404);
        }

        $cacheTTL = (int)getenv('CACHE_MEDIA_ACCESS_TIME') ?: 3600;
        $key = implode(':', ['video', $uuid, $this->user?->id ?: 'null']);

        $allow = $this->redis->get($key);
        if ($allow === null) {
            // Check permissions
            $isAdmin = $this->user && $this->user->hasScope('videos/patch');
            if (!$isAdmin && !$this->video->active) {
                return $this->failure('Not Found', 404);
            }

            $allow = $isAdmin || $this->video->pageFiles()->where('type', 'video')->count();
            if (!$allow) {
                $topicFiles = $this->video->topicFiles()->where('type', 'video');
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
        if ($quality = $this->getProperty('quality')) {
            if ($quality === 'chapters') {
                return $this->success($this->video->chapters);
            }

            if ($quality === 'thumbnails') {
                return $this->getThumbnails();
            }

            if ($quality === 'download' && getenv('DOWNLOAD_MEDIA_ENABLED')) {
                return $this->download($this->video->file);
            }

            /** @var VideoQuality $videoQuality */
            if ($videoQuality = $this->video->qualities()->where('quality', $quality)->first()) {
                if ($range = $this->request->getHeaderLine('Range')) {
                    return $this->getRange($videoQuality->file, $range);
                }

                return $this->getQuality($videoQuality);
            }
        } elseif ($response = $this->getManifest()) {
            return $response;
        }

        return $this->failure('Not Found', 404);
    }

    public function getManifest(): ?ResponseInterface
    {
        if (!$manifest = $this->video->manifest) {
            $manifest = $this->video->getManifest();
        }
        if (empty($manifest)) {
            return null;
        }

        $this->response->getBody()->write($manifest);

        return $this->response
            ->withHeader('Accept-Ranges', 'bytes')
            ->withHeader('Content-Type', 'audio/mpegurl')
            ->withHeader('Content-Length', $this->response->getBody()->getSize())
            ->withHeader(
                'Access-Control-Allow-Origin',
                getenv('CORS') ? $this->request->getHeaderLine('HTTP_ORIGIN') : ''
            );
    }

    public function getThumbnails(): ?ResponseInterface
    {
        if (!$this->video->thumbnails && $this->video->thumbnail_id) {
            $this->video->thumbnails = $this->video->getThumbnails();
            $this->video->save();
        }

        return $this->success($this->video->thumbnails);
    }

    protected function getQuality(VideoQuality $videoQuality): ResponseInterface
    {
        $this->response->getBody()->write($videoQuality->manifest);

        return $this->response
            ->withHeader('Accept-Ranges', 'bytes')
            ->withHeader('Content-Type', 'audio/mpegurl')
            ->withHeader('Content-Length', $this->response->getBody()->getSize())
            ->withHeader(
                'Access-Control-Allow-Origin',
                getenv('CORS') ? $this->request->getHeaderLine('HTTP_ORIGIN') : ''
            );
    }

    protected function getRange(File $file, string $ranges): ResponseInterface
    {
        $range = explode('=', $ranges);
        [$start, $end] = array_map('intval', explode('-', end($range), 2));
        if (!$end) {
            $tmp = $start + 1048576; // 1 Mb
            $end = $tmp + 1 >= $file->size ? $file->size - 1 : $tmp;
        }
        if ($end - $start >= 1073741824) {
            return $this->failure('Range Not Satisfiable', 416);
        }

        try {
            $fs = $file->getFilesystem();
            if (method_exists($fs, 'readRangeStream')) {
                if (!getenv('CACHE_S3_SIZE') || !$data = $this->cache->get($file->uuid, $start, $end)) {
                    /** @var \Psr\Http\Message\StreamInterface $body */
                    $body = $fs->readRangeStream($file->getFilePathAttribute(), $start, $end);
                    $this->response = $this->response->withBody($body);
                    $length = $body->getSize();
                    if (getenv('CACHE_S3_SIZE')) {
                        $this->cache->set($file->uuid, $start, $end, $body->__toString());
                    }
                } else {
                    $this->response->getBody()->write($data);
                    $length = strlen($data);
                }
            } else {
                $stream = $fs->getBaseFilesystem()->readStream($file->getFilePathAttribute());
                $data = stream_get_contents($stream, $end - $start + 1, $start);
                $this->response->getBody()->write($data);
                $length = strlen($data);
            }

            return $this->response
                ->withStatus(206, 'Partial Content')
                ->withHeader('Accept-Ranges', 'bytes')
                ->withHeader('Content-Type', $file->type)
                ->withHeader('Content-Range', "bytes $start-$end/$file->size")
                ->withHeader('Content-Length', $length)
                ->withHeader(
                    'Access-Control-Allow-Origin',
                    getenv('CORS') ? $this->request->getHeaderLine('HTTP_ORIGIN') : ''
                );
        } catch (Throwable $e) {
            Log::error($e);
        }

        return $this->failure('Range Not Satisfiable', 416);
    }

    protected function download(File $file, ?string $title = null): ResponseInterface
    {
        $fs = $file->getFilesystem();
        try {
            if (getenv('DOWNLOAD_MEDIA_FROM_S3') && method_exists($fs, 'getDownloadLink')) {
                $link = $fs->getDownloadLink($file->getFilePathAttribute(), $title ?: $file->file);

                return $this->response
                    ->withStatus(302)
                    ->withHeader('Location', $link);
            }
        } catch (Throwable $e) {
            Log::error($e);
        }

        $stream = new Stream($fs->getBaseFilesystem()->readStream($file->getFilePathAttribute()));

        return $this->response
            ->withBody($stream)
            ->withHeader('Content-Type', $file->type)
            ->withHeader('Content-Length', $file->size)
            ->withHeader('Content-Disposition', 'attachment; filename=' . $title ?: $file->file);
    }
}