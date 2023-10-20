<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property ?string $value
 * @property string $type
 * @property bool $required
 * @property int $rank
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Setting extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    protected $fillable = ['value', 'type', 'required', 'rank'];
    protected $casts = ['required' => 'bool'];

    public const JSON_TYPES = ['string', 'text', 'image'];
}