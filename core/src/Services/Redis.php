<?php

namespace App\Services;

use Predis\Client;

class Redis extends Client
{
    public function __construct($parameters = null, $options = null)
    {
        parent::__construct(['host' => 'redis'], $options);
    }

    public function send(string $event, mixed $data = [], ?string $room = 'general'): void
    {
        $this->publish(
            $room,
            json_encode(['secret' => getenv('SOCKET_SECRET'), 'event' => $event, 'data' => $data], JSON_THROW_ON_ERROR)
        );
    }

    public function clearRoutesCache(): void
    {
        if (getenv('CACHE_PAGES_TIME')) {
            $keys = $this->keys('routes*');
            foreach ($keys as $key) {
                $this->del($key);
            }
        }
    }
}