<?php

use App\Middlewares\Admin;
use Slim\Routing\RouteCollectorProxy;

/** @var Slim\App $app */
$group = $app->group(
    '/api',
    function (RouteCollectorProxy $group) {
        $group->get('/image/{uuid}', App\Controllers\Image::class);

        $group->group('/security', static function (RouteCollectorProxy $group) {
            $group->any('/login', App\Controllers\Security\Login::class);
            $group->any('/logout', App\Controllers\Security\Logout::class);
            $group->any('/register', App\Controllers\Security\Register::class);
            $group->any('/reset', App\Controllers\Security\Reset::class);
            $group->any('/activate', App\Controllers\Security\Activate::class);
        });

        $group->group('/user', static function (RouteCollectorProxy $group) {
            $group->any('/profile', App\Controllers\User\Profile::class);
            $group->any('/logout', App\Controllers\Security\Logout::class);
            if (getenv('REGISTER_ENABLED')) {
                $group->any('/register', App\Controllers\Security\Register::class);
            }
        });

        $group->group('/admin', static function (RouteCollectorProxy $group) {
            $group->any('/users[/{id}]', App\Controllers\Admin\Users::class);
            $group->any('/user-roles[/{id}]', App\Controllers\Admin\UserRoles::class);
        });

        $group->group(
            '/web',
            static function (RouteCollectorProxy $group) {
            }
        );
    }
);

if (class_exists('\Clockwork\Clockwork')) {
    $group->add(Vesp\Middlewares\Clockwork::class);
    $app->get(
        '/__clockwork/{id:(?:[0-9-]+|latest)}[/{direction:(?:next|previous)}[/{count:\d+}]]',
        Vesp\Controllers\Data\Clockwork::class
    );
    if (function_exists('xdebug_get_profiler_filename')) {
        $app->get('/__clockwork/{id:[0-9-]+}/extended', Vesp\Controllers\Data\Clockwork::class);
    }
}