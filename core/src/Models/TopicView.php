<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $topic_id
 * @property int $user_id
 * @property Carbon $timestamp
 *
 * @property-read Topic $topic
 * @property-read User $user
 */
class TopicView extends Model
{
    use Traits\CompositeKey;

    public $timestamps = false;
    protected $primaryKey = ['topic_id', 'user_id'];
    protected $fillable = ['timestamp'];
    protected $casts = ['timestamp' => 'datetime'];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}