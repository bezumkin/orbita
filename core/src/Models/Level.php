<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $title
 * @property ?string $content
 * @property float $price
 * @property ?int $cover_id
 * @property ?string $color
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read File $cover
 */
class Level extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'price' => 'float',
        'active' => 'bool',
    ];

    public function cover(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function costPerDay(): float
    {
        $cost = round($this->price / 30, 2);
        if ($cost > 1) {
            $cost = round($cost);
        }

        return round($cost, 2);
    }

    public function costForPeriod(int $period = 1): float
    {
        return round($this->price * $period, 2);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Subscription::class, 'level_id', 'id', 'id', 'user_id');
    }

    public function activeUsers(): HasManyThrough
    {
        return $this->users()->where('subscriptions.active', true);
    }

    public function prepareOutput(): array
    {
        return $this->toArray();
    }
}