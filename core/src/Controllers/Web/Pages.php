<?php

namespace App\Controllers\Web;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    protected function addSorting(Builder $c): Builder
    {
        return $c->orderBy('rank');
    }

    public function prepareRow(Model $object): array
    {
        $columns = ['id', 'rank', 'position', 'name', 'external'];
        /** @var Page $object */
        if ($object->external) {
            $columns[] = 'link';
            $columns[] = 'blank';
        } else {
            $columns[] = 'alias';
            $columns[] = 'title';
            if ($this->getPrimaryKey()) {
                $columns[] = 'content';
            }
        }

        $array = $object->only($columns);
        if (!empty($array['content']) && !empty($array['content']['blocks'])) {
            $array['content']['blocks'] = array_values($array['content']['blocks']);
        }

        return $array;
    }
}