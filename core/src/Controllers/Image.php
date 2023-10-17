<?php

namespace App\Controllers;

use App\Models\File;

class Image extends \Vesp\Controllers\Data\Image
{
    protected string $model = File::class;
    protected string|array $primaryKey = ['uuid'];
}
