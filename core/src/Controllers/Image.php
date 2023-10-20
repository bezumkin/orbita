<?php

namespace App\Controllers;

use App\Models\File;
use Psr\Http\Message\ResponseInterface;

class Image extends \Vesp\Controllers\Data\Image
{
    protected string $model = File::class;
    protected string|array $primaryKey = ['uuid'];

    protected function handleFile(\Vesp\Models\File $file): ?ResponseInterface
    {
        // SVG cannot be processed, so we output it as is
        if ($file->width && $this->getProperty('fit') === 'crop-center') {
            $this->setProperty('w', (string)$file->width);
        }

        return parent::handleFile($file);
    }
}
