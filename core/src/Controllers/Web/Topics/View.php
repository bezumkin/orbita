<?php

namespace App\Controllers\Web\Topics;

use App\Models\Topic;
use App\Services\Socket;
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

        $view = $topic->saveView($this->user);
        Socket::send('topic-views', [
            'id' => $topic->id,
            'views_count' => $topic->views_count,
        ]);

        return $this->success($view ? ['views_count' => $topic->views_count, 'viewed_at' => $view->timestamp] : []);
    }
}