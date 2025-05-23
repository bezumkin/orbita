<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Services\PaymentService;

class Internal extends PaymentService
{

    public function makePayment(Payment $payment): ?string
    {
        return null;
    }

    public function getPaymentStatus(Payment $payment): ?bool
    {
        return false;
    }
}