<?php

namespace App\Controllers\Web\Topics;

use App\Models\Topic;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class View extends Controller
{
    public function post(): ResponseInterface
    {
        $isAdmin = $this->user && $this->user->hasScope('topics/patch');

        /** @var Topic $topic */
        $topic = Topic::query()->where('uuid', $this->getProperty('topic_uuid'))->first();
        if (!$topic || (!$topic->active && !$isAdmin)) {
            return $this->failure('Not Found', 404);
        }

        if ($view = $topic->saveView($this->user)) {
            return $this->success([
                'views_count' => $view->topic->views_count,
                'viewed_at' => $view->timestamp,
            ]);
        }

        return $this->success();
    }
}