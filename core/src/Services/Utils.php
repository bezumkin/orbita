<?php

namespace App\Services;

use App\Models\File;
use App\Models\Video;

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
}