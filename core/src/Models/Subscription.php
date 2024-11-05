<?php

namespace App\Models;

use App\Services\PaymentService;
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
 * @property Carbon $warned_at
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
        'warned_at' => 'datetime',
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

    protected function getService(): PaymentService
    {
        $service = '\App\Services\Payments\\' . $this->service;

        return new $service();
    }

    public function createPayment(int $period = 1, string $service = null): Payment
    {
        $now = ($this->active_until ?: Carbon::now())->toImmutable();

        $payment = new Payment();
        $payment->user_id = $this->user->id;
        $payment->subscription_id = $this->id;
        $payment->service = $service ?? $this->service;
        $payment->amount = $this->amountForPeriod($period);
        $payment->metadata = [
            'level' => $this->nextLevel->id ?? $this->level->id,
            'title' => $this->nextLevel->title ?? $this->level->title,
            'period' => $period,
            'until' => (string)$now->addMonths($period),
        ];

        return $payment;
    }

    public function amountForPeriod($period): float
    {
        return $this->level->price * $period;
    }

    public function activate(): void
    {
        $now = Carbon::now()->toImmutable();

        $period = $this->next_period ?? $this->period;
        if (!$this->active_until || $this->active_until < $now || $this->next_level_id) {
            $this->active_until = $now->addMonths($period);
            if ($this->next_level_id) {
                $this->level_id = $this->next_level_id;
            }
        } else {
            $this->active_until = $this->active_until->addMonths($period);
        }
        $this->active = true;
        $this->cancelled = false;
        $this->next_level_id = null;
        $this->next_period = null;
        $this->save();

        $this->sendEmail('paid');
    }

    public function disable(): void
    {
        $this->active = false;
        $this->save();

        $this->sendEmail('cancelled');
    }

    public function paidAmountLeft(): float
    {
        $days = Carbon::now()->diffInDays($this->active_until);
        if ($days < 1) {
            return 0;
        }

        return $this->level->costPerDay() * $days;
    }

    public function charge(): ?bool
    {
        $service = $this->getService();
        if ($this->remote_id && $service::SUBSCRIPTIONS) {
            $payment = $this->createPayment($this->next_period ?? $this->period);
            $payment->save();

            if ($service->chargeSubscription($payment)) {
                $payment->paid = true;
                $payment->paid_at = time();
                $payment->save();

                return true;
            }
        }

        return false;
    }

    public function warn(): bool
    {
        if (!$this->sendEmail('warn')) {
            $this->timestamps = false;
            $this->warned_at = date('Y-m-d H:i:s');
            $this->save();

            return true;
        }

        return false;
    }

    protected function sendEmail(string $type): ?string
    {
        $lang = $this->user->lang ?? 'en';
        $service = $this->getService();
        $data = [
            'lang' => $lang,
            'user' => $this->user->toArray(),
            'level' => $this->level->toArray(),
            'subscription' => $this->toArray(),
            'renew' => $service->canSubscribe(),
        ];
        $subject = getenv('EMAIL_SUBSCRIPTION_' . strtoupper($type) . '_' . strtoupper($lang));
        if (empty($subject)) {
            $subject = 'No Subject';
        }

        return $this->user->sendEmail($subject, 'subscription-' . $type, $data);
    }
}