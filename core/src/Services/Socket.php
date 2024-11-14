<?php

namespace App\Services;

class Socket
{
    public static function send(string $event, mixed $data = [], ?string $room = 'general'): void
    {
        $client = new Redis();
        $client->send($event, $data, $room);
    }
}