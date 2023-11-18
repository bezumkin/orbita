<?php

use Slim\Routing\RouteCollectorProxy;

/** @var Slim\App $app */
$group = $app->group(
    '/api',
    function (RouteCollectorProxy $group) {
        $group->map(['OPTIONS', 'GET', 'POST'], '/video/{uuid}[/{quality:\d+}]', App\Controllers\Video::class);
        $group->map(['OPTIONS', 'GET', 'POST'], '/audio/{uuid}', App\Controllers\Audio::class);
        $group->get('/image/{uuid}', App\Controllers\Image::class);
        $group->get('/file/{uuid}', App\Controllers\File::class);
        $group->get('/poster/{uuid}[/{w}]', App\Controllers\Poster::class);

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
            $group->map(['OPTIONS', 'GET', 'POST'], '/video/{uuid}', App\Controllers\User\Videos::class);
            if (getenv('REGISTER_ENABLED')) {
                $group->any('/register', App\Controllers\Security\Register::class);
            }
        });

        $group->group('/admin', static function (RouteCollectorProxy $group) {
            $group->any('/users[/{id:\d+}]', App\Controllers\Admin\Users::class);
            $group->any('/user-roles[/{id:\d+}]', App\Controllers\Admin\UserRoles::class);
            $group->any('/videos/upload[/{uuid}]', App\Controllers\Admin\Videos\Upload::class);
            $group->any('/videos[/{id}]', App\Controllers\Admin\Videos::class);
            $group->any('/settings[/{key}]', App\Controllers\Admin\Settings::class);
            $group->any('/levels[/{id:\d+}]', App\Controllers\Admin\Levels::class);
            $group->any('/topics/upload[/{uuid}]', App\Controllers\Admin\Topics\Upload::class);
            $group->any('/topics[/{id:\d+}]', App\Controllers\Admin\Topics::class);
            $group->any('/notifications[/{id}]', App\Controllers\Admin\Notifications::class);

        });

        $group->group(
            '/web',
            static function (RouteCollectorProxy $group) {
                $group->any('/settings', App\Controllers\Web\Settings::class);
                $group->any('/levels', App\Controllers\Web\Levels::class);
                $group->any('/topics[/{uuid}]', App\Controllers\Web\Topics::class);
                $group->any('/topics/{topic_uuid}/view', App\Controllers\Web\Topics\View::class);
                $group->any('/topics/{topic_uuid}/comments[/{id:\d+}]', App\Controllers\Web\Comments::class);
                $group->any('/topics/{topic_uuid}/comments/upload[/{uuid}]', App\Controllers\Web\Comments\Upload::class);
                $group->any('/comments/latest', App\Controllers\Web\Comments\Latest::class);
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