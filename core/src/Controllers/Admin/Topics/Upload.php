<?php

namespace App\Controllers\Admin\Topics;

use App\Controllers\Traits\UploadController;
use Vesp\Controllers\Controller;

class Upload extends Controller
{
    use UploadController;

    protected string|array $scope = 'topics';
}