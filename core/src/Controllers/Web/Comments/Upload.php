<?php

namespace App\Controllers\Web\Comments;

use App\Controllers\Traits\UploadController;
use Vesp\Controllers\Controller;

class Upload extends Controller
{
    use UploadController;

    protected string|array $scope = 'comments';

    // @TODO Check access to topic
}