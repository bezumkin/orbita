<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vesp\Models\Traits\CompositeKey;

/**
 * @property int $topic_id
 * @property int $tag_id
 *
 * @property-read Topic $topic
 * @property-read Tag $tag
 */
class TopicTag extends Model
{
    use CompositeKey;

    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = ['topic_id', 'tag_id'];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}