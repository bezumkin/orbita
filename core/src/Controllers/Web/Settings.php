<?php

namespace App\Controllers\Web;

use App\Models\Category;
use App\Models\Level;
use App\Models\Page;
use App\Models\Reaction;
use App\Models\Setting;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Settings extends ModelController
{
    protected string $model = Setting::class;

    public function get(): ResponseInterface
    {
        return $this->success([
            'variables' => $this->getVariables(),
            'settings' => $this->getSettings(),
            'categories' => $this->getCategories(),
            'pages' => $this->getPages(),
            'levels' => $this->getLevels(),
            'reactions' => $this->getReactions(),
        ]);
    }

    protected function getVariables(): array
    {
        return [
            'TZ' => getenv('TZ') ?: 'Europe/Moscow',
            'SITE_URL' => getenv('SITE_URL') ?: 'http://127.0.0.1:8080/',
            'API_URL' => getenv('API_URL') ?: '/api/',
            'JWT_EXPIRE' => getenv('JWT_EXPIRE') ?: '2592000',
            'CURRENCY' => getenv('CURRENCY') ?: 'RUB',
            'REGISTER_ENABLED' => getenv('REGISTER_ENABLED') !== false ? getenv('REGISTER_ENABLED') : '1',
            'HIDE_WIDGETS' => getenv('HIDE_WIDGETS') ?: '',
            // Deprecated, better use HIDE_WIDGETS
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
            'TOPICS_CHANGE_PUBDATE' => getenv('TOPICS_CHANGE_PUBDATE') ?: '0',
            'CHART_PAYMENTS_DISABLE' => getenv('CHART_PAYMENTS_DISABLE'),
            'CHART_USERS_DISABLE' => getenv('CHART_USERS_DISABLE'),
            'CHART_SUBSCRIPTIONS_DISABLE' => getenv('CHART_SUBSCRIPTIONS_DISABLE'),
        ];
    }

    protected function getSettings(): array
    {
        $c = Setting::query()->orderBy('rank');

        $items = [];
        /** @var Setting $setting */
        foreach ($c->cursor() as $setting) {
            $array = $setting->only('key', 'value');
            if (!empty($setting->value) && in_array($setting->type, Setting::JSON_TYPES, true)) {
                $array['value'] = json_decode($setting->value, true);
            }
            $items[$array['key']] = $array['value'];
        }

        return $items;
    }

    protected function getCategories(): array
    {
        $c = Category::query()
            ->where('active', true)
            ->orderBy('rank');

        $items = [];
        foreach ($c->cursor() as $item) {
            /** @var Category $item */
            $items[] = $item->prepareOutput();
        }

        return $items;
    }

    protected function getPages(): array
    {
        $c = Page::query()
            ->where('active', true)
            ->orderBy('rank');

        $items = [];
        foreach ($c->cursor() as $item) {
            /** @var Page $item */
            $items[] = $item->prepareOutput(true);
        }

        return $items;
    }

    protected function getLevels(): array
    {
        $c = Level::query()
            ->where('active', true)
            ->with('cover:id,uuid,updated_at')
            ->orderBy('price')
            ->get();

        $items = [];
        foreach ($c as $item) {
            /** @var Level $item */
            $items[] = $item->prepareOutput();
        }

        return $items;
    }

    protected function getReactions(): array
    {
        $c = Reaction::query()
            ->where('active', true)
            ->select('id', 'title', 'emoji', 'rank')
            ->orderBy('rank');

        $items = [];
        foreach ($c->cursor() as $item) {
            /** @var Reaction $item */
            $items[] = $item->prepareOutput();
        }

        return $items;
    }
}