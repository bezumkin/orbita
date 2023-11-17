<?php

namespace App\Controllers\Web\Comments;

use App\Models\Comment;
use App\Models\Topic;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelGetController;

class Latest extends ModelGetController
{
    protected string $model = Comment::class;

    public function get(): ResponseInterface
    {
        $ids = Topic::query()
            ->where('active', true)
            ->orderByDesc('last_comment_id')
            ->limit($this->getProperty('limit', 20))
            ->pluck('last_comment_id')
            ->toArray();

        $rows = Comment::query()
            ->whereIn('id', $ids)
            ->where('active', true)
            ->with('user:id,username,fullname,avatar_id', 'user.avatar:id,uuid,updated_at')
            ->with('topic:id,uuid,title,comments_count')
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