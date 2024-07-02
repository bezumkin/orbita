<?php

namespace App\Controllers\Web;

use App\Models\Topic;
use Psr\Http\Message\ResponseInterface;

class Rss extends Sitemap
{
    public function get(): ResponseInterface
    {
        $rows = [];

        $topics = Topic::query()
            ->where('active', true)
            ->select('uuid', 'title', 'content', 'teaser', 'cover_id', 'user_id', 'published_at')
            ->with('cover:id,uuid,type,updated_at')
            ->with('user:id,fullname')
            ->limit($this->getProperty('limit', 20))
            ->orderByDesc('published_at');
        foreach ($topics->get() as $topic) {
            /** @var Topic $topic */
            $row = [
                'link' => $this->baseUrl . 'topics/' . $topic->uuid,
                'title' => $topic->title,
                'description' => $topic->teaser,
                'content' => '',
                'date' => $topic->published_at->toIso8601String(),
                'author' => [
                    ['name' => $topic->user->fullname]
                ],
            ];
            if ($topic->cover) {
                $row['image'] = [
                    'type' => $topic->cover->type,
                    'url' => $this->getImageUrl($topic->cover->toArray()),
                ];
            }
            if ($topic->hasAccess(null)) {
                $row['content'] = $this->getContent($topic);
            }
            $rows[] = $row;
        }

        return $this->success($rows);
    }

    protected function getContent(Topic $topic): string
    {
        $blocks = [];
        foreach ($topic->content['blocks'] as $block) {
            if ($block['type'] === 'header') {
                $tag = 'h' . $block['data']['level'];
                $blocks[] = "<$tag>" . $block['data']['text'] . "</$tag>";
            } elseif ($block['type'] === 'paragraph') {
                $blocks[] = "<p>" . $block['data']['text'] . '</p>';
            } elseif ($block['type'] === 'image') {
                $blocks[] = '<img src="' . $this->getImageUrl($block['data']) . '" alt="" />';
            }
        }

        return implode(PHP_EOL, $blocks);
    }

    protected function getImageUrl(array $file): string
    {
        return $this->apiUrl . 'image/' . $file['uuid'] . '?t=' . strtotime($file['updated_at']);
    }
}