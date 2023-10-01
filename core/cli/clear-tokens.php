<?php

use App\Models\UserToken;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

UserToken::query()
    ->where('valid_till', '<', date('Y-m-d H:i:s'))
    ->orWhere('active', false)
    ->delete();
