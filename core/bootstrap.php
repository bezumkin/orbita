<?php

define('BASE_DIR', dirname(__DIR__) . '/');
require_once BASE_DIR . 'core/vendor/autoload.php';

\Vesp\Helpers\Env::loadFile(BASE_DIR . '/.env');
