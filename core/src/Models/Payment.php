<?php

namespace App\Models;

use App\Services\PaymentService;
use App\Services\Socket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property int $user_id
 * @property ?int $subscription_id
 * @property ?int $topic_id
 * @property string $service
 * @property float $amount
 * @property ?bool $paid
 * @property ?string $link
 * @property ?string $remote_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $paid_at
 * @property array $metadata
 *
 * @property-read User $user
 * @property-read Subscription $subscription
 * @property-read Topic $topic
 */
class Payment extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'amount' => 'float',
        'paid' => 'boolean',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];
    protected $guarded = ['paid', 'paid_at'];

    protected static function booted(): void
    {
        static::creating(static function (self $model) {
            if (!$model->id) {
                $model->id = Uuid::uuid4();
            }
        });

        static::saving(static function (self $model) {
            if (!$model->paid && $model->paid_at) {
                $model->paid_at = null;
            } elseif ($model->paid && !$model->paid_at) {
                $model->paid_at = Carbon::now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    protected function getService(): PaymentService
    {
        $service = '\App\Services\Payments\\' . $this->service;

        return new $service();
    }

    public function checkStatus(): ?bool
    {
        // Try to avoid race condition on status checking
        $payment = $this->refresh();

        // Check the payment
        $ttl = (int)getenv('PAYMENT_TIMEOUT_HOURS') ?: 6;
        if ($payment->paid === null) {
            $service = $payment->getService();
            $status = $service->getPaymentStatus($payment);
            if ($status === true) {
                $payment->paid = true;
                $payment->paid_at = time();
                $payment->save();

                if ($payment->subscription) {
                    $payment->subscription->service = $payment->service;
                    $payment->subscription->activate($payment);
                }
                Socket::send('profile', ['id' => $payment->user_id]);
                Socket::send('payment', [], 'payments');
            } elseif ($status === false || $payment->created_at->addHours($ttl) < Carbon::now()) {
                $payment->paid = false;
                $payment->save();

                if ($payment->subscription) {
                    $payment->subscription->next_level_id = null;
                    $payment->subscription->next_period = null;
                    $payment->subscription->save();
                }
                Socket::send('profile', ['id' => $payment->user_id]);
            }
        }

        return $payment->paid;
    }

    public function getLink(): ?array
    {
        if ($link = $this->getService()->makePayment($this)) {
            return str_starts_with($link, 'data:image/')
                ? ['qr' => $link]
                : ['redirect' => $link];
        }

        return null;
    }

    public function refund(): bool
    {
        $service = $this->getService();
        if (!$this->paid) {
            return false;
        }

        if ($response = $service->cancelPayment($this)) {
            if ($this->subscription?->active) {
                $this->subscription->disable();
            }
            $this->paid = false;
            $this->save();
        }

        return $response;
    }
}