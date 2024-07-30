<?php

use Slim\Routing\RouteCollectorProxy;

/** @var Slim\App $app */
$group = $app->group(
    '/api',
    function (RouteCollectorProxy $group) {
        $group->map(['OPTIONS', 'GET', 'POST'], '/video/{uuid}[/{quality}]', App\Controllers\Video::class);
        $group->map(['OPTIONS', 'GET', 'POST'], '/audio/{uuid}', App\Controllers\Audio::class);
        $group->get('/image/{uuid}', App\Controllers\Image::class);
        $group->get('/file/{uuid}', App\Controllers\File::class);
        $group->get('/poster/{uuid}[/{w:\d+}]', App\Controllers\Poster::class);
        $group->get('/poster/embed/{service}/{key}', App\Controllers\Poster::class);

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
            $group->any('/payments[/{id}]', App\Controllers\User\Payments::class);
            $group->any('/subscription/{action}[/{level:\d+}]', App\Controllers\User\Subscription::class);
        });

        $group->group('/admin', static function (RouteCollectorProxy $group) {
            $group->any('/users[/{id:\d+}]', App\Controllers\Admin\Users::class);
            $group->any('/user-roles[/{id:\d+}]', App\Controllers\Admin\UserRoles::class);
            $group->any('/videos/upload[/{uuid}]', App\Controllers\Admin\Videos\Upload::class);
            $group->any('/videos/download/{id}', App\Controllers\Admin\Videos\Download::class);
            $group->any('/videos[/{id}]', App\Controllers\Admin\Videos::class);
            $group->any('/videos/{video_id}/qualities[/{quality}]', App\Controllers\Admin\Videos\Qualities::class);
            $group->any('/settings[/{key}]', App\Controllers\Admin\Settings::class);
            $group->any('/levels[/{id:\d+}]', App\Controllers\Admin\Levels::class);
            $group->any('/topics/upload[/{uuid}]', App\Controllers\Admin\Topics\Upload::class);
            $group->any('/topics[/{id:\d+}]', App\Controllers\Admin\Topics::class);
            $group->any('/notifications[/{id}]', App\Controllers\Admin\Notifications::class);
            $group->any('/pages[/{id:\d+}]', App\Controllers\Admin\Pages::class);
            $group->any('/payments[/{id}]', App\Controllers\Admin\Payments::class);
            $group->any('/tags[/{id}]', App\Controllers\Admin\Tags::class);
            $group->any('/reactions', App\Controllers\Admin\Reactions::class);
            $group->any('/redirects[/{id:\d+}]', App\Controllers\Admin\Redirects::class);
        });

        $group->group(
            '/web',
            static function (RouteCollectorProxy $group) {
                $group->any('/settings', App\Controllers\Web\Settings::class);
                $group->any('/levels[/{id:\d+}]', App\Controllers\Web\Levels::class);
                $group->any('/topics[/{uuid}]', App\Controllers\Web\Topics::class);
                $group->any('/topics/{topic_uuid}/view', App\Controllers\Web\Topics\View::class);
                $group->any('/topics/{topic_uuid}/comments[/{id:\d+}]', App\Controllers\Web\Comments::class);
                $group->any('/topics/{topic_uuid}/reactions', App\Controllers\Web\Topics\Reactions::class);
                $group->any(
                    '/topics/{topic_uuid}/comments/upload[/{uuid}]',
                    App\Controllers\Web\Comments\Upload::class
                );
                $group->any('/comments/latest', App\Controllers\Web\Comments\Latest::class);
                $group->any('/comments/{id:\d+}/reactions', App\Controllers\Web\Comments\Reactions::class);
                $group->any('/pages[/{alias}]', App\Controllers\Web\Pages::class);
                $group->any('/tags', App\Controllers\Web\Tags::class);
                $group->any('/reactions', App\Controllers\Web\Reactions::class);
                $group->any('/locate/{uri:.+}', App\Controllers\Web\Redirects::class);
                $group->any('/sitemap', App\Controllers\Web\Sitemap::class);
                $group->any('/rss', App\Controllers\Web\Rss::class);
                $group->any('/search', App\Controllers\Web\Search::class);
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