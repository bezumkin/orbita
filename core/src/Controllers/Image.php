<?php

namespace App\Controllers;

use App\Models\File;
use Psr\Http\Message\ResponseInterface;

class Image extends \Vesp\Controllers\Data\Image
{
    protected string $model = File::class;
    protected string|array $primaryKey = ['uuid'];

    protected function handleFile($file): ?ResponseInterface
    {
        /** @var File $file */
        if ($crop = @$file->metadata['crop']) {
            $data = implode(',', [$crop['width'], $crop['height'], $crop['x'], $crop['y']]);
            $this->setProperty('crop', $data);
        } elseif ($file->width && $this->getProperty('fit') === 'crop-center') {
            $this->setProperty('w', (string)$file->width);
        }

        return parent::handleFile($file);
    }
}
