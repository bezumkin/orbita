<?php

namespace App\Models;

use App\Models\Traits\ContentFilesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property ?string $title
 * @property bool $external
 * @property ?array $content
 * @property ?string $alias
 * @property ?string $position
 * @property ?string $link
 * @property ?string $target
 * @property bool $blank
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
        'external' => 'bool',
        'blank' => 'bool',
        'active' => 'bool',
    ];

    public function contentFiles(): HasMany
    {
        return $this->hasMany(PageFile::class, 'page_id');
    }

    public function prepareOutput(bool $listView = false): array {
        $columns = ['id', 'rank', 'position', 'name', 'external'];
        if ($this->external) {
            $columns[] = 'link';
            $columns[] = 'blank';
        } else {
            $columns[] = 'alias';
            $columns[] = 'title';
            if (!$listView) {
                $columns[] = 'content';
            }
        }
        $array = $this->only($columns);
        if (!empty($array['content']) && !empty($array['content']['blocks'])) {
            $array['content']['blocks'] = array_values($array['content']['blocks']);
        }

        return $array;
    }
}