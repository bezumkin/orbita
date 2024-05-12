<?php

namespace App\Controllers;

use App\Models\Video;
use App\Services\Log;
use App\Services\ThumbStorage;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Throwable;

class Poster extends Image
{
    public function get(): ResponseInterface
    {
        $service = $this->getProperty('service');
        $key = $this->getProperty('key');

        if (!$service || !$key) {
            return parent::get();
        }

        $uuid = Uuid::uuid5(Uuid::NAMESPACE_URL, $service . $key);

        try {
            $fs = (new ThumbStorage())->getBaseFilesystem();
            if (!$fs->fileExists($uuid) && $file = $this->downloadFile($service, $key)) {
                $fs->write($uuid, $file);
            }
            $this->response->getBody()->write($fs->read($uuid));

            return $this->response
                ->withHeader('Content-Type', $fs->mimeType($uuid))
                ->withHeader('Content-Length', $fs->fileSize($uuid))
                ->withHeader('Cache-Control', 'max-age=31536000, public')
                ->withHeader('Expires', Carbon::now()->addYear()->toRfc822String());
        } catch (Throwable $e) {
            Log::error($e);
        }

        return $this->failure('Not Found', 404);
    }

    protected function downloadFile(string $service, string $key): string
    {
        $url = '';
        if ($service === 'youtube') {
            if ($meta = $this->getOgTags('https://www.youtube.com/watch?v=' . $key)) {
                $url = $meta['image'];
            }
        } elseif ($service === 'vimeo') {
            if ($meta = $this->getOgTags('https://vimeo.com/' . $key)) {
                $url = $meta['image'];
            }
        } elseif ($service === 'rutube') {
            if ($meta = $this->getOgTags('https://rutube.ru/video/' . $key . '/')) {
                $url = $meta['image'];
            }
        } elseif ($service === 'vk') {
            if ($meta = $this->getOgTags('https://vk.com/video' . $key)) {
                $url = $meta['image'];
            }
        } elseif ($service === 'peertube') {
            if ($meta = $this->getOgTags('https://peertube.tv/w/' . $key)) {
                $url = $meta['image'];
            }
        }

        if ($url) {
            try {
                $response = $this->getClient()->get($url);

                return (string)$response->getBody();
            } catch (Throwable $e) {
                Log::error($e);
            }
        }

        return $this->getDefaultImage();
    }

    protected function getOgTags(string $url): ?array
    {
        $tags = [];
        try {
            $response = $this->getClient()->get($url);
            $html = (string)$response->getBody();
            $pattern = '#<meta.*?property="og:(.*?)".*?content="(.*?)".*?>#i';
            if (preg_match_all($pattern, $html, $matches)) {
                return array_combine($matches[1], $matches[2]);
            }
        } catch (Throwable $e) {
            Log::error($e);
        }

        return $tags;
    }

    protected function getClient(): Client
    {
        return new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:121.0) Gecko/20100101 Firefox/121.0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en',
            ],
        ]);
    }

    protected function getDefaultImage(string $path = 'default.jpg'): string
    {
        $temp = new ThumbStorage();
        $fs = $temp->getBaseFilesystem();
        if (!$fs->has($path)) {
            $img = imagecreatetruecolor(800, 450);
            imagefill($img, 0, 0, imagecolorallocate($img, 248, 249, 250));
            imagejpeg($img, $temp->getFullPath($path));
        }

        return $fs->read($path);
    }

    protected function getPrimaryKey(): null|string|array
    {
        $uuid = $this->getProperty('uuid');
        /** @var Video $video */
        if ($uuid && $video = Video::query()->find($uuid)) {
            return $video->image_id;
        }

        return null;
    }
}