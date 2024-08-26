<?php

namespace App\Controllers\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Stream;
use Slim\Psr7\UploadedFile;

/**
 * @method getProperty(string $key, $default = null)
 * @method failure(string $message, int $code = 422)
 * @property array $attachments
 * @property array $allowedTypes
 */
trait FileModelController
{
    protected function processFiles(Model $record): ?ResponseInterface
    {
        foreach ($this->attachments as $attachment) {
            /** @var File $file */
            $file = $record->$attachment;
            // Delete existed attachment if received false
            if ($file && $this->getProperty("new_$attachment") === false) {
                $file->delete();
            } elseif ($tmp = $this->getProperty("new_$attachment", $this->getProperty($attachment))) {
                if (empty($tmp['file'])) {
                    return null;
                }
                if (!$file) {
                    $file = new File();
                }

                // Check mime type of uploaded file
                $uploadedFile = $this->normalizeFile($tmp['file'], $tmp['metadata']);
                if (isset($this->allowedTypes[$attachment])) {
                    if (!$uploadedType = $uploadedFile->getClientMediaType()) {
                        return $this->failure('errors.upload.no_mime');
                    }
                    $allow = false;
                    $types = $this->allowedTypes[$attachment];
                    if (!is_array($types)) {
                        $types = [$types];
                    }
                    foreach ($types as $pattern) {
                        if (str_starts_with($uploadedType, $pattern)) {
                            $allow = true;
                            break;
                        }
                    }
                    if (!$allow) {
                        return $this->failure('errors.upload.wrong_mime');
                    }
                }

                if (!empty($tmp['file']) && $file->uploadFile($uploadedFile)) {
                    $record->{"{$attachment}_id"} = $file->id;
                }
            }
        }

        return null;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        if ($error = $this->processFiles($record)) {
            return $error;
        }

        return null;
    }

    protected function normalizeFile(string|UploadedFile $file, ?array $metadata = []): UploadedFile
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }
        if (!strpos($file, ';base64,')) {
            throw new InvalidArgumentException('Could not parse base64 string');
        }
        $data = explode(',', $file)[1];
        $name = preg_replace('#[^\w\s.-_]#u', '', @$metadata['name']);

        $resource = fopen('php://temp', 'rb+');
        fwrite($resource, base64_decode($data));
        fseek($resource, 0);

        $stream = new Stream($resource);

        return new UploadedFile($stream, $name, mime_content_type($resource), $stream->getSize());
    }
}
