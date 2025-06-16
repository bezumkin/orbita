<?php

use App\Models\User;
use Carbon\Carbon;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$users = User::query()
    ->where('readonly', true)
    ->where('readonly_until', '<=', Carbon::now()->toDateTimeString())
    ->whereNotNull('readonly_until');

/** @var User $user */
foreach ($users->cursor() as $user) {
    $user->readonly = false;
    $user->readonly_until = null;
    $user->save();
}