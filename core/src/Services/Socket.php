<?php

namespace App\Services;

class Socket
{
    public static function send(string $event, mixed $data): void
    {
        $client = new Redis();
        $client->publish(
            'general',
            json_encode([
                'secret' => getenv('REDIS_SECRET'),
                'event' => $event,
                'data' => $data,
            ], JSON_THROW_ON_ERROR)
        );
    }
}