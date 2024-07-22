<?php

namespace App\Services;

use App\Models\Topic;
use Manticoresearch\Client;
use Manticoresearch\Exceptions\ResponseException;
use Manticoresearch\Index;
use Manticoresearch\Search;
use Vesp\Services\Eloquent;

class Manticore
{
    protected string $indexName = 'topics';
    protected Eloquent $eloquent;
    protected Client $client;

    public function __construct(Eloquent $eloquent)
    {
        $this->eloquent = $eloquent;
        $this->client = new Client(['host' => 'manticore', 'port' => 9308]);

        $this->getIndex();
    }

    public function createIndex(): Index
    {
        $morphology = ['soundex', 'metaphone'];
        $locales = array_map('trim', explode(',', getenv('LOCALES')));
        if (in_array('en', $locales, true)) {
            $morphology[] = 'lemmatize_en_all';
        }
        if (in_array('ru', $locales, true)) {
            $morphology[] = 'lemmatize_ru_all';
        }
        if (in_array('de', $locales, true)) {
            $morphology[] = 'lemmatize_de_all';
        }

        $index = $this->client->index($this->indexName);
        $index->create([
            'uuid' => ['type' => 'string'],
            'title' => ['type' => 'text'],
            'teaser' => ['type' => 'text'],
            'content' => ['type' => 'text'],
            'tags' => ['type' => 'text'],
            'published_at' => ['type' => 'timestamp'],
        ], [
            'morphology' => implode(',', $morphology),
        ], true);

        return $index;
    }

    public function getIndex(): Index
    {
        return $this->client->index($this->indexName);
    }

    public function getSearch(): Search
    {
        return (new Search($this->client))
            ->setIndex($this->indexName);
    }

    public function index(bool $truncate = true): int
    {
        $index = $this->getIndex();
        try {
            $schema = $index->describe();
            // Migrate from old wrong index
            if ($schema['title']['Type'] !== 'text') {
                $index->drop(true);
                $this->createIndex();
            }
        } catch (ResponseException $e) {
            $index = $this->createIndex();
        }

        if ($truncate) {
            $index->truncate();
        }

        /** @var Topic $topic */
        $topics = Topic::query()
            ->where('active', true)
            ->with('tags')
            ->get();

        foreach ($topics as $topic) {
            $index->replaceDocument($topic->getSearchData(), $topic->id);
        }
        $index->optimize();

        return $index->status()['indexed_documents'];
    }
}
