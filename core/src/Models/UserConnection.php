<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vesp\Models\Traits\CompositeKey;

/**
 * @property int $user_id
 * @property string $service
 * @property string $remote_id
 * @property ?array $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $user
 */
class UserConnection extends Model
{
    use CompositeKey;

    protected $primaryKey = ['user_id', 'service'];
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}