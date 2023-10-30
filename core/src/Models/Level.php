<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property ?string $content
 * @property float $price
 * @property ?int $cover_id
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
}