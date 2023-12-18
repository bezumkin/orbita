<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $title
 *
 * @property-read TopicTag[] $topicTags
 * @property-read Topic[] $topics
 */
class Tag extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function topicTags(): HasMany
    {
        return $this->hasMany(TopicTag::class);
    }

    public function topics(): HasManyThrough
    {
        return $this->hasManyThrough(Topic::class, TopicTag::class, 'tag_id', 'id', 'id', 'topic_id');
    }
}