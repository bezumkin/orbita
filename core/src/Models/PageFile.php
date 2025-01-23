<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vesp\Models\Traits\CompositeKey;

/**
 * @property int $page_id
 * @property int $file_id
 * @property string $type
 *
 * @property-read Page $page
 * @property-read File $file
 */
class PageFile extends Model
{
    use CompositeKey;

    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = ['page_id', 'file_id'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}