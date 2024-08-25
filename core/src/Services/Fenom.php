<?php

namespace App\Services;

use Fenom\Provider;
use RuntimeException;

class Fenom extends \Fenom
{
    public function __construct()
    {
        parent::__construct(new Provider(getenv('TEMPLATE_DIR')));

        $cache = getenv('CACHE_DIR') . 'fenom/';
        if (!file_exists($cache) && !mkdir($cache) && !is_dir($cache)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $cache));
        }

        $this->setCompileDir($cache);
        $this->setOptions(self::DENY_NATIVE_FUNCS | self::AUTO_RELOAD | self::FORCE_VERIFY);
        $this->addAllowedFunctions([
            'print_r',
            'number_format',
            'trim',
        ]);
    }
}