<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Services\Log;
use App\Services\PaymentService;
use GuzzleHttp\Client;

class Payrexx extends PaymentService
{
    public const SUBSCRIPTIONS = true;
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => getenv('PAYMENT_PAYREXX_ENDPOINT')]);
    }

    public function makePayment(Payment $payment): ?string
    {
        $instance = getenv('PAYMENT_PAYREXX_INSTANCE');
        $url = $this->getSuccessUrl($payment);
        $data = [
            'amount' => (string)$payment->amount,
            'vatRate' => getenv('PAYMENT_PAYREXX_VAT') ?: '0',
            'currency' => getenv('CURRENCY') ?: 'EUR',
            'referenceId' => $payment->id,
            'fields' => [
                'id' => ['value' => $payment->user->id],
                'email' => ['value' => $payment->user->email],
            ],
            'successRedirectUrl' => $url,
            'failedRedirectUrl' => $url,
            'cancelRedirectUrl' => $url,
        ];
        if ($payment->subscription) {
            $data['purpose'] = $payment->subscription->level->title;
            $data['preAuthorization'] = '1';
            $data['chargeOnAuthorization'] = '1';
        } elseif ($payment->topic) {
            $data['purpose'] = $payment->topic->title;
        }
        $data['ApiSignature'] = $this->getSignature($data);

        try {
            $response = $this->client->post('Gateway?instance=' . $instance, [
                'headers' => ['Content-type: application/x-www-form-urlencoded'],
                'form_params' => $data,
            ]);
            $output = json_decode((string)$response->getBody(), true);
            if ($output['status'] === 'success' && !empty($output['data'])) {
                $data = array_shift($output['data']);
                if (!empty($data['link'])) {
                    $payment->remote_id = $data['id'];
                    $payment->link = $data['link'];
                    $payment->save();

                    return $payment->link;
                }
            }
        } catch (\Throwable $e) {
            Log::error($e);
        }

        return null;
    }

    public function getPaymentStatus(Payment $payment): ?bool
    {
        $instance = getenv('PAYMENT_PAYREXX_INSTANCE');
        $response = $this->client->get('Gateway/' . $payment->remote_id . '/?instance=' . $instance, [
            'headers' => ['Content-type: application/x-www-form-urlencoded'],
            'form_params' => [
                'ApiSignature' => $this->getSignature(),
            ],
        ]);
        try {
            $output = json_decode((string)$response->getBody(), true);
            if ($output['status'] === 'success' && !empty($output['data'])) {
                $data = array_shift($output['data']);
                if ($data['status'] === 'confirmed') {
                    if ($payment->subscription && !empty($data['invoices'])) {
                        foreach ($data['invoices'] as $invoice) {
                            foreach ($invoice['transactions'] as $transaction) {
                                if ($transaction['status'] === 'authorized') {
                                    $payment->subscription->remote_id = $transaction['id'];
                                    $payment->subscription->save();
                                    break;
                                }
                            }
                        }
                    }

                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::error($e);
        }

        return null;
    }

    public function chargeSubscription(Payment $payment): ?bool
    {
        if (!$payment->subscription || !$payment->subscription->remote_id) {
            return null;
        }

        $instance = getenv('PAYMENT_PAYREXX_INSTANCE');
        $data = [
            'amount' => (string)$payment->amount,
            'purpose' => $payment->subscription->level->title,
            'referenceId' => $payment->id,
        ];
        $data['ApiSignature'] = $this->getSignature($data);

        try {
            $response = $this->client->post(
                'Transaction/' . $payment->subscription->remote_id . '/?instance=' . $instance,
                [
                    'headers' => ['Content-type: application/x-www-form-urlencoded'],
                    'form_params' => $data,
                ]
            );
            $output = json_decode((string)$response->getBody(), true);
            if ($output['status'] === 'success' && !empty($output['data'])) {
                $data = array_shift($output['data']);
                $payment->remote_id = $data['id'];
                $payment->save();

                return $data['status'] === 'confirmed';
            }
        } catch (\Throwable  $e) {
            Log::error($e);
        }

        return false;
    }

    protected function getSignature(array $params = []): string
    {
        return base64_encode(
            hash_hmac('sha256', http_build_query($params, null, '&'), getenv('PAYMENT_PAYREXX_KEY'), true)
        );
    }
}