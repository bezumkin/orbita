<?php

namespace App\Controllers\Web\Comments;

use App\Models\Comment;
use App\Models\Topic;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelGetController;

class Latest extends ModelGetController
{
    protected string $model = Comment::class;
    protected int $maxLimit = 100;

    public function get(): ResponseInterface
    {
        $limit = abs((int)$this->getProperty('limit', 20));
        if (!$limit || $limit > $this->maxLimit) {
            $limit = $this->maxLimit;
        }

        $ids = Topic::query()
            ->where('active', true)
            ->orderByDesc('last_comment_id')
            ->limit($limit)
            ->pluck('last_comment_id')
            ->toArray();

        $rows = Comment::query()
            ->whereIn('id', $ids)
            ->where('active', true)
            ->with('user:id,username,fullname,avatar_id', 'user.avatar:id,uuid,updated_at')
            ->with('topic:id,uuid,title,category_id,comments_count', 'topic.category:id,title,uri')
            ->orderByDesc('id')
            ->get()
            ->toArray();

        $output = [
            'rows' => $rows,
            'total' => count($rows),
        ];

        return $this->success($output);
    }
}