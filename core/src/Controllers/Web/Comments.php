<?php

namespace App\Controllers\Web;

use App\Models\Comment;
use App\Models\Topic;
use App\Services\Socket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Comments extends ModelController
{
    protected string|array $scope = 'comments';
    protected string $model = Comment::class;
    private ?Topic $topic = null;
    private bool $isNew = false;
    private bool $isAdmin = false;
    private bool $hasAccess = true;

    public function checkScope(string $method): ?ResponseInterface
    {
        if ($method === 'options') {
            return null;
        }

        $this->isAdmin = $this->user && $this->user->hasScope('comments/delete');
        $uuid = $this->getProperty('topic_uuid');

        if (!$uuid || !$this->topic = $this->getTopic($uuid)) {
            return $this->failure('Not Found', 404);
        }
        $this->hasAccess = $this->topic->hasAccess($this->user);

        return null;
    }

    protected function getTopic(string $uuid): ?Topic
    {
        $c = Topic::query()->where('uuid', $uuid);
        if (!$this->isAdmin) {
            $c->where('active', true);
        }
        /** @var ?Topic $topic */
        $topic = $c->first();

        return $topic;
    }

    protected function beforeCount(Builder $c): Builder
    {
        $c->where('topic_id', $this->topic->id);

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->with('user', function (BelongsTo $c) {
            $c->select('id', 'username', 'fullname', 'role_id', 'avatar_id');
            $c->with('avatar:id,uuid,updated_at');
        });
        if ($this->user) {
            $c->with('userReactions', function (HasMany $c) {
                $c->where('user_id', $this->user->id);
            });
        }

        return $c;
    }

    protected function addSorting(Builder $c): Builder
    {
        return $c->orderBy('id');
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        if (!$this->user || !$this->hasAccess || !$this->user->hasScope('comments/put')) {
            return $this->failure('errors.no_scope');
        }

        /** @var Comment $record */
        $content = $record->content;
        $content['blocks'] = !empty($content['blocks']) ? array_values($content['blocks']) : [];
        $record->content = $content;

        if (!$this->isAdmin && $record->isDirty('active')) {
            return $this->failure('errors.no_scope');
        }
        if (!$record->content) {
            return $this->failure('errors.comment.no_content');
        }

        if (!$record->exists) {
            $record->active = true;
            $record->parent_id = null;
            $record->topic_id = $this->topic->id;

            if ($parent_id = (int)$this->getProperty('parent_id')) {
                /** @var Comment $parent */
                if (!$parent = Comment::query()->where('active', true)->find($parent_id)) {
                    return $this->failure('errors.comment.wrong_parent');
                }
                if ($parent->topic_id !== $record->topic_id) {
                    return $this->failure('errors.comment.wrong_parent');
                }
                $record->parent_id = $parent->id;
            }
            $this->isNew = true;
        } elseif (!$this->isAdmin) {
            if ($record->children()->count()) {
                return $this->failure('errors.comment.children_exists');
            }
            if ($record->created_at->toImmutable()->addSeconds(getenv('COMMENTS_EDIT_TIME'))->timestamp < time()) {
                return $this->failure('errors.comment.edit_time');
            }
        }

        if (!$record->user_id) {
            $record->user_id = $this->user->id;
        }

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        /** @var Comment $record */
        $record->processUploadedFiles();

        $record->refresh();
        $record->load('user:id,username,fullname,role_id,avatar_id', 'user.avatar:id,uuid,updated_at');

        if ($this->isNew) {
            Socket::send('comment-create', $this->prepareRow($record));
            $this->createNotifications($record);
        } else {
            Socket::send('comment-update', $this->prepareRow($record));
        }

        Socket::send('topic-comments', [
            'id' => $record->topic->id,
            'comments_count' => $record->topic->comments_count,
        ]);

        return $record;
    }

    public function prepareRow(Model $object): array
    {
        /** @var Comment $object */
        return $object->prepareOutput($this->user);
    }

    protected function beforeDelete(Model $record): ?ResponseInterface
    {
        if (!$this->isAdmin) {
            return $this->failure('errors.no_scope');
        }

        return null;
    }

    public function delete(): ResponseInterface
    {
        $id = $this->getPrimaryKey();
        /** @var Comment $record */
        if (!$id || !$record = Comment::query()->find($id)) {
            return $this->failure('Could not find a record', 404);
        }

        $record->delete();
        Socket::send('comment-delete', $record->toArray());
        Socket::send('topic-comments', [
            'id' => $record->topic->id,
            'comments_count' => $record->topic->comments_count,
        ]);

        return $this->success();
    }

    protected function createNotifications(Comment $comment): void
    {
        $topic = $comment->topic;
        $parent = $comment->parent;

        if (getenv('COMMENTS_NOTIFY_REPLY')) {
            // Notify comment author
            if ($parent && $parent->user_id !== $comment->user_id && $parent->user->notify) {
                $parent->user->notifications()
                    ->create(['type' => 'comment-reply', 'topic_id' => $topic->id, 'comment_id' => $comment->id]);
            }
        }

        if (getenv('COMMENTS_NOTIFY_AUTHOR')) {
            // Notify topic author
            if ($topic->user_id !== $comment->user_id && $topic->user->notify && (!$parent || $parent->user_id !== $topic->user_id)) {
                $topic->user->notifications()
                    ->create(['type' => 'comment-new', 'topic_id' => $topic->id, 'comment_id' => $comment->id]);
            }
        }
    }
}