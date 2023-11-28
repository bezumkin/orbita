<?php

namespace App\Interfaces;

interface Payment
{
    public function makePayment(\App\Models\Payment $payment): ?string;

    public function getPaymentStatus(\App\Models\Payment $payment): ?bool;

    public function getSuccessUrl(\App\Models\Payment $payment): string;
}