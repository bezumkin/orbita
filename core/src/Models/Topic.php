<?php

namespace App\Models;

use App\Models\Traits\ContentFilesTrait;
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
 * @property-read TopicFile[] $contentFiles
 * @property-read TopicView[] $views
 * @property-read TopicTag[] $topicTags
 * @property-read Tag[] $tags
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

    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(Tag::class, TopicTag::class, 'topic_id', 'id', 'id', 'tag_id');
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
            } elseif ($user->payments()->where(['topic_id' => $this->id, 'paid' => true])->count()) {
                $allow = true;
            } elseif ($this->level && $user->currentSubscription) {
                $allow = $user->currentSubscription->level->price >= $this->level->price;
            }
        }

        return $allow;
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
            'comments_count',
            'published_at',
            'tags'
        );

        $array['cover'] = $this->cover?->only('id', 'uuid', 'updated_at');
        $array['access'] = $this->hasAccess($user);
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
            }
        }

        return $array;
    }

    public function getLink(): string
    {
        return implode('/', [rtrim(getenv('SITE_URL'), '/'), 'topics', $this->uuid]);
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

    public function notifyUsers(): void
    {
        $level = $this->level;
        $users = User::query()
            // ->where('id', '!=', $this->user_id)
            ->where('active', true)
            ->where('blocked', false)
            ->whereNotNull('email');

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
}