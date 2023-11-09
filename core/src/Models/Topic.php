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
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $published_at
 *
 * @property-read User $user
 * @property-read File $cover
 * @property-read Level $level
 * @property-read TopicFile[] $topicFiles
 * @property-read TopicView[] $views
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
        return $this->belongsTo(File::class);
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

    public function saveView(?User $user): int
    {
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
                /*
                $user->notifications()
                    ->where(['topic_id' => $this->id, 'active' => true, 'sent' => false])
                    ->update(['active' => false]);
                */
            } else {
                $this->increment('views_count');
            }
        }

        return $this->views_count;
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
            if ($user->hasScope('topics/patch')) {
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
        if (!$listView && $array['access']) {
            $array['content'] = $this->content;
        }

        return $array;
    }
}