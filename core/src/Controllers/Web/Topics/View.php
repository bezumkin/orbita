<?php

namespace App\Controllers\Web\Topics;

use App\Models\Topic;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class View extends Controller
{
    public function post(): ResponseInterface
    {
        /** @var Topic $topic */
        $topic = Topic::query()->where('uuid', $this->getProperty('uuid'))->first();
        if (!$topic || !$topic->active) {
            return $this->failure('Not Found', 404);
        }

        return $this->success([
            'views_count' => $topic->saveView($this->user),
        ]);
    }
}