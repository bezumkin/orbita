<?php

namespace App\Controllers;

use App\Models\File;
use Intervention\Image\Drivers\Vips\Driver;
use Psr\Http\Message\ResponseInterface;

class Image extends \Vesp\Controllers\Data\Image
{
    protected string $model = File::class;
    protected string|array $primaryKey = ['uuid'];

    protected function handleFile($file): ?ResponseInterface
    {
        if (getenv('IMAGE_DRIVER') === 'vips') {
            putenv('IMAGE_DRIVER=' . Driver::class);
        }

        // Replace any SVG to black square for security reasons
        if (str_starts_with($file->type, 'image/svg')) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="black"/></svg>';
            $body = $this->response->getBody();
            $body->write($svg);

            return $this->response
                ->withBody($body)
                ->withHeader('Content-Type', 'image/svg+xml')
                ->withHeader('Content-Length', strlen($svg));
        }

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
