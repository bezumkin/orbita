<?php

use App\Models\Video;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$video = Video::query()
    ->whereNull('processed')
    ->whereNull('progress')
    ->orderBy('created_at')
    ->first();
if ($video) {
    /** @var Video $video */
    $video->transcode();
}