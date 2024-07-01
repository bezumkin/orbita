<?php

namespace App\Controllers\Admin;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Redirects extends ModelController
{
    protected string $model = Redirect::class;
    protected string|array $scope = 'redirects';

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = trim($this->getProperty('query', ''))) {
            $c->where('title', 'LIKE', "%$query%");
            $c->orWhere('from', 'LIKE', "%$query%");
            $c->orWhere('to', 'LIKE', "%$query%");
        }

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        /** @var Redirect $record */
        $record->from = '/' . ltrim($record->from, '/');
        try {
            $record::getDispatcher($record)->dispatch('GET', '/test/page');
        } catch (\Throwable $e) {
            return $this->failure($e->getMessage());
        }

        return null;
    }
}