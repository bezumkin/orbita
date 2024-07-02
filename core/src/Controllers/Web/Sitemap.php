<?php

namespace App\Controllers\Web;

use App\Models\Page;
use App\Models\PageFile;
use App\Models\Topic;
use App\Models\TopicFile;
use Illuminate\Database\Capsule\Manager;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Sitemap extends Controller
{
    protected string $baseUrl;
    protected string $apiUrl;

    public function __construct(Manager $eloquent)
    {
        parent::__construct($eloquent);

        $this->baseUrl = rtrim(getenv('SITE_URL'), '/') . '/';
        $api = rtrim(getenv('API_URL'), '/') . '/';
        $this->apiUrl = !str_starts_with($api, 'http') ? $this->baseUrl . ltrim($api, '/') : $api;
    }

    public function get(): ResponseInterface
    {
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
                'loc' => $this->baseUrl . 'topics/' . $topic->uuid,
                'lastmod' => $topic->published_at->toIso8601String(),
                'image:image' => $this->addImages($topic->contentFiles),
            ];
            if ($topic->cover) {
                array_unshift(
                    $row['image:image'],
                    ['image:loc' => $this->apiUrl . 'image/' . $topic->cover->uuid . '?t=' . $topic->cover->updated_at->timestamp]
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
                'loc' => $this->baseUrl . 'pages/' . $page->alias,
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
            $time = $contentFile->file->updated_at->timestamp;
            if ($contentFile->type === 'image') {
                $rows[] = ['image:loc' => $this->apiUrl . 'image/' . $contentFile->file->uuid . '?t=' . $time];
            } elseif ($contentFile->type === 'video' && $contentFile->file->video) {
                $rows[] = ['image:loc' => $this->apiUrl . 'poster/' . $contentFile->file->video->id . '?t=' . $time];
            }
        }

        return $rows;
    }
}