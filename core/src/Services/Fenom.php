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

        $this->addModifiers();
    }

    protected function addModifiers(): void
    {
        $this->addModifier('declension', static function ($amount, $variants, $number = true, $delimiter = '|') {
            $variants = explode($delimiter, $variants);
            if (count($variants) < 2) {
                $variants = array_fill(0, 3, $variants[0]);
            } elseif (count($variants) < 3) {
                $variants[2] = $variants[1];
            }
            $modulusOneHundred = $amount % 100;
            $text = match ($amount % 10) {
                1 => $modulusOneHundred === 11
                    ? $variants[2]
                    : $variants[0],
                2, 3, 4 => ($modulusOneHundred > 10) && ($modulusOneHundred < 20)
                    ? $variants[2]
                    : $variants[1],
                default => $variants[2],
            };

            return $number
                ? $amount . ' ' . $text
                : $text;
        });
    }
}