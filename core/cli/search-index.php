<?php

require dirname(__DIR__) . '/bootstrap.php';

(new DI\Container())
    ->get(App\Services\Manticore::class)
    ->index();