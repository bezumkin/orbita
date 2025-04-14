<?php

namespace App\Controllers\Web;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vesp\Controllers\ModelGetController;

class Topics extends ModelGetController
{
    protected string $model = Topic::class;
    protected string|array $primaryKey = ['uuid'];

    protected function commonConditions(Builder $c): Builder
    {
        if (!$this->user || !$this->user->hasScope('topics/patch')) {
            $c->where('active', true);
            $c->where(static function (Builder $c) {
                $c->whereNull('category_id');
                $c->orWhereHas('category', function (Builder $c) {
                    $c->where('active', true);
                });
            });
        }
        if ($this->user) {
            $c->with('views', function (HasMany $c) {
                $c->where('user_id', $this->user->id);
            });
            $c->with('userReactions', function (HasMany $c) {
                $c->where('user_id', $this->user->id);
            });
        }
        if (getenv('TOPICS_SHOW_AUTHOR')) {
            $c->with('user:id,fullname');
        }

        return $c;
    }

    protected function beforeGet(Builder $c): Builder
    {
        return $this->commonConditions($c);
    }

    protected function beforeCount(Builder $c): Builder
    {
        $c = $this->commonConditions($c);
        if ($categoryId = (int)$this->getProperty('category_id')) {
            $c->where('category_id', $categoryId);
        }
        if ($tags = $this->getProperty('tags')) {
            $tags = explode(',', $tags);
            $c->whereHas('topicTags', static function (Builder $c) use ($tags) {
                $c->whereIn('tag_id', $tags)
                    ->havingRaw('COUNT(tag_id) = ?', [count($tags)]);
            });
        }
        if ($type = $this->getProperty('type')) {
            $c->where('type', $type);
        }

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->with('cover:id,uuid,updated_at');
        $c->with('category:id,title,uri');
        $c->with('tags');

        return $c;
    }

    protected function addSorting(Builder $c): Builder
    {
        $sort = $this->getProperty('sort', 'date');
        $dir = $this->getProperty('reverse') ? 'asc' : 'desc';
        match ($sort) {
            'views' => $c->orderBy('views_count', $dir),
            'comments' => $c->orderBy('comments_count', $dir),
            'reactions' => $c->orderBy('reactions_count', $dir),
            default => $c->orderByRaw(($dir === 'asc' ? '+' : '-') . 'published_at, id ' . $dir),
        };

        return $c;
    }

    public function prepareRow(Model $object): array
    {
        /** @var Topic $object */
        return $object->prepareOutput($this->user, !$this->getPrimaryKey());
    }
}