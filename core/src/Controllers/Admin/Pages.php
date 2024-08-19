<?php

namespace App\Controllers\Admin;

use App\Models\Page;
use App\Services\Redis;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Pages extends ModelController
{
    protected Redis $redis;
    protected string $model = Page::class;
    protected string|array $scope = 'pages';
    private bool $isNew = false;

    public function __construct(Manager $eloquent, Redis $redis)
    {
        parent::__construct($eloquent);
        $this->redis = $redis;
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = trim($this->getProperty('query', ''))) {
            $c->where(static function (Builder $c) use ($query) {
                $c->where('title', 'LIKE', "%$query%");
                $c->orWhere('alias', 'LIKE', "%$query%");
            });
        }

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        $c = Page::query();

        /** @var Page $record */
        $content = $record->content;
        if ($record->external) {
            $record->alias = null;
            if (!$record->title) {
                $record->title = null;
            }
            if (!$content || empty($content['blocks'])) {
                $content = null;
            }
        } elseif (!$content) {
            $content = ['blocks' => []];
        } else {
            $content['blocks'] = array_values($content['blocks']);
        }
        $record->content = $content;

        if ($this->isNew = !$record->exists) {
            if (!$record->rank) {
                $record->rank = Page::query()->count();
            }
        } else {
            $c->where('id', '!=', $record->id);
        }

        if ($record->alias && (clone $c)->where('alias', $record->alias)->count()) {
            return $this->failure('errors.page.alias_exists');
        }

        if ((clone $c)->where('name', $record->title)->count()) {
            return $this->failure('errors.page.name_exists');
        }

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        /** @var Page $record */
        foreach (Page::query()->orderBy('rank')->orderByDesc('updated_at')->cursor() as $idx => $page) {
            $page->update(['rank' => $idx]);
        }

        $record->processUploadedFiles();

        if ($this->isNew) {
            $this->redis->send('page-create', $this->prepareRow($record));
        } else {
            $this->redis->send('page-update', $this->prepareRow($record));
        }
        $this->redis->clearRoutesCache();

        return $record;
    }

    protected function beforeDelete(Model $record): ?ResponseInterface
    {
        $array = $this->prepareRow($record);
        $array['active'] = false;

        $this->redis->send('page-update', $array);
        $this->redis->clearRoutesCache();

        return null;
    }
}