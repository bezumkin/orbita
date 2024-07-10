<?php

namespace App\Controllers\Web;

use App\Services\Manticore;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Manticoresearch\ResultHit;
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
            $search->limit(100);
            if ($this->getProperty('sort', '') === 'date') {
                $search->sort('published_at', 'desc');
            }

            $results = $search->match($query)->get();
            foreach ($results as $result) {
                $rows[] = [
                    'uuid' => $result->uuid,
                    'title' => $result->title,
                    'content' => $this::getHighlight($result),
                    'published_at' => Carbon::createFromTimestamp($result->published_at)->toISOString(),
                    'score' => $result->getScore(),
                ];
            }
        }

        return $this->success([
            'rows' => $rows,
            'total' => count($rows),
        ]);
    }

    protected static function getHighlight(ResultHit $result): string
    {
        $highlight = $result->getHighlight();
        if (!empty($highlight['teaser'])) {
            $text = implode(' ... ', $highlight['teaser']);
        } elseif (!empty($highlight['content'])) {
            $text = trim(implode(' ... ', $highlight['content']), ' .,:\/') . '...';
        } else {
            $text = $result->teaser;
        }

        return $text;
    }

}