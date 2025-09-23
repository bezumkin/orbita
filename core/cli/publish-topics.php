<?php

use App\Models\Topic;
use App\Services\Socket;
use Carbon\Carbon;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$now = Carbon::now()->toImmutable();
// $refresh = false;

$topics = Topic::query()
    ->where('publish_at', '<=', $now->toDateTimeString())
    ->where('active', false)
    ->orderBy('publish_at');

/** @var Topic $topic */
foreach ($topics->cursor() as $topic) {
    $topic->active = true;
    $topic->published_at = $topic->publish_at;
    $topic->publish_at = null;
    $topic->save();

    if ($topic->published_at->diffInDays($now) < 1) {
        $topic->createNotifications();
        // $refresh = true;
    }
}

// Disabled for now, need to update frontend logic
/* if ($refresh) {
    Socket::send('topics-refresh');
} */