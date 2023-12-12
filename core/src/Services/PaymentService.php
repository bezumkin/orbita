<?php

namespace App\Services;

use App\Models\Payment;
use ReflectionClass;

abstract class PaymentService
{

    public const SUBSCRIPTIONS = false;

    abstract public function makePayment(Payment $payment): ?string;

    abstract public function getPaymentStatus(Payment $payment): ?bool;

    public function getSuccessUrl(Payment $payment): string
    {
        return rtrim(getenv('SITE_URL'), '/') . '/user/payments/' . $payment->id;
    }

    public function canSubscribe(): bool
    {
        if (!$this::SUBSCRIPTIONS) {
            return false;
        }
        $name = (new ReflectionClass($this))->getShortName();

        return in_array($name, array_map('trim', explode(',', getenv('PAYMENT_SUBSCRIPTIONS'))), true);
    }

    public function chargeSubscription(Payment $payment): ?bool
    {
        return false;
    }
}