<?php

namespace App\Controllers\Web\Topics;

use App\Models\Topic;
use App\Services\Socket;
use Carbon\Carbon;
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

        $data = [
            'id' => $topic->id,
            'uuid' => $topic->uuid,
            'unseen_comments_count' => 0,
            'viewed_at' => Carbon::now()->toJSON(),
            'user_id' => 0,
        ];

        if ($view = $topic->saveView($this->user)) {
            $data['user_id'] = $view->user_id;
            $data['unseen_comments_count'] = $topic->comments()
                ->where('created_at', '>', $view->timestamp)
                ->where('active', true)
                ->where('user_id', '!=', $this->user->id)
                ->count();
        }
        $data['views_count'] = $topic->views_count;
        Socket::send('topic-views', $data);

        return $this->success($data);
    }
}