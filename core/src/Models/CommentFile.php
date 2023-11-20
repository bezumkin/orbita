<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $comment_id
 * @property string $file_id
 * @property string $type
 *
 * @property-read Comment $comment
 * @property-read File $file
 */
class CommentFile extends Model
{
    use Traits\CompositeKey;

    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = ['comment_id', 'file_id'];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}