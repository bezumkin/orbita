<?php

namespace App\Controllers\Web;

use App\Models\Level;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Controllers\ModelGetController;

class Levels extends ModelGetController
{
    protected string $model = Level::class;

    protected function beforeCount(Builder $c): Builder
    {
        $c->where('active', true);

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->with('cover:id,uuid,updated_at');

        return $c;
    }

    protected function addSorting(Builder $c): Builder
    {
        $c->orderBy('price');

        return $c;
    }
}