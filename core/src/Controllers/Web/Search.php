<?php

namespace App\Controllers\Web;

use App\Models\Topic;
use App\Services\Manticore;
use Illuminate\Database\Capsule\Manager;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Search extends Controller
{
    protected Manticore $manticore;

    public function __construct(Manager $eloquent, Manticore $manticore)
    {
        parent::__construct($eloquent);
        $this->manticore = $manticore;
    }

    public function get(): ResponseInterface
    {
        $rows = [];
        if ($query = $this->getProperty('query')) {
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
            $search->limit(getenv('SEARCH_LIMIT') ?: 100);
            if ($this->getProperty('sort', '') === 'date') {
                $search->sort('published_at', 'desc');
            }

            $results = $search->match($query)->get();
            foreach ($results as $result) {
                /** @var Topic $topic */
                if ($topic = Topic::query()->find($result->getId())) {
                    $row = $topic->prepareOutput($this->user);
                    $row['score'] = $result->getScore();
                    $rows[] = $row;
                }
            }
        }

        return $this->success([
            'rows' => $rows,
            'total' => count($rows),
        ]);
    }

}