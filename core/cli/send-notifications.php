<?php

use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$timeout = getenv('NOTIFICATIONS_TIMEOUT') ?: 600;
$limit = getenv('NOTIFICATIONS_LIMIT') ?: 25;

$notifications = UserNotification::query()
    ->where(['active' => true, 'sent' => false])
    ->where(static function (Builder $c) use ($timeout) {
        $c->where('created_at', '<=', Carbon::now()->subSeconds($timeout)->toDateTimeString());
        $c->orWhere('type', 'topic-new');
    })
    ->with('user')
    ->with('topic', 'topic.user')
    ->with('comment', 'comment.user')
    ->limit($limit);

/** @var UserNotification $notification */
foreach ($notifications->cursor() as $notification) {
    $notification->sendEmail();
}