<?php

namespace App\Controllers\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;

/**
 * @method getProperty(string $key, $default = null)
 * @method failure(string $message, int $code = 422)
 * @property array $attachments
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
                if (!$file) {
                    $file = new File();
                }

                // Process base64 encoded file
                if (!empty($tmp['file']) && $file->uploadFile($tmp['file'], $tmp['metadata'])) {
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
}
