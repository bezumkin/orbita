<?php

namespace App\Controllers\User;

use App\Interfaces\Payment as PaymentInterface;
use App\Models\Level;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vesp\Controllers\ModelGetController;

class Payments extends ModelGetController
{
    protected string $model = Payment::class;
    protected string|array $scope = 'profile';

    protected function beforeGet(Builder $c): Builder
    {
        $c->with('topic:id,uuid,title');

        return $c->where('user_id', $this->user->id);
    }

    protected function beforeCount(Builder $c): Builder
    {
        $c->with('topic:id,uuid,title');

        return $c->where('user_id', $this->user->id);
    }

    public function prepareRow(Model $object): array
    {
        /** @var Payment $object */
        $object->checkStatus();

        $array = $object->only('id', 'service', 'amount', 'paid', 'paid_at', 'created_at', 'metadata');
        if ($object->topic) {
            $array['topic'] = $object->topic->toArray();
        }

        return $array;
    }

    public function post(): ResponseInterface
    {
        try {
            $service = $this->getService($this->getProperty('service', ''));
            $serviceName = (new \ReflectionClass($service))->getShortName();

            if ($levelId = $this->getProperty('level')) {
                /** @var Level $level */
                if ($level = Level::query()->where('active', true)->find($levelId)) {
                    return $this->success($this->buyLevel($level, $serviceName));
                }

                return $this->failure('Not Found', 404);
            }

            if ($topicUuid = $this->getProperty('topic')) {
                /** @var Topic $topic */
                if ($topic = Topic::query()->where(['uuid' => $topicUuid, 'active' => true])->first()) {
                    return $this->success($this->buyTopic($topic, $serviceName));
                }

                return $this->failure('Not Found', 404);
            }
        } catch (\Throwable $e) {
            return $this->failure($e->getMessage());
        }

        return $this->failure('Bad Request', 400);
    }

    protected function buyLevel(Level $level, string $serviceName): array
    {
        $response = [];
        /** @var User $user */
        $user = $this->user;

        $period = (int)$this->getProperty('period') ?: 1;
        $cost = $level->costForPeriod($period);

        if (!$subscription = $user->currentSubscription) {
            $subscription = new Subscription();
            $subscription->user_id = $user->id;
            $subscription->level_id = $level->id;
            $subscription->period = $period;
            $subscription->service = $serviceName;
        } else {
            $subscription->next_level_id = $level->id;
            $subscription->next_period = $period;
            $paid = $subscription->paidAmountLeft();
            if ($paid && $paid < $cost) {
                $cost -= $paid;
            }
        }
        $subscription->save();

        if ($payment = $subscription->createPayment($period, $serviceName)) {
            $payment->amount = $cost;
            $payment->save();
            $response['id'] = $payment->id;
            if ($link = $payment->getLink()) {
                $response['payment'] = $link;
            }
        }

        return $response;
    }

    protected function buyTopic(Topic $topic, string $serviceName): array
    {
        $response = [];
        if ($payment = $topic->createPayment($this->user, $serviceName)) {
            $payment->save();
            $response['id'] = $payment->id;
            if ($link = $payment->getLink()) {
                $response['payment'] = $link;
            }
        }

        return $response;
    }

    protected function getService(string $name): PaymentInterface
    {
        $allowedServices = array_map('trim', explode(',', strtolower(getenv('PAYMENT_SERVICES') ?: '')));
        $serviceName = implode('', array_map('ucfirst', explode('-', $name)));
        if (!in_array(strtolower($serviceName), $allowedServices, true)) {
            throw new RuntimeException('errors.payment.no_service');
        }

        $serviceClass = '\App\Services\Payments\\' . $serviceName;
        if (!class_exists($serviceClass)) {
            throw new RuntimeException('errors.payment.no_service');
        }

        $service = new $serviceClass();
        if (!($service instanceof PaymentInterface)) {
            throw new RuntimeException('errors.payment.wrong_service');
        }

        return $service;
    }
}