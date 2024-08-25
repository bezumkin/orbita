<?php

namespace App\Controllers\Web;

use App\Models\Page;
use App\Models\PageFile;
use App\Models\Topic;
use App\Models\TopicFile;
use App\Services\Utils;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Sitemap extends Controller
{

    public function get(): ResponseInterface
    {
        $siteUrl = Utils::getSiteUrl();

        $rows = [];
        $topics = Topic::query()
            ->where('active', true)
            ->select('id', 'uuid', 'cover_id', 'published_at')
            ->orderByDesc('published_at')
            ->with('cover:id,uuid,updated_at')
            ->with('contentFiles.file:id,uuid,updated_at', 'contentFiles.file.video:file_id,id');
        foreach ($topics->get() as $topic) {
            /** @var Topic $topic */
            $row = [
                'loc' => $topic->getLink(),
                'lastmod' => $topic->published_at->toIso8601String(),
                'image:image' => $this->addImages($topic->contentFiles),
            ];
            if ($topic->cover) {
                array_unshift(
                    $row['image:image'],
                    ['image:loc' => Utils::getImageLink($topic->cover)]
                );
            }
            $rows[] = $row;
        }

        $pages = Page::query()
            ->where('active', true)
            ->select('id', 'alias', 'updated_at')
            ->orderBy('rank')
            ->with('contentFiles.file:id,uuid,updated_at', 'contentFiles.file.video:file_id,id');
        foreach ($pages->cursor() as $page) {
            /** @var Page $page */
            $row = [
                'loc' => $siteUrl . 'pages/' . $page->alias,
                'lastmod' => $page->updated_at->toIso8601String(),
                'image:image' => $this->addImages($page->contentFiles),
            ];
            $rows[] = $row;
        }

        return $this->success($rows);
    }

    protected function addImages(iterable $contentFiles): array
    {
        $rows = [];
        /** @var TopicFile|PageFile $contentFile */
        foreach ($contentFiles as $contentFile) {
            if ($contentFile->type === 'image') {
                $rows[] = ['image:loc' => Utils::getImageLink($contentFile->file)];
            } elseif ($contentFile->type === 'video' && $contentFile->file->video) {
                $rows[] = ['image:loc' => Utils::getVideoLink($contentFile->file->video)];
            }
        }

        return $rows;
    }
}