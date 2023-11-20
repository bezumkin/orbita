<?php

namespace App\Controllers\Web;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Controllers\ModelGetController;

class Pages extends ModelGetController
{
    protected string $model = Page::class;
    protected string|array $primaryKey = ['alias'];

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
        return $c->select('id', 'title', 'alias', 'position', 'rank');
    }

    protected function addSorting(Builder $c): Builder
    {
        return $c->orderBy('rank');
    }
}