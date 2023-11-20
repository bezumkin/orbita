<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $topic_id
 * @property int $file_id
 * @property string $type
 *
 * @property-read Topic $topic
 * @property-read File $file
 */
class TopicFile extends Model
{
    use Traits\CompositeKey;

    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = ['topic_id', 'file_id'];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}