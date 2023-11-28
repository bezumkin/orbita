<?php

namespace App\Controllers\Admin;

use App\Controllers\Traits\FileModelController;
use App\Models\Topic;
use App\Services\Socket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Topics extends ModelController
{
    use FileModelController;

    protected string $model = Topic::class;
    protected string|array $scope = 'topics';
    public array $attachments = ['cover'];
    private bool $isNew = false;

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
        $c->with('cover:id,uuid,updated_at');

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        /** @var Topic $record */
        if (!$record->user_id) {
            $record->user_id = $this->user->id;
        }

        if (!$title = $this->getProperty('title')) {
            return $this->failure('errors.topic.no_title');
        }

        $c = Topic::query();
        if ($record->id) {
            $c->where('id', '!=', $record->id);
        }
        if ($c->where('title', $title)->count()) {
            return $this->failure('errors.topic.title_exists');
        }

        if ($error = $this->processFiles($record)) {
            return $error;
        }

        if ($record->active && !$record->published_at) {
            $record->published_at = time();
        }
        if (!$record->price) {
            $record->price = null;
        }
        if (!$record->level_id) {
            $record->level_id = null;
        }
        $this->isNew = !$record->exists;

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        /** @var Topic $record */
        $record->processUploadedFiles();

        if ($this->isNew) {
            Socket::send('topic-create', $this->prepareRow($record));
        } else {
            Socket::send('topic-update', $this->prepareRow($record));
        }

        return $record;
    }
}