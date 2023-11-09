<?php

namespace App\Controllers\Admin;

use App\Controllers\Traits\FileModelController;
use App\Models\Level;
use App\Services\Socket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;


class Levels extends ModelController
{
    use FileModelController;

    protected string $model = Level::class;
    protected string|array $scope = 'levels';
    public array $attachments = ['cover'];

    protected function beforeGet(Builder $c): Builder
    {
        $c->with('cover:id,uuid,updated_at');

        return $c;
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = $this->getProperty('query')) {
            $c->where('title', 'LIKE', "%$query%");
        }

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        if ($this->getProperty('combo')) {
            $c->select('id', 'title', 'price', 'active');
        } else {
            $c->with('cover:id,uuid,updated_at');
        }

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        /** @var Level $record */
        if (!$title = $this->getProperty('title')) {
            return $this->failure('errors.level.no_title');
        }
        if (!$price = (int)$this->getProperty('price')) {
            return $this->failure('errors.level.no_price');
        }

        $c = Level::query();
        if ($record->id) {
            $c->where('id', '!=', $record->id);
        }
        if ((clone $c)->where('title', $title)->count()) {
            return $this->failure('errors.level.title_exists');
        }
        if ((clone $c)->where('price', $price)->count()) {
            return $this->failure('errors.level.price_exists');
        }

        if ($error = $this->processFiles($record)) {
            return $error;
        }

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        Socket::send('levels', $this->prepareRow($record));

        return $record;
    }
}