<?php

use Go\Job;
use GO\Scheduler;

require dirname(__DIR__) . '/bootstrap.php';
$scheduler = new Scheduler();

$scheduler->php(__DIR__ . '/clear-garbage.php', null, [], 'clear_garbage')
    ->daily()
    // ->inForeground()
    ->onlyOne();

if (getenv('CACHE_S3_SIZE') > 0) {
    $scheduler->php(__DIR__ . '/clear-cache.php', null, [], 'clear_cache')
        ->everyMinute(10)
        ->onlyOne();
}

$scheduler->php(__DIR__ . '/prepare-videos.php', null, [], 'prepare_videos')
    ->everyMinute()
    ->onlyOne();

$scheduler->php(__DIR__ . '/send-notifications.php', null, [], 'send_notifications')
    ->everyMinute(10)
    ->inForeground()
    ->onlyOne();

$scheduler->php(__DIR__ . '/publish-topics.php', null, [], 'publish_topics')
    ->everyMinute(5)
    ->inForeground()
    ->onlyOne();

$scheduler->php(__DIR__ . '/readonly-users.php', null, [], 'readonly_users')
    ->everyMinute(5)
    ->inForeground()
    ->onlyOne();

$scheduler->php(__DIR__ . '/subscriptions.php', null, [], 'subscriptions')
    ->hourly()
    ->inForeground()
    ->onlyOne();

$scheduler->php(__DIR__ . '/search-index.php', null, [], 'search_index')
    ->daily()
    ->inForeground()
    ->onlyOne();

$executed = $scheduler->run();
/** @var Job $job */
foreach ($executed as $job) {
    if ($output = $job->getOutput()) {
        if (is_array($output)) {
            $output = implode("\n", $output);
        }
        echo $output;
    }
}
