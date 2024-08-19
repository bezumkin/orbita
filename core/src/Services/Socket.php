<?php

namespace App\Services;

class Socket
{
    public static function send(string $event, mixed $data = []): void
    {
        $client = new Redis();
        $client->send($event, $data);
    }
}