<?php

namespace App\Controllers\Web;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Vesp\Controllers\ModelGetController;

class Settings extends ModelGetController
{
    protected string $model = Setting::class;

    protected function addSorting(Builder $c): Builder
    {
        $c->orderBy('rank');

        return $c;
    }

    public function prepareRow(Model $object): array
    {
        /** @var Setting $object */
        $array = $object->only('key', 'value');
        if (!empty($object->value) && in_array($object->type, Setting::JSON_TYPES, true)) {
            $array['value'] = json_decode($object->value, true);
        }

        return $array;
    }
}