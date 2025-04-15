<?php

namespace App\Controllers\Web\Topics;

use App\Controllers\Traits\ReactionModelController;
use App\Models\Topic;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Reactions extends Controller
{
    use ReactionModelController;

    public function checkScope(string $method): ?ResponseInterface
    {
        if ($method === 'options') {
            return null;
        }

        /** @var Topic $topic */
        $topic = Topic::query()->where('uuid', $this->getProperty('topic_uuid'))->first();
        if (!$topic || !$topic->active || $topic->hide_reactions) {
            return $this->failure('Not Found', 404);
        }
        $this->model = $topic;

        return null;
    }
}