<?php

namespace App\Models;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Illuminate\Database\Eloquent\Model;

use function FastRoute\simpleDispatcher;

/**
 * @property int $id
 * @property ?string $title
 * @property string $from
 * @property string $to
 * @property ?int $code
 * @property ?string $message
 * @property int $rank
 * @property bool $active
 */
class Redirect extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['active' => 'bool'];

    public static function getDispatcher(?Redirect $add = null): Dispatcher
    {
        $callback = static function (Redirect $redirect, array $params = []) {
            $location = $redirect->to;
            if (!empty($params['id']) && $topic = Topic::query()->find($params['id'])) {
                /** @var Topic $topic */
                $params['uuid'] = $topic->uuid;
            }
            foreach ($params as $key => $value) {
                $location = str_replace("{{$key}}", $value, $location);
            }
            if (!preg_match('#https?://#', $location)) {
                $location = '/' . ltrim($location, '/');
            }

            return str_contains($location, '{') ? null : ['location' => $location, 'code' => $redirect->code];
        };

        return simpleDispatcher(static function (RouteCollector $r) use ($callback, $add) {
            $added = false;

            /** @var Redirect $redirect */
            foreach (self::query()->where('active', true)->orderBy('rank')->cursor() as $redirect) {
                if ($add && !$added && $add->rank < $redirect->rank) {
                    $r->addRoute(['GET', 'OPTIONS'], $add->from, function ($params) use ($callback, $add) {
                        return $callback($add, $params);
                    });
                    $added = true;
                }
                if (!$add || $add->id !== $redirect->id) {
                    $r->addRoute(['GET', 'OPTIONS'], $redirect->from, function ($params) use ($callback, $redirect) {
                        return $callback($redirect, $params);
                    });
                }
            }

            if ($add && !$added) {
                $r->addRoute(['GET', 'OPTIONS'], $add->from, function () use ($callback, $add) {
                    return $callback($add);
                });
            }
        });
    }
}