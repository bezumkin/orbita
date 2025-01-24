<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $emoji
 * @property int $rank
 * @property bool $active
 *
 * @property-read TopicReaction[] $topicReactions
 * @property-read CommentReaction[] $commentReactions
 */
class  Reaction extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'active' => 'bool',
    ];

    public function topicReactions(): HasMany
    {
        return $this->hasMany(TopicReaction::class);
    }

    public function commentReactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function prepareOutput(): array
    {
        return $this->toArray();
    }
}