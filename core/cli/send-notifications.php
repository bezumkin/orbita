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
    ->limit($limit);

/** @var UserNotification $notification */
foreach ($notifications->cursor() as $notification) {
    if ($notification->sendEmail()) {
        $notification->active = false;
        $notification->save();

        $notification->user->notify = false;
        $notification->user->save();
    }
}