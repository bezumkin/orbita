<?php

namespace App\Controllers\Admin;

use App\Controllers\Traits\RankedModelController;
use App\Models\Category;
use App\Services\Redis;
use App\Services\Utils;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Categories extends ModelController
{
    use RankedModelController;

    protected Redis $redis;
    protected string|array $scope = 'topics';
    protected string $model = Category::class;
    protected array $systemURIs = ['admin', 'pages', 'topics', 'user', 'index', 'search'];
    private bool $isNew = false;

    public function __construct(Manager $eloquent, Redis $redis)
    {
        parent::__construct($eloquent);
        $this->redis = $redis;
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = $this->getProperty('query')) {
            $c->where(function (Builder $c) use ($query) {
                $c->where('title', 'like', "%$query%");
                $c->orWhere('uri', 'like', "%$query%");
            });
        }

        if ($exclude = (int)$this->getProperty('exclude')) {
            $c->where('id', '!=', $exclude);
        }

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        if ($this->getProperty('combo')) {
            $c->select('id', 'title', 'uri');
        } else {
            $c->withCount('topics');
        }

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        /** @var Category $record */
        if (!$record->title) {
            return $this->failure('errors.category.no_title');
        }
        if (!$record->uri) {
            return $this->failure('errors.category.no_uri');
        }

        $record->uri = strtolower($record->uri);
        if (!filter_var(Utils::getSiteUrl() . $record->uri, FILTER_VALIDATE_URL)) {
            return $this->failure('errors.category.wrong_uri');
        }
        if (in_array($record->uri, $this->systemURIs, true)) {
            return $this->failure('errors.category.system_uri');
        }

        $this->isNew = !$record->exists;
        $c = Category::query();
        if (!$this->isNew) {
            $c->where('id', '!=', $record->id);
        } elseif (!$record->rank) {
            $record->rank = (clone $c)->count();
        }

        if ((clone $c)->where('title', $record->title)->count()) {
            return $this->failure('errors.category.title_exists');
        }
        if ((clone $c)->where('uri', $record->uri)->count()) {
            return $this->failure('errors.category.uri_exists');
        }

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        /** @var Category $record */
        if ($this->isNew) {
            $this->redis->send('category-create', $record->prepareOutput());
        } else {
            $this->redis->send('category-update', $record->prepareOutput());
        }
        $this->redis->clearRoutesCache();

        return $record;
    }

    protected function beforeDelete(Model $record): ?ResponseInterface
    {
        /** @var Category $record */
        $record->active = false;
        $this->redis->send('category-update', $record->prepareOutput());

        return null;
    }
}