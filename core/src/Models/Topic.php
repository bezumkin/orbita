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
 * @property-read TopicView[] $topicViews
 */
class Topic extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'content' => 'array',
        'price' => 'float',
        'active' => 'bool',
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

    public function topicViews(): HasMany
    {
        return $this->hasMany(TopicView::class);
    }
}