<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

/**
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property array $content
 * @property ?array $teaser
 * @property int $user_id
 * @property ?int $cover_id
 * @property ?int $level_id
 * @property ?float $price
 * @property bool $active
 * @property bool $closed
 * @property int $comments_count
 * @property int $views_count
 * @property ?int $last_comment_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $published_at
 *
 * @property-read User $user
 * @property-read File $cover
 * @property-read Level $level
 * @property-read TopicFile[] $topicFiles
 * @property-read TopicView[] $views
 * @property-read Comment $lastComment
 * @property-read Comment[] $comments
 */
class Topic extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'content' => 'array',
        'price' => 'float',
        'active' => 'bool',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $model) {
            if (!$model->uuid) {
                $model->uuid = Uuid::uuid4();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function topicFiles(): HasMany
    {
        return $this->hasMany(TopicFile::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(TopicView::class);
    }

    public function lastComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getLastCommentId(): ?int
    {
        /** @var Comment $comment */
        $comment = $this->comments()
            ->where('active', true)
            ->orderByDesc('id')
            ->first();

        return $comment?->id;
    }

    public function saveView(?User $user): ?TopicView
    {
        $view = null;
        if ($this->hasAccess($user)) {
            if ($user) {
                if ($view = $this->views()->where('user_id', $user->id)->first()) {
                    $view->update(['timestamp' => time()]);
                } else {
                    $view = new TopicView();
                    $view->topic_id = $this->id;
                    $view->user_id = $user->id;
                    $view->timestamp = time();
                    $view->save();
                    $this->increment('views_count');
                }

                // Disable unsent notifications to user
                $user->notifications()
                    ->where(['topic_id' => $this->id, 'active' => true, 'sent' => false])
                    ->update(['active' => false]);
            } else {
                $this->increment('views_count');
            }
        }

        return $view;
    }

    public function hasAccess(?User $user): bool
    {
        $allow = false;
        // Free topic
        if (!$this->level_id && !$this->price) {
            $allow = true;
        }
        if ($user) {
            // Admin can see anything
            if ($user->hasScope('topics/patch') || $user->hasScope('vip')) {
                $allow = true;
            }
            // @TODO check UserPayment and UserLevel
        }

        return $allow;
    }

    public function prepareOutput(?User $user, bool $listView = false): array
    {
        $array = $this->only('id', 'uuid', 'title', 'views_count', 'comments_count', 'published_at');

        $array['access'] = $this->hasAccess($user);
        if ($listView || !$array['access']) {
            $array['teaser'] = $this->teaser;
            $array['cover'] = $this->cover?->only('id', 'uuid', 'published_at');
        }
        if ($user && $array['access']) {
            if ($this->relationLoaded('views') && count($this->views)) {
                $array['viewed_at'] = $this->views[0]->timestamp;
                $array['unseen_comments_count'] = $this->comments()
                    ->where('created_at', '>', $array['viewed_at'])
                    ->where('active', true)
                    ->where('user_id', '!=', $user->id)
                    ->count();
            }
            if (!$listView) {
                $array['content'] = $this->content;
            }
        }

        return $array;
    }

    public function processUploadedFiles(): void
    {
        $content = $this->content;
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
        $this->content = $content;
        $this->save();

        // Save topic files
        foreach ($files as $id => $type) {
            TopicFile::query()->insertOrIgnore(['topic_id' => $this->id, 'file_id' => $id, 'type' => $type]);
            /** @var File $file */
            if ($file = File::query()->where('temporary', true)->find($id)) {
                $file->temporary = false;
                $file->save();
            }
        }

        // Clean abandoned topic files
        $ids = array_keys($files);
        /** @var TopicFile $topicFile */
        foreach ($this->topicFiles()->whereNotIn('file_id', $ids)->cursor() as $topicFile) {
            if ($topicFile->type !== 'video') {
                $topicFile->file->delete();
            }
            $topicFile->delete();
        }
    }

    public function getLink(): string
    {
        return implode('/', [rtrim(getenv('SITE_URL'), '/'), 'topics', $this->uuid]);
    }
}