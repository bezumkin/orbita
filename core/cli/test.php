<?php

use App\Models\Video;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$video = Video::query()
    ->whereNull('processed')
    ->where('id', '98365cde-9a58-4b7c-90e4-af63905cd0c0')
    ->orderBy('created_at')
    ->first();
if ($video) {
    /** @var Video $video */
    $video->transcode();
}