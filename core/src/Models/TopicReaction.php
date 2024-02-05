<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $topic_id
 * @property int $reaction_id
 * @property int $user_id
 * @property Carbon $timestamp
 *
 * @property-read Topic $topic
 * @property-read Reaction $reaction
 * @property-read User $user
 */
class TopicReaction extends Model
{
    use Traits\CompositeKey;

    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = ['topic_id', 'user_id'];
    protected $casts = ['timestamp' => 'datetime'];

    protected static function booted(): void
    {
        static::created(
            static function (self $record) {
                $record->topic->update(['reactions_count' => $record->topic->userReactions()->count()]);
            }
        );

        static::deleted(
            static function (self $record) {
                $record->topic->update(['reactions_count' => $record->topic->userReactions()->count()]);
            }
        );
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function reaction(): BelongsTo
    {
        return $this->belongsTo(Reaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}