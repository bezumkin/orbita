<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $video_id
 * @property int $user_id
 * @property int $quality
 * @property int $time
 * @property float $speed
 * @property float $volume
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Video $video
 * @property-read User $user
 */
class VideoUser extends Model
{
    use Traits\CompositeKey;

    protected $primaryKey = ['user_id', 'video_id'];
    protected $fillable = ['user_id', 'video_id', 'quality', 'time', 'speed', 'volume'];
    protected $casts = [
        'quality' => 'int',
        'time' => 'int',
        'speed' => 'float',
        'volume' => 'float',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quality(): BelongsTo
    {
        return $this->belongsTo(VideoQuality::class, 'quality');
    }
}