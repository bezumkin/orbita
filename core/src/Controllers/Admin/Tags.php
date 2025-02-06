<?php

namespace App\Controllers\Admin;

use App\Models\Tag;
use App\Services\Redis;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Tags extends ModelController
{
    protected string|array $scope = 'topics';
    protected string $model = Tag::class;
    protected Redis $redis;

    public function __construct(Manager $eloquent, Redis $redis)
    {
        parent::__construct($eloquent);
        $this->redis = $redis;
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = trim($this->getProperty('query', ''))) {
            $c->where('title', 'LIKE', "%$query%");
        }

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->withCount('topics');

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        /** @var Tag $record */
        $title = trim($record->title);
        if (!$title) {
            return $this->failure('errors.tag.no_title');
        }

        $c = Tag::query()->where('title', $title);
        if ($record->id) {
            $c->where('id', '!=', $record->id);
        }
        if ($c->count()) {
            return $this->failure('errors.tag.title_exists');
        }

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        $this->redis->send('tags');

        return parent::afterSave($record);
    }

    public function delete(): ResponseInterface
    {
        $response = parent::delete();
        $this->redis->del('tags');
        $this->redis->send('tags');

        return $response;
    }
}