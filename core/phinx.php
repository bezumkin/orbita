<?php

require 'bootstrap.php';

use Vesp\Services\Eloquent;
use Vesp\Services\Migration;

return [
    'paths' => [
        'migrations' => BASE_DIR . 'core/db/migrations',
        'seeds' => BASE_DIR . 'core/db/seeds',
    ],
    'migration_base_class' => Migration::class,
    'templates' => [
        'style' => 'up_down',
    ],
    'environments' => [
        'default_migration_table' => getenv('DB_PREFIX') . 'migrations',
        'default_environment' => 'dev',
        'dev' => [
            'name' => getenv('DB_DATABASE'),
            'connection' => (new Eloquent())->getConnection()->getPdo(),
        ],
    ],
];