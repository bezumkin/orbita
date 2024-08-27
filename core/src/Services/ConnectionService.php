<?php

namespace App\Services;

use App\Models\User;

abstract class ConnectionService
{
    abstract public function getConnectionLink(User $user): string;

    abstract public function connectUser(User $user, string $remoteId, ?array $data = null): bool;

    abstract public function disconnectUser(User $user): bool;

    public function checkSignature(int $localId, string $remoteId, string $signature): bool
    {
        return true;
    }
}