<?php

namespace App\Controllers\Web;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Controllers\ModelGetController;

class Reactions extends ModelGetController
{
    protected string $model = Reaction::class;

    protected function beforeGet(Builder $c): Builder
    {
        return $c->where('active', true);
    }

    protected function beforeCount(Builder $c): Builder
    {
        return $c->where('active', true);
    }

    protected function afterCount(Builder $c): Builder
    {
        return $c->select('id', 'title', 'emoji', 'rank');
    }

    protected function addSorting(Builder $c): Builder
    {
        return $c->orderBy('rank');
    }
}