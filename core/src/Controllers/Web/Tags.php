<?php

namespace App\Controllers\Web;

use App\Models\Tag;
use App\Models\Topic;
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
    protected const int CACHE_TIME = 600;
    protected array $tags = [];

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

    public static function getCacheName(): string
    {
        return rtrim(getenv('CACHE_DIR'), '/') . '/tags.json';
    }

    protected function getCache(): ?array
    {
        $file = self::getCacheName();
        if (self::CACHE_TIME && file_exists($file) && filemtime($file) + self::CACHE_TIME > time()) {
            return json_decode(file_get_contents($file), true);
        }

        return null;
    }

    protected function setCache(array $data): void
    {
        file_put_contents(self::getCacheName(), json_encode($data));
    }
}