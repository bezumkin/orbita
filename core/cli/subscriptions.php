<?php

use App\Models\Subscription;
use Carbon\Carbon;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$now = Carbon::now()->toImmutable();

// Clean old unpaid subscriptions
Subscription::query()
    ->where('active', false)
    ->whereNull('active_until')
    ->where('created_at', '<', $now->addDay())
    ->delete();

// @TODO pay for current subscriptions
$subscriptions = Subscription::query()->where('active', true)->where('active_until', '<', $now);
/** @var Subscription $subscription */
foreach ($subscriptions->cursor() as $subscription) {
    if (!$subscription->charge()) {
        $subscription->active = false;
        $subscription->save();
    }
}
