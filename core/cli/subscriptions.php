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

// Try to prolong active subscriptions
$subscriptions = Subscription::query()->where('active', true)->where('active_until', '<', $now);
foreach ($subscriptions->cursor() as $subscription) {
    /** @var Subscription $subscription */
    if ($subscription->charge()) {
        $subscription->activate();
    } else {
        $subscription->disable();
    }
}
