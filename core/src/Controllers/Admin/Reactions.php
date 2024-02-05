<?php

namespace App\Controllers\Admin;

use App\Models\Reaction;
use App\Services\Socket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Reactions extends ModelController
{
    protected string $model = Reaction::class;
    protected string|array $scope = 'reactions';
    private bool $isNew = false;

    protected function addSorting(Builder $c): Builder
    {
        return $c->orderBy('rank');
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        $c = Reaction::query();

        /** @var Reaction $record */
        if ($this->isNew = !$record->exists) {
            if (!$record->rank) {
                $record->rank = Reaction::query()->count();
            }
        } else {
            $c->where('id', '!=', $record->id);
        }

        if ($c->where('title', $record->title)->count()) {
            return $this->failure('errors.reaction.title_exists');
        }

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        if ($this->isNew) {
            Socket::send('reaction-create', $this->prepareRow($record));
        } else {
            Socket::send('reaction-update', $this->prepareRow($record));
        }

        return $record;
    }

    public function post(): ResponseInterface
    {
        if ($reactions = $this->getProperty('reactions')) {
            foreach ($reactions as $row) {
                if (isset($row['id']) && isset($row['rank'])) {
                    Reaction::query()->where('id', $row['id'])->update(['rank' => $row['rank']]);
                }
            }
            Socket::send('reactions');
        }

        return $this->success();
    }
}