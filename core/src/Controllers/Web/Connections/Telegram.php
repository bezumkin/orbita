<?php

namespace App\Controllers\Web\Connections;

use App\Models\User;
use App\Models\UserConnection;
use App\Services\Connections\Telegram as Service;
use Illuminate\Database\Capsule\Manager;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Telegram extends Controller
{
    protected Service $service;

    public function __construct(Manager $eloquent, Service $service)
    {
        parent::__construct($eloquent);
        $this->service = $service;
    }

    public function post(): ResponseInterface
    {
        $localId = (int)$this->getProperty('local_id');
        $remoteId = (string)$this->getProperty('remote_id');
        if ($localId && $remoteId && $user = User::query()->find($localId)) {
            /** @var User $user */
            $signature = $this->getProperty('signature', '');
            $data = $this->getProperty('data');

            if ($this->service->checkSignature($user->id, $remoteId, $signature)) {
                if ($this->service->connectUser($user, $remoteId, $data)) {
                    return $this->success($this->prepareOutput($user));
                }
            }
        }

        return $this->failure();
    }

    public function get(): ResponseInterface
    {
        $localId = 0;
        $remoteId = (string)$this->getProperty('remote_id');
        $signature = $this->getProperty('signature');

        if ($this->service->checkSignature($localId, $remoteId, $signature)) {
            /** @var UserConnection $connection */
            $connection = UserConnection::query()
                ->where('service', 'Telegram')
                ->where('remote_id', $remoteId)
                ->first();
            if ($connection && $data = $this->prepareOutput($connection->user)) {
                return $this->success($data);
            }
        }

        return $this->failure('Not Found', 404);
    }

    protected function prepareOutput(User $user): array
    {
        if ($subscription = $user->currentSubscription) {
            return [
                'user_id' => $user->id,
                'subscription' => true,
                'level' => $subscription->level->only('id', 'title', 'price'),
                'created_at' => $subscription->created_at->toDateTimeString(),
                'active_until' => $subscription->active_until->toDateTimeString(),
                'cancelled' => $subscription->cancelled,
            ];
        }

        return [
            'user_id' => $user->id,
            'subscription' => false,
        ];
    }
}