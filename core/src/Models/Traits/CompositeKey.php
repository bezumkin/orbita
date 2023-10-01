<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property array primaryKey
 * @method Builder newQuery
 */
trait CompositeKey
{
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * @param $key
     * @return mixed|void
     */
    public function getAttribute($key)
    {
        if (is_array($key)) {
            return;
        }

        return parent::getAttribute($key);
    }

    public function getKeyName(): string
    {
        $tmp = array_merge([], $this->primaryKey);

        return array_pop($tmp);
    }

    public function getKey(): array
    {
        $key = [];
        foreach ($this->primaryKey as $item) {
            $key[$item] = $this->getAttribute($item);
        }

        return $key;
    }

    protected function setKeysForSaveQuery($query): Builder
    {
        foreach ($this->getKey() as $key => $value) {
            $query->where($key, $this->original[$key] ?? $value);
        }

        return $query;
    }

    public static function find($ids, $columns = ['*'])
    {
        $me = new self();
        $query = $me->newQuery();
        foreach ($me->getKey() as $key) {
            $query->where($key, $ids[$key]);
        }

        return $query->first($columns);
    }
}
