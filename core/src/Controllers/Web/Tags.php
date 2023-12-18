<?php

namespace App\Controllers\Web;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Controllers\ModelGetController;

class Tags extends ModelGetController
{
    protected string $model = Tag::class;
    protected string|array $primaryKey = ['topic_id', 'tag_id'];

    protected function beforeCount(Builder $c): Builder
    {
        $c->whereHas('topicTags');

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        return $c->withCount('topicTags');
    }

    protected function addSorting(Builder $c): Builder
    {
        return $c->orderByDesc('topic_tags_count');
    }
}