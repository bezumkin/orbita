<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Services\Log;
use App\Services\PaymentService;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Throwable;

class Yookassa extends PaymentService
{
    public const SUBSCRIPTIONS = true;
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => getenv('PAYMENT_YOOKASSA_ENDPOINT') ?: 'https://api.yookassa.ru/v3/',
            'auth' => [
                getenv('PAYMENT_YOOKASSA_USER'),
                getenv('PAYMENT_YOOKASSA_PASSWORD'),
            ],
        ]);
    }

    public function makePayment(Payment $payment): ?string
    {
        $url = $this->getSuccessUrl($payment);
        $data = [
            'amount' => [
                'value' => $payment->amount,
                'currency' => getenv('CURRENCY') ?: 'RUB',
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => $url,
            ],
            'description' => '',
            'capture' => true,
            'metadata' => [
                'payment_id' => $payment->id,
            ],
        ];
        if ($payment->subscription) {
            $data['description'] = $payment->subscription->level->title;
            $data['save_payment_method'] = $this->canSubscribe();
            $data['metadata']['subscription_id'] = $payment->subscription->id;
        } elseif ($payment->topic) {
            $data['description'] = $payment->topic->title;
            $data['metadata']['topic_id'] = $payment->topic->id;
        }

        $response = $this->client->post('payments', [
            'headers' => ['Idempotence-Key' => (string)Uuid::uuid5(Uuid::NAMESPACE_URL, $url)],
            'json' => $data,
        ]);
        $output = json_decode((string)$response->getBody(), true);
        if (!empty($output['id'])) {
            $payment->remote_id = $output['id'];
            $payment->link = $output['confirmation']['confirmation_url'];
            $payment->save();

            return $payment->link;
        }

        return null;
    }

    public function getPaymentStatus(Payment $payment): ?bool
    {
        try {
            $url = $this->getSuccessUrl($payment);
            $response = $this->client->get('payments/' . $payment->remote_id, [
                'headers' => ['Idempotence-Key' => (string)Uuid::uuid5(Uuid::NAMESPACE_URL, $url)],
            ]);
            $output = json_decode((string)$response->getBody(), true);

            if ($output['status'] === 'succeeded') {
                if ($payment->subscription && !empty($output['payment_method']['saved'])) {
                    $payment->subscription->remote_id = $output['payment_method']['id'];
                    $payment->subscription->save();
                }

                return true;
            }
            if ($output['status'] === 'canceled') {
                return false;
            }
        } catch (Throwable  $e) {
            Log::error($e);
        }

        return null;
    }

    public function getSuccessUrl(Payment $payment): string
    {
        return rtrim(getenv('SITE_URL'), '/') . '/user/payments/' . $payment->id;
    }

    public function chargeSubscription(Payment $payment): ?bool
    {
        if (!$payment->subscription || !$payment->subscription->remote_id) {
            return null;
        }

        try {
            $data = [
                'amount' => [
                    'value' => $payment->amount,
                    'currency' => getenv('CURRENCY') ?: 'RUB',
                ],
                'description' => $payment->subscription->level->title,
                'capture' => true,
                'payment_method_id' => $payment->subscription->remote_id,
            ];

            $url = $this->getSuccessUrl($payment);
            $response = $this->client->post('payments', [
                'headers' => ['Idempotence-Key' => (string)Uuid::uuid5(Uuid::NAMESPACE_URL, $url)],
                'json' => $data,
            ]);
            $output = json_decode((string)$response->getBody(), true);
            if (!empty($output['id'])) {
                $payment->remote_id = $output['id'];
                $payment->save();

                return $output['status'] === 'succeeded';
            }
        } catch (Throwable  $e) {
            Log::error($e);
        }

        return false;
    }
}