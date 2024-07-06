<?php

use App\Models\UserNotification;
use Carbon\Carbon;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$timeout = getenv('NOTIFICATIONS_TIMEOUT') ?: 600;
$limit = getenv('NOTIFICATIONS_LIMIT') ?: 25;

$notifications = UserNotification::query()
    ->where('created_at', '<=', Carbon::now()->subSeconds($timeout)->toDateTimeString())
    ->where(['active' => true, 'sent' => false])
    ->with('user')
    ->with('topic', 'topic.user')
    ->with('comment', 'comment.user')
    ->limit($limit)
    ->orderBy('created_at');

/** @var UserNotification $notification */
foreach ($notifications->cursor() as $notification) {
    if (!$notification->user->notify) {
        $notification->active = false;
        $notification->save();
        continue;
    }
    if ($error = $notification->sendEmail()) {
        $notification->active = false;
        $notification->save();

        $notification->user->notify = false;
        $notification->user->save();
    }
}