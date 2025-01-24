<?php

namespace App\Models;

use App\Models\Traits\RankedModel;
use App\Services\Utils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property ?string $description
 * @property string $uri
 * @property bool $active
 * @property int $rank
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Category $parent
 * @property-read Topic[] $topics
 */
class Category extends Model
{
    use RankedModel;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function prepareOutput(): array
    {
        return $this->only('id', 'title', 'description', 'uri', 'rank', 'active');
    }

    public function getLink(): string
    {
        return Utils::getSiteUrl() . $this->uri;
    }
}