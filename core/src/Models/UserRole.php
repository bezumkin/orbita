<?php

namespace App\Models;

/**
 * @property ?string $color
 */
class UserRole extends \Vesp\Models\UserRole
{
    protected $fillable = ['title', 'scope', 'color'];
}
