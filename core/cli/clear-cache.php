<?php

use App\Services\VideoCache;

require dirname(__DIR__) . '/bootstrap.php';
(new VideoCache())->clear();