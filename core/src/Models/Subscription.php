<?php

namespace App\Models;

use App\Interfaces\Payment as PaymentInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $level_id
 * @property ?int $next_level_id
 * @property string $service
 * @property int $period
 * @property ?int $next_period
 * @property bool $active
 * @property bool $cancelled
 * @property ?string $remote_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $active_until
 *
 * @property-read User $user
 * @property-read Level $level
 * @property-read Level $nextLevel
 */
class Subscription extends Model
{
    protected $casts = [
        'active' => 'bool',
        'cancelled' => 'bool',
        'active_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function nextLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'next_level_id');
    }

    protected function getService(): PaymentInterface
    {
        $service = '\App\Services\Payments\\' . $this->service;

        return new $service();
    }

    public function createPayment(int $period = 1, string $service = null): Payment
    {
        $payment = new Payment();
        $payment->user_id = $this->user->id;
        $payment->subscription_id = $this->id;
        $payment->service = $service ?? $this->service;
        $payment->amount = $this->amountForPeriod($period);
        $payment->metadata = [
            'level' => $this->level->id,
            'title' => $this->level->title,
            'period' => $period,
            'until' => (string)Carbon::now()->addMonths($period),
        ];

        return $payment;
    }

    public function amountForPeriod($period): float
    {
        return $this->level->price * $period;
    }

    public function activate(): void
    {
        $period = $this->next_period ?? $this->period;
        $now = Carbon::now();
        if (!$this->active_until || $this->active_until < $now) {
            $this->active_until = $now->addMonths($period);
        } else {
            $this->active_until->addMonths($period);
        }
        if ($this->next_level_id) {
            $this->level_id = $this->next_level_id;
        }
        $this->active = true;
        $this->cancelled = false;
        $this->next_level_id = null;
        $this->next_period = null;
        $this->save();
    }

    public function paidAmountLeft(): float
    {
        $days = Carbon::now()->diffInDays($this->active_until);
        if ($days < 1) {
            return 0;
        }

        return $this->level->costPerDay() * $days;
    }


    // Make a payment for the perios
    public function charge(): bool
    {
        return true;
    }
}