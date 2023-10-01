<?php

namespace App\Services;

use Fenom\Provider;

class Fenom extends \Fenom
{
    public function __construct()
    {
        parent::__construct(new Provider(getenv('TEMPLATE_DIR')));

        $cache = getenv('CACHE_DIR') . 'fenom/';
        if (!file_exists($cache) && !mkdir($cache) && !is_dir($cache)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $cache));
        }

        $this->setCompileDir($cache);
        $this->setOptions(self::DENY_NATIVE_FUNCS | self::AUTO_RELOAD | self::FORCE_VERIFY | self::AUTO_ESCAPE);
        $this->addAllowedFunctions(['print_r']);
    }
}