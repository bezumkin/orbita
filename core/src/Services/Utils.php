<?php

namespace App\Services;

use App\Models\File;
use App\Models\Video;
use EditorJS\EditorJS;

class Utils
{
    public static function getSiteUrl(): string
    {
        return rtrim(getenv('SITE_URL'), '/') . '/';
    }

    public static function getApiUrl(): string
    {
        $base = self::getSiteUrl();
        $api = rtrim(getenv('API_URL'), '/') . '/';

        return !str_starts_with($api, 'http') ? $base . ltrim($api, '/') : $api;
    }

    public static function getImageLink(array|File $file, array $params = []): string
    {
        if (!is_array($file)) {
            $file = $file->only('id', 'uuid', 'updated_at');
        }

        if (!empty($file['updated_at'])) {
            $params['t'] = strtotime($file['updated_at']);
        }

        return self::getApiUrl() . 'image/' . $file['uuid'] . '?' . self::getImageParams($params);
    }

    public static function getVideoLink(array|Video $file, array $params = []): string
    {
        if (!is_array($file)) {
            $file = ['uuid' => $file->id, 'updated_at' => $file->file->updated_at];
        }

        if (!empty($file['updated_at'])) {
            $params['t'] = strtotime($file['updated_at']);
        }

        return self::getApiUrl() . 'poster/' . $file['uuid'] . '?' . self::getImageParams($params);
    }

    public static function getEmbedLink(array $file, array $params = []): string
    {
        if (!empty($file['updated_at'])) {
            $params['t'] = strtotime($file['updated_at']);
        }

        return self::getApiUrl() . 'poster/embed/' . $file['service'] . '/' .
            $file['id'] . '?' . self::getImageParams($params);
    }

    public static function renderContent(array $blocks, ?array $imageParams = []): string
    {
        $output = [];
        foreach ($blocks as $block) {
            if ($block['type'] === 'header') {
                $tag = 'h' . $block['data']['level'];
                $output[] = "<$tag>" . $block['data']['text'] . "</$tag>";
            } elseif ($block['type'] === 'paragraph') {
                $output[] = '<p>' . $block['data']['text'] . '</p>';
            } elseif ($block['type'] === 'image') {
                $output[] = '<img src="' . self::getImageLink($block['data'], $imageParams) . '" alt="" />';
            } elseif ($block['type'] === 'video') {
                $output[] = '<img src="' . self::getVideoLink($block['data'], $imageParams) . '" alt="" />';
            } elseif ($block['type'] === 'embed') {
                $output[] = '<img src="' . self::getEmbedLink($block['data'], $imageParams) . '" alt="" />';
            } elseif ($block['type'] === 'list') {
                $tag = $block['data']['style'] === 'unordered' ? 'ul' : 'ol';
                $items = [];
                foreach ($block['data']['items'] as $item) {
                    $items[] = '<li>' . $item . '</li>';
                }
                $output[] = "<$tag>" . implode(PHP_EOL, $items) . "</$tag>";
            } elseif ($block['type'] === 'code') {
                $output[] = "<code><pre>" . htmlspecialchars($block['data']['code']) . "</pre></code>";
            } elseif ($block['type'] === 'file' || $block['type'] === 'audio') {
                $tmp = [$block['data']['title'], $block['data']['type']];
                if (!empty($block['data']['width']) && !empty($block['data']['height'])) {
                    $tmp[] = $block['data']['width'] . 'x' . $block['data']['height'];
                }
                $output[] = '<div class="file">' . implode(', ', $tmp) . '</div>';
            }
        }

        return implode(PHP_EOL, $output);
    }

    protected static function getImageParams(array $params = []): string
    {
        if (empty($params['w'])) {
            $params['w'] = getenv('RSS_MAX_IMAGE_WIDTH') ?: '800';
        }
        if (empty($params['fit'])) {
            $params['fit'] = 'max';
        }

        return http_build_query($params, '', '&');
    }

    public static function sanitizeContent(array $content, ?array $activeBlocks = null): array
    {
        if (!getenv('EDITOR_SANITIZATION')) {
            return $content;
        }

        $tags  = 'i,b,u,kbd,br,a[href]';
        $rules = [
            'header' => [
                'text' => ['type' => 'string', 'allowedTags' => $tags],
                'level' => 'int',
            ],
            'paragraph' => [
                'text' => ['type' => 'string', 'allowedTags' => $tags],
            ],
            'list' => [
                'style' => ['type' => 'string', 'canBeOnly' => ['ordered', 'unordered']],
                'items' => [
                    'type' => 'array',
                    'data' => [
                        '-' => ['type' => 'string', 'allowedTags' => $tags],
                    ],
                ],
            ],
            'file' => [
                'id' => 'int',
                'uuid' => 'string',
                'title' => ['type' => 'string', 'required' => false],
                'size' => ['type' => 'int', 'required' => false, 'allow_null' => true],
                'type' => 'string',
                'width' => ['type' => 'int', 'required' => false, 'allow_null' => true],
                'height' => ['type' => 'int', 'required' => false, 'allow_null' => true],
                'updated_at' => 'string',
            ],
            'video' => [
                'id' => 'int',
                'uuid' => 'string',
                'duration' => ['type' => 'int', 'required' => false],
                'size' => ['type' => 'int', 'required' => false],
                'width' => ['type' => 'int', 'required' => false],
                'height' => ['type' => 'int', 'required' => false],
                'moved' => ['type' => 'bool', 'required' => false],
                'audio' => ['type' => 'string', 'required' => false],
                'audio_size' => ['type' => 'int', 'required' => false],
                'updated_at' => 'string',
            ],
            'embed' => [
                'id' => 'string',
                'service' => 'string',
                'url' => 'string',
            ],
            'code' => [
                'language' => 'string',
                'code' => 'string',
            ],
        ];

        $tools = [
            'header' => $rules['header'],
            'paragraph' => $rules['paragraph'],
            'list' => $rules['list'],
            'file' => $rules['file'],
            'image' => array_merge($rules['file'], [
                'crop' => [
                    'type' => 'array',
                    'required' => false,
                    'data' => [
                        'width' => ['type' => 'int', 'required' => false],
                        'height' => ['type' => 'int', 'required' => false],
                        'fit' => ['type' => 'string', 'required' => false],
                    ],
                ],
            ]),
            'audio' => $rules['file'],
            'video' => $rules['video'],
            'embed' => $rules['embed'],
            'code' => $rules['code'],
        ];
        if ($activeBlocks) {
            $tools = array_filter($tools, static function ($key) use ($activeBlocks) {
                return $key === 'paragraph' || in_array($key, $activeBlocks, true);
            }, ARRAY_FILTER_USE_KEY);
        }

        $content['blocks'] = array_filter($content['blocks'], static function ($value) {
            return !empty($value['data']);
        });
        $editor = new EditorJS(
            json_encode($content, JSON_THROW_ON_ERROR),
            json_encode(['tools' => $tools], JSON_THROW_ON_ERROR)
        );
        $content['blocks'] = $editor->getBlocks();

        return $content;
    }
}