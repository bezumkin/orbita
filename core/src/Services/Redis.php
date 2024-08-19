<?php

namespace App\Services;

use Predis\Client;

class Redis extends Client
{
    public function __construct($parameters = null, $options = null)
    {
        parent::__construct(['host' => 'redis'], $options);
    }

    public function send(string $event, mixed $data = []): void
    {
        $this->publish(
            'general',
            json_encode(['secret' => getenv('SOCKET_SECRET'), 'event' => $event, 'data' => $data])
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