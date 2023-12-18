<?php

namespace App\Controllers\Admin;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Controllers\ModelGetController;

class Tags extends ModelGetController
{
    protected string|array $scope = 'topics';
    protected string $model = Tag::class;

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = $this->getProperty('query')) {
            $c->where('title', 'LIKE', "%$query%");
        }

        return $c;
    }
}