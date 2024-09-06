<?php

use App\Models\Topic;
use App\Services\Socket;
use Carbon\Carbon;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$topics = Topic::query()
    ->where('publish_at', '<=', Carbon::now()->toDateTimeString())
    ->where('active', false)
    ->orderBy('publish_at')
    ->get();

/** @var Topic $topic */
foreach ($topics as $topic) {
    $topic->active = true;
    $topic->published_at = date('Y-m-d H:i:s');
    $topic->publish_at = null;
    $topic->save();

    $topic->notifyUsers();
}

if (count($topics)) {
    Socket::send('topics-refresh');
}