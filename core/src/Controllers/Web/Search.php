<?php

namespace App\Controllers\Web;

use App\Services\Manticore;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Search extends Topics
{
    protected Manticore $manticore;
    protected array $ids = [];
    protected array $scores = [];

    public function __construct(Manager $eloquent, Manticore $manticore)
    {
        parent::__construct($eloquent);
        $this->manticore = $manticore;
    }

    protected function beforeCount(Builder $c): Builder
    {
        $limit = getenv('SEARCH_LIMIT') ?: 100;
        $this->maxLimit = $limit;

        if ($query = trim($this->getProperty('query', ''))) {
            $search = $this->manticore->getSearch();
            $search->option('field_weights', [
                'title' => 50,
                'tags' => 30,
                'teaser' => 20,
                'content' => 10,
            ]);
            $search->highlight(
                ['teaser', 'content'],
                [
                    'allow_empty' => true,
                    'use_boundaries' => true,
                    'snippet_boundary' => 'paragraph',
                    'html_strip_mode' => 'strip',
                    'before_match' => '<mark>',
                    'after_match' => '</mark>',
                    'weight_order' => true,
                ]
            );
            $search->trackScores(true);
            $search->stripBadUtf8(true);
            $search->limit($limit);
            if ($this->getProperty('sort') === 'date') {
                $search->sort('published_at', 'desc');
            }

            $results = $search->match($query)->get();
            foreach ($results as $result) {
                $id = (int)$result->getId();
                $this->ids[] = $id;
                $this->scores[$id] = $result->getScore();
            }
        }

        if (!$this->ids) {
            $c->where('id', -1);
        } else {
            $c->whereIn('id', $this->ids);
        }

        return parent::beforeCount($c);
    }

    protected function addSorting(Builder $c): Builder
    {
        if ($this->ids) {
            $c->orderByRaw('FIELD (id, ' . implode(', ', $this->ids) . ') ASC');
        }

        return $c;
    }

    public function prepareRow(Model $object): array
    {
        $array = parent::prepareRow($object);
        $array['search_score'] = $this->scores[$array['id']] ?? 0;

        return $array;
    }
}