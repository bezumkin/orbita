<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Services\Log;
use App\Services\PaymentService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use RuntimeException;
use Throwable;

class Payrexx extends PaymentService
{
    public const SUBSCRIPTIONS = true;
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => getenv('PAYMENT_PAYREXX_ENDPOINT') ?: 'https://api.payrexx.com/v1.0/',
            'headers' => ['Content-type' => 'application/x-www-form-urlencoded'],
        ]);
    }

    public function makePayment(Payment $payment): ?string
    {
        $instance = getenv('PAYMENT_PAYREXX_INSTANCE');
        $url = $this->getSuccessUrl($payment);
        $data = [
            'amount' => (string)$payment->amount * 100,
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
            $response = $this->client->post('Gateway?instance=' . $instance, ['form_params' => $data]);
            $output = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            Log::info('Payrexx', $output);

            if ($output['status'] === 'error') {
                Log::error('Payrexx', $output);
                throw new RuntimeException($output['message']);
            }

            if ($output['status'] === 'success' && !empty($output['data'])) {
                $data = array_shift($output['data']);
                if (!empty($data['link'])) {
                    $payment->remote_id = $data['id'];
                    $payment->link = $data['link'];
                    $payment->save();

                    return $payment->link;
                }
            }
        } catch (RequestException $e) {
            Log::error($e);
        }

        return null;
    }

    public function cancelPayment(Payment $payment): bool
    {
        $instance = getenv('PAYMENT_PAYREXX_INSTANCE');
        $url = 'Gateway/' . $payment->remote_id . '/?instance=' . $instance;

        $signature = $this->getSignature();
        $response = $this->client->get($url, ['form_params' => ['ApiSignature' => $signature]]);
        $output = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        Log::info('Payrexx', $output);

        if (@$gateway = $output['data'][0]) {
            foreach ($gateway['invoices'] as $invoice) {
                foreach ($invoice['transactions'] as $transaction) {
                    if ($transaction['status'] === 'confirmed') {
                        $url = 'Transaction/' . $transaction['id'] . '/refund/?instance=' . $instance;
                        $response = $this->client->post($url, ['form_params' => ['ApiSignature' => $signature]]);
                        $output = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                        Log::info('Payrexx', $output);

                        return @$output['status'] === 'success';
                    }
                }
            }
        }

        return false;
    }

    public function getPaymentStatus(Payment $payment): ?bool
    {
        try {
            $output = $this->getPayment($payment);
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
        } catch (Throwable $e) {
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
            'amount' => (string)$payment->amount * 100,
            'purpose' => $payment->subscription->level->title,
            'referenceId' => $payment->id,
        ];
        $data['ApiSignature'] = $this->getSignature($data);

        try {
            $response = $this->client->post(
                'Transaction/' . $payment->subscription->remote_id . '/?instance=' . $instance,
                ['form_params' => $data]
            );
            $output = json_decode((string)$response->getBody(), true);
            if ($output['status'] === 'success' && !empty($output['data'])) {
                $data = array_shift($output['data']);
                $payment->remote_id = $data['id'];
                $payment->save();

                return $data['status'] === 'confirmed';
            }
        } catch (Throwable  $e) {
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

    public function getPayment(Payment $payment): ?array
    {
        $instance = getenv('PAYMENT_PAYREXX_INSTANCE');
        $response = $this->client->get('Gateway/' . $payment->remote_id . '/?instance=' . $instance, [
            'form_params' => [
                'ApiSignature' => $this->getSignature(),
            ],
        ]);
        $output = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        Log::info('Payrexx', $output);

        return $output;
    }
}