<?php

namespace App\Controllers;

use App\Models\Video;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Poster extends Controller
{

    public function get(): ResponseInterface
    {
        $uuid = $this->getProperty('uuid');
        /** @var Video $video */
        if (!$uuid || !$video = Video::query()->find($uuid)) {
            return $this->failure('', 404);
        }

        if (!$file = $video->getPoster()) {
            return $this->failure('', 404);
        }
        $stream = fopen('php://memory', 'rb+');
        fwrite($stream, $file);
        rewind($stream);
        $type = mime_content_type($stream);
        $size = strlen($file);

        $this->response->getBody()->write($file);

        return $this->response
            ->withHeader('Content-Type', $type)
            ->withHeader('Content-Length', $size)
            ->withHeader('Cache-Control', 'max-age=31536000, public')
            ->withHeader('Expires', Carbon::now()->addYear()->toRfc822String());
    }
}