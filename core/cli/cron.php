<?php

use Go\Job;
use GO\Scheduler;

require dirname(__DIR__) . '/bootstrap.php';
$scheduler = new Scheduler();

$scheduler->php(__DIR__ . '/clear-tokens.php', null, [], 'clear_tokens')
    ->daily()
    // ->inForeground()
    ->onlyOne();

$scheduler->php(__DIR__ . '/prepare-videos.php', null, [], 'prepare_videos')
    ->everyMinute()
    ->onlyOne();

$scheduler->php(__DIR__ . '/send-notifications.php', null, [], 'send_notifications')
    ->everyMinute(10)
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
