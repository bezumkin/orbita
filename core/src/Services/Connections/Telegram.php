<?php

namespace App\Services\Connections;

use App\Models\User;
use App\Models\UserConnection;
use App\Services\ConnectionService;
use App\Services\Redis;

class Telegram extends ConnectionService
{
    public const string URL = 'https://t.me/';
    protected Redis $redis;

    public function __construct()
    {
        $this->redis = new Redis();
    }

    public function getConnectionLink(User $user): string
    {
        return self::URL . getenv('CONNECTION_TELEGRAM_BOT') . '?' . getenv('CONNECTION_TELEGRAM_CMD') . $user->id;
    }

    public function connectUser(User $user, string $remoteId, ?array $data = null): bool
    {
        $pk = ['user_id' => $user->id, 'service' => 'Telegram'];
        if (!$connection = UserConnection::query()->where($pk)->first()) {
            $connection = new UserConnection($pk);
        }
        $connection->remote_id = $remoteId;
        $connection->data = $data;
        if ($connection->save()) {
            $this->redis->send('user-connections', ['id' => $user->id]);

            return true;
        }

        return false;
    }

    public function disconnectUser(User $user): bool
    {
        if ($connection = $user->connections()->where('service', 'Telegram')->first()) {
            $connection->delete();
            $this->redis->send('user-connections', ['id' => $user->id]);

            return true;
        }

        return false;
    }

    public function checkSignature(int $localId, string $remoteId, string $signature): bool
    {
        $parts = [$localId, $remoteId, getenv('CONNECTION_TELEGRAM_KEY')];

        return sha1(implode('-', $parts)) === $signature;
    }
}