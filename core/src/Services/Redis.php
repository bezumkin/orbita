<?php

namespace App\Services;

use Predis\Client;

class Redis extends Client
{
    public function __construct($parameters = null, $options = null)
    {
        parent::__construct(['host' => 'redis'], $options);
    }
}