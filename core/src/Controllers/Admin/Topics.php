<?php

namespace App\Controllers\Admin;

use App\Controllers\Traits\FileModelController;
use App\Models\File;
use App\Models\Level;
use App\Models\Topic;
use App\Models\TopicFile;
use App\Services\Socket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Topics extends ModelController
{
    use FileModelController;

    protected string $model = Topic::class;
    protected string|array $scope = 'topics';
    public array $attachments = ['cover'];

    protected function beforeGet(Builder $c): Builder
    {
        $c->with('cover:id,uuid,updated_at');

        return $c;
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = $this->getProperty('query')) {
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
        if (!$record->user_id) {
            $record->user_id = $this->user->id;
        }

        if (!$title = $this->getProperty('title')) {
            return $this->failure('errors.topic.no_title');
        }

        $c = Level::query();
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
            $record->published_at = time();
        }

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        /** @var Topic $record */
        $content =  $record->content;
        $blocks = $content['blocks'];
        $fileTypes = ['image', 'file', 'audio', 'video'];
        $files = [];
        foreach ($blocks as $idx => $block) {
            $type = $block['type'];
            if (in_array($type, $fileTypes, true)) {
                if (empty($block['data']['id'])) {
                    unset($blocks[$idx]);
                } else {
                    $files[$block['data']['id']] = $type;
                }
            }
        }
        $content['blocks'] = $blocks;
        $record->content = $content;
        $record->save();

        // Save topic files
        foreach ($files as $id => $type) {
            TopicFile::query()->insertOrIgnore(['topic_id' => $record->id, 'file_id' => $id, 'type' => $type]);
            /** @var File $file */
            if ($file = File::query()->where('temporary', true)->find($id)) {
                $file->temporary = false;
                $file->save();
            }
        }

        // Clean abandoned topic files
        $ids = array_keys($files);
        /** @var TopicFile $topicFile */
        foreach ($record->topicFiles()->whereNotIn('file_id', $ids)->cursor() as $topicFile) {
            if ($topicFile->type !== 'video') {
                $topicFile->file->delete();
            }
            $topicFile->delete();
        }

        Socket::send('topics', $this->prepareRow($record));

        return $record;
    }
}