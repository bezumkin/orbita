<?php

use App\Models\Subscription;
use Carbon\Carbon;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';
new Eloquent();

$now = Carbon::now()->toImmutable();

// Clean old unpaid subscriptions
/*
Subscription::query()
    ->where('active', false)
    ->whereNull('active_until')
    ->where('created_at', '<', $now->subDay())
    ->delete();
*/

// Warning about future prolongation
$subscriptions = Subscription::query()
    ->where('active', true)
    ->where('cancelled', false)
    ->whereNotNull('remote_id')
    ->where(static function ($c) use ($now) {
        $c->whereNull('warned_at');
        $c->orWhereDate('warned_at', '!=', $now->toDateString());
    })
    ->whereDate('active_until', $now->addDays(getenv('SUBSCRIPTION_WARN_BEFORE_DAYS') ?: 3)->toDateString());
foreach ($subscriptions->cursor() as $subscription) {
    /** @var Subscription $subscription */
    $subscription->warn();
}

// Try to prolong active subscriptions
$subscriptions = Subscription::query()
    ->where('active', true)
    ->where('cancelled', false)
    ->whereNotNull('remote_id')
    ->where('active_until', '<', $now);
foreach ($subscriptions->cursor() as $subscription) {
    /** @var Subscription $subscription */
    if ($subscription->charge()) {
        $subscription->activate();
    } else {
        $subscription->disable();
    }
}
