<?php

namespace App\Controllers\Web\Comments;

use App\Controllers\Traits\UploadController;
use Vesp\Controllers\Controller;

class Upload extends Controller
{
    protected string|array $scope = 'comments';

    use UploadController;

    // @TODO Check access to topic
}