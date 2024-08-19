<?php

namespace App\Controllers\Traits;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Reaction;
use App\Models\Topic;
use App\Models\TopicReaction;
use App\Models\User;
use App\Services\Socket;
use Psr\Http\Message\ResponseInterface;

/**
 * @property ?User $user
 * @method getProperty(string $key, $default = null)
 * @method success(array $data, int $code = 200)
 * @method failure(string $message, int $code = 422)
 */
trait ReactionModelController
{
    protected Topic|Comment|null $model = null;

    public function get(): ResponseInterface
    {
        $reactions = $data = [];
        $userReactions = $this->model->userReactions()
            ->selectRaw('COUNT(reaction_id) as reactions, reaction_id')
            ->groupBy('reaction_id');
        foreach ($userReactions->cursor() as $tmp) {
            $reactions[$tmp->reaction_id] = $tmp->reactions;
        }

        foreach (Reaction::query()->where('active', true)->cursor() as $reaction) {
            $data[$reaction->id] = $reactions[$reaction->id] ?? 0;
        }

        $reactionId = null;
        if ($this->user) {
            /** @var TopicReaction|CommentReaction $tmp */
            if ($tmp = $this->model->userReactions()->where('user_id', $this->user->id)->first()) {
                $reactionId = $tmp->reaction_id;
            }
        }

        return $this->success(['reactions' => $data, 'reaction' => $reactionId]);
    }

    public function post(): ResponseInterface
    {
        if (!$this->user) {
            return $this->failure('Authentication required', 401);
        }
        $reactionId = $this->getProperty('reaction_id');

        /** @var TopicReaction|CommentReaction $tmp */
        if ($reaction = $this->model->userReactions()->where('user_id', $this->user->id)->first()) {
            $reaction->update([
                'reaction_id' => $reactionId,
                'timestamp' => time(),
            ]);
        } else {
            $this->model->userReactions()->create([
                'user_id' => $this->user->id,
                'reaction_id' => $reactionId,
            ]);
        }
        $this->sendInfoToSocket();

        return $this->get();
    }

    public function delete(): ResponseInterface
    {
        if ($this->user && $reaction = $this->model->userReactions()->where('user_id', $this->user->id)->first()) {
            $reaction->delete();
        }
        $this->sendInfoToSocket();

        return $this->get();
    }

    protected function sendInfoToSocket(): void
    {
        if ($this->model instanceof Topic) {
            Socket::send('topic-reactions', [
                'id' => $this->model->id,
                'reactions_count' => $this->model->refresh()->reactions_count,
            ]);
        } elseif ($this->model instanceof Comment) {
            Socket::send('comment-reactions', [
                'id' => $this->model->id,
                'reactions_count' => $this->model->refresh()->reactions_count,
            ]);
        }
    }
}