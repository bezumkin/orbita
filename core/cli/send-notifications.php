<?php

use App\Models\UserNotification;
use Carbon\Carbon;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$timeout = getenv('COMMENTS_EDIT_TIME') ?: 600;
$created_at = Carbon::now()->subSeconds($timeout)->toDateTimeString();
$notifications = UserNotification::query()
    ->where('created_at', '<=', $created_at)
    ->where([
        'active' => true,
        'sent' => false,
    ]);

/** @var UserNotification $notification */
foreach ($notifications->cursor() as $notification) {
    $notification->sendEmail();
}