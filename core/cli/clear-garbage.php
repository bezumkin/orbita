<?php

use App\Models\File;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserToken;
use App\Services\TempStorage;
use Carbon\Carbon;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$now = Carbon::now()->toImmutable();

$items = UserToken::query()
    ->where('valid_till', '<', $now)
    ->orWhere('active', false);
if ($count = $items->count()) {
    $items->delete();
    echo "Deleted user tokens: $count\n";
}

if ($days = getenv('CLEAR_USER_NOTIFICATIONS')) {
    $items = UserNotification::query()
        ->where('created_at', '<', $now->subDays($days));
    if ($count = $items->count()) {
        $items->delete();
        echo "Deleted user notifications: $count\n";
    }
}

if ($days = getenv('CLEAR_INACTIVE_USERS')) {
    $items = User::query()
        ->where('active', false)
        ->whereNull('active_at')
        ->where('created_at', '<', $now->subDays($days));
    if ($count = $items->delete()) {
        $items->delete();
        echo "Deleted inactive users: $count\n";
    }
}

if ($days = getenv('CLEAR_TEMPORARY_FILES')) {
    $items = File::query()
        ->where('temporary', true)
        ->where('created_at', '<', $now->subDays($days));
    if ($count = $items->count()) {
        /** @var File $file */
        foreach ($items->cursor() as $file) {
            $file->delete();
        }
    }

    $count += (new TempStorage())->clearTemporaryFiles($days);
    if ($count) {
        echo "Deleted temporary files: $count\n";
    }
}

if ($days = getenv('CLEAR_FAILED_PAYMENTS')) {
    $items = Payment::query()
        ->where('paid', false)
        ->where('created_at', '<', $now->subDays($days));
    if ($count = $items->count()) {
        $items->delete();
        echo "Deleted failed payments: $count\n";
    }
}

if ($days = getenv('CLEAR_UNPAID_SUBSCRIPTIONS')) {
    $items = Subscription::query()
        ->where('active', false)
        ->whereNull('active_until')
        ->where('created_at', '<', $now->subDays($days));
    if ($count = $items->count()) {
        $items->delete();
        echo "Deleted unpaid subscriptions: $count\n";
    }
}