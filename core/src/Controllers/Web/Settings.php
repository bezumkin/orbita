<?php

namespace App\Controllers\Web;

use App\Models\Setting;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Settings extends ModelController
{
    protected string $model = Setting::class;

    public function get(): ResponseInterface
    {
        $settings = [];
        /** @var Setting $setting */
        foreach (Setting::query()->orderBy('rank')->cursor() as $setting) {
            $array = $setting->only('key', 'value');
            if (!empty($setting->value) && in_array($setting->type, Setting::JSON_TYPES, true)) {
                $array['value'] = json_decode($setting->value, true);
            }
            $settings[$array['key']] = $array['value'];
        }

        $variables = [
            'TZ' => getenv('TZ') ?: 'Europe/Moscow',
            'SITE_URL' => getenv('SITE_URL') ?: 'http://127.0.0.1:8080/',
            'API_URL' => getenv('API_URL') ?: '/api/',
            'JWT_EXPIRE' => getenv('JWT_EXPIRE') ?: '2592000',
            'CURRENCY' => getenv('CURRENCY') ?: 'RUB',
            'REGISTER_ENABLED' => getenv('REGISTER_ENABLED') !== false ? getenv('REGISTER_ENABLED') : '1',
            'COMMENTS_SHOW_ONLINE' => getenv('COMMENTS_SHOW_ONLINE') !== false ? getenv('COMMENTS_SHOW_ONLINE') : '1',
            'COMMENTS_MAX_LEVEL' => getenv('COMMENTS_MAX_LEVEL') ?: '3',
            'COMMENTS_EDIT_TIME' => getenv('COMMENTS_EDIT_TIME') ?: '600',
            'COMMENTS_REQUIRE_SUBSCRIPTION' => getenv('COMMENTS_REQUIRE_SUBSCRIPTION') ?: '0',
            'EDITOR_TOPIC_BLOCKS' => getenv('EDITOR_TOPIC_BLOCKS') ?: '',
            'EDITOR_COMMENT_BLOCKS' => getenv('EDITOR_COMMENT_BLOCKS') ?: '',
            'PAYMENT_SERVICES' => getenv('PAYMENT_SERVICES') ?: '',
            'PAYMENT_SUBSCRIPTIONS' => getenv('PAYMENT_SUBSCRIPTIONS') ?: '',
            'CONNECTION_SERVICES' => getenv('CONNECTION_SERVICES') ?: '',
            'ADMIN_SECTIONS' => getenv('ADMIN_SECTIONS') ?: '',
            'DOWNLOAD_MEDIA_ENABLED' => getenv('DOWNLOAD_MEDIA_ENABLED') ?: '0',
            'EXTRACT_VIDEO_AUDIO_ENABLED' => getenv('EXTRACT_VIDEO_AUDIO_ENABLED') ?: '0',
            'EXTRACT_VIDEO_THUMBNAILS_ENABLED' => getenv('EXTRACT_VIDEO_THUMBNAILS_ENABLED') !== false
                ? getenv('EXTRACT_VIDEO_THUMBNAILS_ENABLED')
                : '1',
            'TOPICS_SHOW_AUTHOR' => getenv('TOPICS_SHOW_AUTHOR') ?: '0',
            'TOPICS_CHANGE_AUTHOR' => getenv('TOPICS_CHANGE_AUTHOR') ?: '0',
            'CHART_PAYMENTS_DISABLE' => getenv('CHART_PAYMENTS_DISABLE'),
        ];

        return $this->success([
            'settings' => $settings,
            'variables' => $variables,
        ]);
    }
}