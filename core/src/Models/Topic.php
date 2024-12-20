<?php

namespace App\Models;

use App\Models\Traits\ContentFilesTrait;
use App\Services\Utils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Ramsey\Uuid\Uuid;

/**
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property array $content
 * @property ?string $teaser
 * @property int $user_id
 * @property ?int $cover_id
 * @property ?int $level_id
 * @property ?float $price
 * @property bool $active
 * @property bool $closed
 * @property int $comments_count
 * @property int $views_count
 * @property int $reactions_count
 * @property ?int $last_comment_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $published_at
 * @property ?Carbon $publish_at
 *
 * @property-read User $user
 * @property-read File $cover
 * @property-read Level $level
 * @property-read TopicFile[] $contentFiles
 * @property-read TopicView[] $views
 * @property-read TopicTag[] $topicTags
 * @property-read TopicReaction[] $userReactions
 * @property-read Tag[] $tags
 * @property-read Reaction[] $reactions
 * @property-read Comment $lastComment
 * @property-read Comment[] $comments
 */
class Topic extends Model
{
    use ContentFilesTrait;

    protected $guarded = ['id', 'created_at', 'updated_at', 'published_at'];
    protected $casts = [
        'content' => 'array',
        'price' => 'float',
        'active' => 'bool',
        'closed' => 'bool',
        'published_at' => 'datetime',
        'publish_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $model) {
            if (!$model->uuid) {
                $model->uuid = Uuid::uuid4();
            }
        });

        static::deleting(
            static function (self $record) {
                /** @var TopicFile $topicFile */
                foreach ($record->contentFiles()->cursor() as $topicFile) {
                    $count = TopicFile::query()
                        ->where('file_id', $topicFile->file_id)->where('topic_id', '!=', $record->id)
                        ->count();
                    if (!$count) {
                        $topicFile->file->delete();
                    }
                }
            }
        );
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
        return $this->belongsTo(Level::class);
    }

    public function contentFiles(): HasMany
    {
        return $this->hasMany(TopicFile::class, 'topic_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(TopicView::class);
    }

    public function topicTags(): HasMany
    {
        return $this->hasMany(TopicTag::class);
    }

    public function userReactions(): HasMany
    {
        return $this->hasMany(TopicReaction::class);
    }

    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(Tag::class, TopicTag::class, 'topic_id', 'id', 'id', 'tag_id');
    }

    public function reactions(): HasManyThrough
    {
        return $this->hasManyThrough(Reaction::class, TopicReaction::class, 'topic_id', 'id', 'id', 'reaction_id');
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

    public function hasAccess(?User $user = null): bool
    {
        $isAdmin = $user?->hasScope('topics/patch');
        if (!$this->active && !$isAdmin) {
            return false;
        }

        $allow = false;
        if ($this->isFree()) {
            $allow = true;
        } elseif ($user) {
            if ($isAdmin || $user->hasScope('vip')) {
                $allow = true;
            } elseif ($user->payments()->where(['topic_id' => $this->id, 'paid' => true])->count()) {
                $allow = true;
            } elseif ($this->level && $user->currentSubscription) {
                $allow = $user->currentSubscription->level->price >= $this->level->price;
            }
        }

        return $allow;
    }

    public function isFree(): bool
    {
        return !$this->level_id && !$this->price;
    }

    public function prepareOutput(?User $user, bool $listView = false): array
    {
        $array = $this->only(
            'id',
            'uuid',
            'title',
            'teaser',
            'level_id',
            'price',
            'closed',
            'views_count',
            'reactions_count',
            'comments_count',
            'published_at',
            'publish_at',
            'tags',
            'active',
        );

        $array['cover'] = $this->cover?->only('id', 'uuid', 'updated_at');
        $array['access'] = $this->hasAccess($user);
        $array['paid'] = !$this->isFree();
        if ($array['access']) {
            if ($user && $this->relationLoaded('views') && count($this->views)) {
                $array['viewed_at'] = $this->views[0]->timestamp;
                $array['unseen_comments_count'] = $this->comments()
                    ->where('created_at', '>', $array['viewed_at'])
                    ->where('active', true)
                    ->where('user_id', '!=', $user->id)
                    ->count();
            }
            if (!$listView) {
                $array['content'] = $this->content;
                $array['content']['blocks'] = !empty($array['content']['blocks'])
                    ? array_values($array['content']['blocks'])
                    : [];
            }
        }

        if ($user && $this->relationLoaded('userReactions') && count($this->userReactions)) {
            $array['reaction'] = $this->userReactions[0]->reaction_id;
        }

        if (getenv('TOPICS_SHOW_AUTHOR')) {
            $array['user'] = $this->user->only('id', 'fullname');
        }

        return $array;
    }

    public function getLink(): string
    {
        return Utils::getSiteUrl() . 'topics/' . $this->uuid;
    }

    public function createPayment(User $user, string $serviceName): ?Payment
    {
        if ($this->hasAccess($user)) {
            return null;
        }

        $payment = new Payment();
        $payment->user_id = $user->id;
        $payment->topic_id = $this->id;
        $payment->service = $serviceName;
        $payment->amount = $this->price;
        $payment->metadata = [
            'uuid' => $this->uuid,
            'title' => $this->title,
        ];

        return $payment;
    }

    public function createNotifications(): void
    {
        if (getenv('TOPICS_FREE_SKIP_NOTIFICATIONS') && $this->isFree()) {
            return;
        }

        $common = User::query()
            // ->where('id', '!=', $this->user_id)
            ->where('active', true)
            ->where('notify', true)
            ->where('blocked', false)
            ->whereNotNull('email');

        // VIP users
        $vip = clone $common;
        $vip->whereHas('role', static function (Builder $c) {
            $c->whereJsonContains('scope', 'vip');
        });

        /** @var User $user */
        foreach ($vip->cursor() as $user) {
            $user->notifications()->create([
                'topic_id' => $this->id,
                'type' => 'topic-new',
            ]);
        }

        // Subscribers
        $level = $this->level;
        $users = clone $common;
        if ($level && $this->price) {
            $users->where(function (Builder $c) use ($level) {
                $c->whereHas('currentSubscription', static function (Builder $c) use ($level) {
                    $c->whereHas('level', static function (Builder $c) use ($level) {
                        $c->where('price', '>=', $level->price);
                    });
                });
                $c->orWhereHas('payments', function (Builder $c) {
                    $c->where('topic_id', $this->id);
                    $c->where('paid', true);
                });
            });
        } elseif ($level) {
            $users->whereHas('currentSubscription', static function (Builder $c) use ($level) {
                $c->whereHas('level', static function (Builder $c) use ($level) {
                    $c->where('price', '>=', $level->price);
                });
            });
        } elseif ($this->price) {
            $users->whereHas('payments', function (Builder $c) {
                $c->where('topic_id', $this->id);
                $c->where('paid', true);
            });
        }

        /** @var User $user */
        foreach ($users->cursor() as $user) {
            $user->notifications()->create([
                'topic_id' => $this->id,
                'type' => 'topic-new',
            ]);
        }
    }

    public function getSearchData(): array
    {
        $content = [];
        if ($this->content && $this->content['blocks'] && $this->hasAccess()) {
            foreach ($this->content['blocks'] as $block) {
                if ($block['type'] === 'header') {
                    $tag = 'h' . $block['data']['level'];
                    $content[] = '<' . $tag . '>' . $block['data']['text'] . '</' . $tag . '>';
                } elseif ($block['type'] === 'paragraph') {
                    $content[] = '<p>' . $block['data']['text'] . '</p>';
                }
            }
        }

        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = $tag->title;
        }

        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'teaser' => $this->teaser ?? '',
            'content' => implode(PHP_EOL, $content),
            'tags' => implode(PHP_EOL, $tags),
            'published_at' => $this->published_at->timestamp,
        ];
    }

    public static function sanitizeContent(array $content): ?array
    {
        $blocks = getenv('EDITOR_TOPIC_BLOCKS');
        $enabled = $blocks ? array_map('trim', explode(',', strtolower($blocks))) : null;

        return Utils::sanitizeContent($content, $enabled);
    }
}