<?php

namespace App\Controllers\Web;

use App\Models\Level;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    public function prepareRow(Model $object): array
    {
        /** @var Level $object */
        return $object->prepareOutput();
    }
}