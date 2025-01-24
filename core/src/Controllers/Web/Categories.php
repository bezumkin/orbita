<?php

namespace App\Controllers\Web;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Vesp\Controllers\ModelGetController;

class Categories extends ModelGetController
{
    protected string $model = Category::class;
    protected string|array $primaryKey = ['uri'];

    protected function beforeGet(Builder $c): Builder
    {
        if (!$this->user || !$this->user->hasScope('topics/get')) {
            $c->where('active', true);
        }

        return $c;
    }

    protected function beforeCount(Builder $c): Builder
    {
        return $this->beforeGet($c);
    }

    public function prepareRow(Model $object): array
    {
        /** @var Category $object */
        return $object->prepareOutput();
    }
}