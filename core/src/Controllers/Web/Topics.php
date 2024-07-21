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

    protected function beforeGet(Builder $c): Builder
    {
        if (!$this->user || !$this->user->hasScope('topics/patch')) {
            $c->where('active', true);
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

    protected function beforeCount(Builder $c): Builder
    {
        if (!$this->user || !$this->user->hasScope('topics/patch')) {
            $c->where('active', true);
        }
        if ($tags = $this->getProperty('tags')) {
            $tags = explode(',', $tags);
            $c->whereHas('topicTags', static function (Builder $c) use ($tags){
                $c->whereIn('tag_id', $tags)
                    ->havingRaw('COUNT(tag_id) = ?', [count($tags)]);
            });
        }

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->with('cover:id,uuid,updated_at');
        $c->with('tags');
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

    protected function addSorting(Builder $c): Builder
    {
        if ($this->getProperty('reverse')) {
            $c->orderByRaw('+published_at, id ASC');
        } else {
            $c->orderByRaw('-published_at, id DESC');
        }


        return $c;
    }

    public function prepareRow(Model $object): array
    {
        /** @var Topic $object */
        return $object->prepareOutput($this->user, !$this->getPrimaryKey());
    }
}