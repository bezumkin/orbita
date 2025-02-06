<?php

namespace App\Controllers\Web;

use App\Models\Tag;
use App\Models\Topic;
use App\Services\Redis;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Builder;
use KSamuel\FacetedSearch\Filter\ValueIntersectionFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use Vesp\Controllers\ModelGetController;

class Tags extends ModelGetController
{
    protected string $model = Tag::class;
    protected string|array $primaryKey = ['topic_id', 'tag_id'];
    protected Redis $redis;

    public function __construct(Manager $eloquent, Redis $redis)
    {
        parent::__construct($eloquent);
        $this->redis = $redis;
    }

    protected function beforeCount(Builder $c): Builder
    {
        $c->whereHas('topics', static function (Builder $c) {
            $c->where('active', true);
        });

        return $c;
    }

    protected function addSorting(Builder $c): Builder
    {
        return $c->orderBy('title');
    }

    public function prepareList(array $array): array
    {
        $filters = [];
        $selected = $this->getProperty('selected');
        if ($selected && $selected = array_map('trim', explode(',', $selected))) {
            $filters[] = new ValueIntersectionFilter('tag', $selected);
        }

        $query = (new AggregationQuery())->filters($filters)->countItems()->sort()->selfFiltering(true);
        $data = $this->getIndex()->aggregate($query);

        foreach ($array['rows'] as &$row) {
            $row['topics'] = $data['tag'][$row['id']] ?? 0;
        }

        return $array;
    }

    protected function getIndex(): IndexInterface
    {
        $factory = (new Factory())->create(Factory::ARRAY_STORAGE);
        $storage = $factory->getStorage();

        if ($cache = $this->getCache()) {
            $storage->setData($cache);
        } else {
            $topics = Topic::query()->where('active', true)->with('topicTags')->get();
            foreach ($topics as $topic) {
                /** @var Topic $topic */
                foreach ($topic->topicTags as $topicTag) {
                    $storage->addRecord($topic->id, ['tag' => $topicTag->tag_id]);
                }
            }
            $storage->optimize();
            $this->setCache($storage->export());
        }

        return $factory;
    }

    protected function getCache(): ?array
    {
        if ($this->redis->exists('tags')) {
            return json_decode($this->redis->get('tags'), true, 512, JSON_THROW_ON_ERROR);
        }

        return null;
    }

    protected function setCache(array $data): void
    {
        $cacheTTL = getenv('CACHE_PAGES_TIME') ?: 600;
        $this->redis->set('tags', json_encode($data, JSON_THROW_ON_ERROR), 'EX', $cacheTTL);
    }
}