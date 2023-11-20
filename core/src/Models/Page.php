<?php

namespace App\Models;

use App\Models\Traits\ContentFilesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property array $content
 * @property string $alias
 * @property ?string $position
 * @property int $rank
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property PageFile[] $contentFiles
 */
class Page extends Model
{
    use ContentFilesTrait;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'content' => 'array',
        'active' => 'bool',
    ];

    public function contentFiles(): HasMany
    {
        return $this->hasMany(PageFile::class, 'page_id');
    }
}