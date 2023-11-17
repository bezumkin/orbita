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
        $this->setOptions(self::DENY_NATIVE_FUNCS | self::AUTO_RELOAD | self::FORCE_VERIFY);
        $this->addAllowedFunctions([
            'print_r',
            'number_format',
            'trim',
        ]);

        $this->addModifier(
            'content_preview',
            static function (array $content = []) {
                $text = [];
                if ($content && $content['blocks']) {
                    foreach ($content['blocks'] as $block) {
                        if ($block['type'] === 'paragraph') {
                            if (!empty($block['data']) && !empty($block['data']['text'])) {
                                $text[] = strip_tags($block['data']['text'], '<br>');
                            }
                        }
                    }
                }

                return implode('<br><br>', $text);
            }
        );
    }
}