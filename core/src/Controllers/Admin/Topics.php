<?php

namespace App\Controllers\Admin;

use App\Controllers\Traits\FileModelController;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\TopicTag;
use App\Services\Manticore;
use App\Services\Redis;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Topics extends ModelController
{
    use FileModelController;

    protected Manticore $manticore;
    protected Redis $redis;
    protected string $model = Topic::class;
    protected string|array $scope = 'topics';
    public array $attachments = ['cover'];
    public array $allowedTypes = ['cover' => 'image/'];
    private bool $isNew = false;
    private bool $notifyUsers = false;

    public function __construct(Manager $eloquent, Manticore $manticore, Redis $redis)
    {
        parent::__construct($eloquent);
        $this->manticore = $manticore;
        $this->redis = $redis;
    }

    protected function beforeGet(Builder $c): Builder
    {
        $c->with('cover:id,uuid,updated_at');
        $c->with('tags');

        return $c;
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = trim($this->getProperty('query', ''))) {
            $c->where('title', 'LIKE', "%$query%");
        }

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->with('cover:id,uuid,updated_at');

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        /** @var Topic $record */
        $content = $record->content;
        $content['blocks'] = !empty($content['blocks']) ? array_values($content['blocks']) : [];
        try {
            $record->content = $record::sanitizeContent($content);
        } catch (\Throwable $e) {
            return $this->failure('errors.topic.wrong_content');
        }

        if (!$record->user_id) {
            $record->user_id = $this->user->id;
        }

        if (!$title = $this->getProperty('title')) {
            return $this->failure('errors.topic.no_title');
        }

        $c = Topic::query();
        if ($record->id) {
            $c->where('id', '!=', $record->id);
        }
        if ($c->where('title', $title)->count()) {
            return $this->failure('errors.topic.title_exists');
        }

        if ($error = $this->processFiles($record)) {
            return $error;
        }

        if ($record->active && !$record->published_at) {
            $record->published_at = date('Y-m-d H:i:s');
            $this->notifyUsers = true;
        }
        if (!$record->price) {
            $record->price = null;
        }
        if (!$record->level_id) {
            $record->level_id = null;
        }
        if ($record->active) {
            $record->publish_at = null;
        }
        $this->isNew = !$record->exists;

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        /** @var Topic $record */
        $record->processUploadedFiles();

        // Handle tags
        $updateTags = 0;
        $tags = $this->getProperty('tags');
        if (is_array($tags)) {
            $topicTags = [];
            foreach ($tags as $topicTag) {
                if (!$topicTag['title']) {
                    continue;
                }
                if (!$tag = Tag::query()->where('title', $topicTag['title'])->first()) {
                    $tag = new Tag(['title' => $topicTag['title']]);
                    $tag->save();
                    $updateTags++;
                }
                $topicTags[] = $tag->id;
            }

            foreach ($topicTags as $id) {
                $updateTags += TopicTag::query()->insertOrIgnore(['topic_id' => $record->id, 'tag_id' => $id]);
            }

            $updateTags += $record->topicTags()->whereNotIn('tag_id', $topicTags)->delete();

            if ($updateTags) {
                $cache = \App\Controllers\Web\Tags::getCacheName();
                if (file_exists($cache)) {
                    unlink($cache);
                }
                $this->redis->send('tags');
            }
        }

        /** @var Topic $topic */
        $topic = $this->beforeGet($record->newQuery())->find($record->id);
        $data = $topic->prepareOutput(null);
        unset($data['access']);

        // Send data to socket.io
        if ($this->isNew) {
            $this->redis->send('topic-create', $data);
        } else {
            $this->redis->send('topic-update', $data);
        }
        $this->redis->clearRoutesCache();

        // Create notifications
        if ($this->notifyUsers) {
            $topic->createNotifications();
            $this->redis->send('topic-publish', $data);
        }

        // Update search index
        $index = $this->manticore->getIndex();
        if ($topic->active) {
            $index->replaceDocument($topic->getSearchData(), $topic->id);
        } else {
            $index->deleteDocument($topic->id);
        }

        return $topic;
    }

    public function delete(): ResponseInterface
    {
        $key = $this->getPrimaryKey();
        /** @var Topic $record */
        if (!$key || !$record = Topic::query()->find($key)) {
            return $this->failure('Not Found', 404);
        }
        $record->delete();

        $this->redis->send('topic-delete', $record->toArray());
        $this->redis->clearRoutesCache();
        ($this->manticore->getIndex())->deleteDocument($record->id);

        return $this->success();
    }

    public function prepareRow(Model $object): array
    {
        /** @var Topic $object */
        $array = $object->toArray();
        if ($object->publish_at) {
            $array['publish_at'] = $object->publish_at->toDateTimeString();
        }
        if (isset($array['content'])) {
            $array['content']['blocks'] = !empty($array['content']['blocks'])
                ? array_values($array['content']['blocks'])
                : [];
        }

        return $array;
    }
}