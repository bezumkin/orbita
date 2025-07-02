<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Services\Log;
use App\Services\PaymentService;
use App\Services\Utils;
use GuzzleHttp\Client;
use RuntimeException;
use Throwable;

class Tbank extends PaymentService
{
    public const bool SUBSCRIPTIONS = true;
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => getenv('PAYMENT_TBANK_ENDPOINT') ?: 'https://securepay.tinkoff.ru/v2/',
            'auth' => [
                getenv('PAYMENT_TBANK_TERMINAL'),
                getenv('PAYMENT_TBANK_PASSWORD'),
            ],
        ]);
    }

    public function makePayment(Payment $payment): ?string
    {
        $url = $this->getSuccessUrl($payment);
        $data = [
            'Amount' => round($payment->amount) * 100,
            'OrderId' => $payment->id,
            'Description' => '',
            'CustomerKey' => $payment->user_id,
            'PayType' => 'O',
            'Language' => $payment->user->lang ?: 'ru',
            'SuccessURL' => $url,
            'FailURL' => $url,
            'NotificationURL' => Utils::getApiUrl() . 'web/payment/tbank',
            'DATA' => [],
            'Receipt' => [
                'Email' => $payment->user->email,
                'Taxation' => getenv('PAYMENT_TBANK_TAXATION') ?: 'osn',
                'Items' => [],
            ],
        ];
        if ($payment->subscription) {
            $desc = ($payment->user->lang === 'en' ? 'Subscription' : 'Подписка') . ' ' . $payment->subscription->level->title;
            $data['Description'] = mb_substr($desc, 0, 139);
            $data['DATA']['subscription_id'] = $payment->subscription->id;
            if ($this->canSubscribe()) {
                $data['Recurrent'] = 'Y';
            }
        } elseif ($payment->topic) {
            $desc = ($payment->user->lang === 'en' ? 'Access to' : 'Доступ к заметке') . ' ' . $payment->topic->title;
            $data['Description'] = mb_substr($desc, 0, 139);
            $data['DATA']['topic_id'] = $payment->topic->id;
        }

        if ($data['Description']) {
            $data['Receipt']['Items'][] = [
                'Name' => mb_substr($data['Description'], 0, 127),
                'Price' => $data['Amount'],
                'Quantity' => 1,
                'Amount' => $data['Amount'],
                'PaymentObject' => 'intellectual_activity',
                'Tax' => getenv('PAYMENT_TBANK_RECEIPT_TAX') ?: 'none',
            ];
        }

        $response = $this->sendRequest('Init', $data);
        if (!$response['Success']) {
            throw new RuntimeException($response['Details']);
        }
        if (!empty($response['PaymentId'])) {
            $payment->remote_id = $response['PaymentId'];
            $payment->link = $response['PaymentURL'];
            $payment->save();

            return $payment->link;
        }

        return null;
    }

    public function getPaymentStatus(Payment $payment): ?bool
    {
        try {
            $response = $this->getPayment($payment);
            if ($response['Status'] === 'CONFIRMED') {
                if ($payment->subscription && !empty($response['RebillId'])) {
                    $payment->subscription->remote_id = $response['RebillId'];
                    $payment->subscription->save();
                }

                return true;
            }
            $cancel = ['REVERSED', 'REFUNDED', 'CANCELED', 'REJECTED', 'AUTH_FAIL', 'DEADLINE_EXPIRED'];
            if (in_array($response['Status'], $cancel, true)) {
                return false;
            }
        } catch (Throwable  $e) {
            Log::error($e);
        }

        return null;
    }

    public function getSuccessUrl(Payment $payment): string
    {
        return Utils::getSiteUrl() . 'user/payments/' . $payment->id;
    }

    public function chargeSubscription(Payment $payment): ?bool
    {
        if (!$payment->subscription || !$payment->subscription->remote_id) {
            return null;
        }

        try {
            $desc = ($payment->user->lang === 'en' ? 'Subscription' : 'Подписка') . ' ' . $payment->subscription->level->title;
            $amount = $payment->amount * 100;
            $data = [
                'Amount' => $amount,
                'OrderId' => $payment->id,
                'Description' => mb_substr($desc, 0, 139),
                'PayType' => 'O',
                'DATA' => ['subscription_id' => $payment->subscription->id],
                'Receipt' => [
                    'Email' => $payment->user->email,
                    'Taxation' => getenv('PAYMENT_TBANK_TAXATION') ?: 'osn',
                    'Items' => [
                        [
                            'Name' => mb_substr($desc, 0, 127),
                            'Price' => $amount,
                            'Quantity' => 1,
                            'Amount' => $amount,
                            'PaymentObject' => 'intellectual_activity',
                            'Tax' => getenv('PAYMENT_TBANK_RECEIPT_TAX') ?: 'none',
                        ],
                    ],
                ],
            ];

            $response = $this->sendRequest('Init', $data);
            if (!empty($response['PaymentId'])) {
                $payment->remote_id = $response['PaymentId'];
                $payment->save();

                $data = ['PaymentId' => $payment->remote_id, 'RebillId' => $payment->subscription->remote_id];
                $response = $this->sendRequest('Charge', $data);

                return $response['Status'] === 'CONFIRMED';
            }
        } catch (Throwable  $e) {
            Log::error($e);
        }

        return false;
    }

    public function cancelPayment(Payment $payment): bool
    {
        $response = $this->sendRequest('Cancel', ['PaymentId' => $payment->remote_id]);

        return in_array(@$response['Status'], ['REVERSED', 'REFUNDED', 'CANCELED'], true);
    }

    protected function sendRequest(string $method, array $data): array
    {
        $data['TerminalKey'] = getenv('PAYMENT_TBANK_TERMINAL');
        $data['Token'] = $this->getToken($data);

        try {
            $response = $this->client->post($method, ['json' => $data]);
            $output = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            Log::info('Tbank', $output);
            if (empty($output['Success'])) {
                Log::error('Tbank', $output);
            }

            return $output;
        } catch (Throwable  $e) {
            Log::error($e);
        }

        return [];
    }

    protected function getToken(array $data): string
    {
        $data['TerminalKey'] = getenv('PAYMENT_TBANK_TERMINAL');
        $data['Password'] = getenv('PAYMENT_TBANK_PASSWORD');
        ksort($data);

        $values = '';
        foreach ($data as $value) {
            if (!is_array($value)) {
                $values .= $value;
            }
        }

        return hash('sha256', $values);
    }

    public function receiveNotification(array $data): array
    {
        /** @var Payment $payment */
        $payment = Payment::query()
            ->where('service', 'Tbank')
            ->where('remote_id', $data['PaymentId'])
            ->first();
        if ($payment) {
            $status = $payment->checkStatus();
            if ($status && $payment->subscription && !empty($data['RebillId'])) {
                $payment = $payment->refresh();
                $payment->subscription->remote_id = $data['RebillId'];
                $payment->subscription->save();
            }
        }

        exit('OK');
    }

    public function getPayment(Payment $payment): ?array
    {
        return $this->sendRequest('GetState', ['PaymentId' => $payment->remote_id]);
    }
}