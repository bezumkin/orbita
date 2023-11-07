<?php

namespace App\Controllers;

use App\Models\File;
use App\Services\Log;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Audio extends Controller
{
    protected ?File $file = null;

    public function checkScope(string $method): ?ResponseInterface
    {
        $uuid = $this->getProperty('uuid');
        $this->file = File::query()
            ->where('type', 'LIKE', 'audio/%')
            ->where('uuid', $uuid)
            ->first();
        if (!$this->file) {
            return $this->failure('Not Found', 404);
        }

        return parent::checkScope($method);
    }

    public function get(): ResponseInterface
    {
        $file = $this->file;
        $range = $this->request->getHeaderLine('Range');
        $range = explode('=', $range);
        [$start, $end] = array_map('intval', explode('-', end($range), 2));
        if (!$end) {
            $end = $this->file->size - 1;
        }

        try {
            $fs = $file->getFilesystem();
            if (method_exists($fs, 'readRangeStream')) {
                $body = $fs->readRangeStream($file->getFilePathAttribute(), $start, $end);
                $this->response = $this->response->withBody($body);
                $length = $body->getSize();
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
                ->withHeader('Content-Length', $length)
                ->withHeader('Content-Range', "bytes $start-$end/$file->size")
                ->withHeader(
                    'Access-Control-Allow-Origin',
                    getenv('CORS') ? $this->request->getHeaderLine('HTTP_ORIGIN') : ''
                );
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }

        return $this->failure('', 500);
    }
}