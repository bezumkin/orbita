<?php

namespace App\Controllers;

use App\Models\Video as VideoFile;
use App\Models\VideoQuality;
use App\Models\VideoUser;
use App\Services\Log;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Video extends Controller
{
    protected ?VideoFile $video = null;

    public function checkScope(string $method): ?ResponseInterface
    {
        $uuid = $this->getProperty('uuid');
        if (!$this->video = VideoFile::query()->find($uuid)) {
            return $this->failure('', 404);
        }

        return parent::checkScope($method);
    }

    public function get(): ResponseInterface
    {
        if ($quality = $this->getProperty('quality')) {
            if ($range = $this->request->getHeaderLine('Range')) {
                $range = explode('=', $range);
                [$start, $end] = explode('-', end($range), 2);
                if ($response = $this->getRange($quality, (int)$start, (int)$end)) {
                    return $response;
                }
            } elseif ($response = $this->getQuality($quality)) {
                return $response;
            }
        } elseif ($response = $this->getManifest()) {
            return $response;
        }

        return $this->failure('', 404);
    }

    public function getManifest(): ?ResponseInterface
    {
        if (!$manifest = $this->video->manifest) {
            $manifest = $this->video->getManifest();
        }
        if (empty($manifest)) {
            return null;
        }

        $this->response->getBody()
            ->write($manifest);

        return $this->response;
    }

    public function getQuality(string $quality): ?ResponseInterface
    {
        /** @var VideoQuality $videoQuality */
        if (!$videoQuality = $this->video->qualities()->where('quality', $quality)->first()) {
            return null;
        }
        $this->response->getBody()->write($videoQuality->manifest);

        return $this->response
            ->withHeader('Accept-Ranges', 'bytes');
    }

    public function getRange(string $quality, int $start, int $end): ?ResponseInterface
    {
        /** @var VideoQuality $videoQuality */
        if (!$videoQuality = $this->video->qualities()->where('quality', $quality)->first()) {
            return null;
        }

        try {
            $file = $videoQuality->file;
            $stream = $file->getFilesystem()
                ->getBaseFilesystem()
                ->readStream($file->getFilePathAttribute());
            $data = stream_get_contents($stream, $end - $start + 1, $start);
            $this->response->getBody()->write($data);
            $length = strlen($data);

            return $this->response
                ->withStatus(206, 'Partial Content')
                ->withHeader('Content-Type', $file->type)
                ->withHeader('Content-Range', "bytes $start-$end/$file->size")
                ->withHeader('Content-Length', $length);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }

        return null;
    }

    protected function post(): ResponseInterface
    {
        if ($this->user && $quality = $this->getProperty('quality')) {
            if (!$item = $this->video->videoUsers()->where('user_id', $this->user->id)->first()) {
                $item = new VideoUser();
                $item->video_id = $this->video->id;
                $item->user_id = $this->user->id;
            }
            $item->fill($this->getProperties());
            $item->save();
        }

        return $this->success();
    }
}