<?php

namespace App\Controllers\Traits;

use App\Models\File;
use App\Models\Video;
use App\Services\TempStorage;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;
use Vesp\Services\Eloquent;

trait UploadController
{
    protected TempStorage $storage;

    protected const TUS_PROTOCOL_VERSION = '1.0.0';
    protected const TUS_EXTENSIONS = ['creation', 'termination', 'expiration'];
    protected const EXPIRE_HOURS = 24;

    public function __construct(Eloquent $eloquent, TempStorage $storage)
    {
        parent::__construct($eloquent);
        $this->storage = $storage;
    }

    public function options(): ResponseInterface
    {
        return parent::options()
            ->withHeader('Allow', 'OPTIONS,HEAD,POST,PATCH,DELETE');
    }

    public function head(): ResponseInterface
    {
        if (!$meta = $this->storage->getMeta($this->getProperty('uuid', ''))) {
            return $this->failure('', 404);
        }

        return $this->success()
            ->withHeader('Upload-Length', $meta['size'])
            ->withHeader('Upload-Offset', $meta['offset'])
            ->withHeader('Cache-Control', 'no-store');
    }

    public function get(): ResponseInterface
    {
        $file = File::query()
            ->where('uuid', $this->getProperty('uuid'))
            ->where('temporary', true)
            ->first();

        return $file ? $this->success($file->toArray()) : $this->failure('Not Found', 404);
    }

    public function post(): ResponseInterface
    {
        $meta = [];
        if ($headers = explode(',', $this->request->getHeaderLine('Upload-Metadata'))) {
            foreach ($headers as $header) {
                $pieces = explode(' ', trim($header));
                $meta[$pieces[0]] = !empty($pieces[1]) ? base64_decode($pieces[1]) : '';
            }
        }
        if (empty($meta['filename'])) {
            return $this->failure('errors.upload.no_filename', 400);
        }

        if (!$size = (int)$this->request->getHeaderLine('Upload-Length')) {
            return $this->failure('errors.upload.no_size', 400);
        }

        $uuid = Uuid::uuid4()->toString();
        $meta['file'] = $this->getTempName($uuid, $meta['filename']);
        $meta['offset'] = 0;
        $meta['size'] = $size;
        $meta['expires'] = Carbon::now()->addHours($this::EXPIRE_HOURS)->toRfc7231String();

        $this->storage->getBaseFilesystem()->write($meta['file'], '');
        $this->storage->setMeta($uuid, $meta);
        $location = rtrim((string)$this->request->getUri(), '/') . '/' . $uuid;
        $location = str_replace('http://', '//', $location);

        return $this->success('', 201)
            ->withHeader('Location', $location)
            ->withHeader('Upload-Expires', $meta['expires']);
    }

    public function patch(): ResponseInterface
    {
        $uuid = $this->getProperty('uuid');
        if (!$uuid || !$meta = $this->storage->getMeta($uuid)) {
            return $this->failure('errors.upload.missing_meta', 410);
        }

        $offset = (int)$this->request->getHeaderLine('Upload-Offset');
        if ($offset && $offset !== $meta['offset']) {
            return $this->failure('errors.upload.wrong_offset', 409);
        }

        $contentType = $this->request->getHeaderLine('Content-Type');
        if ($contentType !== 'application/offset+octet-stream') {
            return $this->failure('errors.upload.wrong_content', 415);
        }

        if (!$meta = $this->storage->writeFile($uuid, $meta)) {
            return $this->failure('errors.upload.write', 500);
        }

        if ($meta['size'] === $meta['offset']) {
            $this->finishUpload($uuid);
        }

        return $this->success('', 204)
            ->withHeader('Content-Type', 'application/offset+octet-stream')
            ->withHeader('Upload-Expires', $meta['expires'])
            ->withHeader('Upload-Offset', $meta['offset']);
    }

    public function delete(): ResponseInterface
    {
        $uuid = $this->getProperty('uuid');
        if (!$uuid || !$this->storage->getMeta($uuid)) {
            return $this->failure('', 404);
        }

        if (Video::query()->find($uuid)) {
            return $this->failure('', 403);
        }

        $this->storage->deleteFile($uuid);

        return $this->success('', 204);
    }

    protected function response($data, int $status = 200, string $reason = ''): ResponseInterface
    {
        return parent::response($data, $status, $reason)
            ->withHeader('Tus-Extension', implode(',', $this::TUS_EXTENSIONS))
            ->withHeader('Tus-Resumable', $this::TUS_PROTOCOL_VERSION);
    }

    protected function getTempName(string $uuid, string $filename): string
    {
        $tmp = explode('.', $filename);
        $ext = count($tmp) > 1 ? end($tmp) : 'none';

        return $uuid . '.' . $ext;
    }

    protected function finishUpload(string $uuid): void
    {
        $meta = $this->storage->getMeta($uuid);

        $resource = fopen($this->storage->getFullPath($meta['file']), 'rb');
        $stream = new Stream($resource);

        $meta['filetype'] = mime_content_type($resource);
        $meta['size'] = $stream->getSize();
        $meta['filename'] = preg_replace('#[^\w\s.-_]#u', '', $meta['filename']);
        $this->storage->setMeta($uuid, $meta);

        $uploadedFile = new UploadedFile($stream, $meta['filename'], $meta['filetype'], $meta['size']);

        $file = new File();
        $file->uuid = $uuid;
        $file->temporary = true;
        $file->uploadFile($uploadedFile);
        $file->save();
    }
}