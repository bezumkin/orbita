<?php

namespace App\Models\Traits;

use App\Models\CommentFile;
use App\Models\File;
use App\Models\PageFile;
use App\Models\TopicFile;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array content
 * @method HasMany contentFiles
 */
trait ContentFilesTrait
{
    public function processUploadedFiles(): void
    {
        $files = [];
        $content = $this->content;
        if ($content && !empty($content['blocks'])) {
            $blocks = $content['blocks'];
            $fileTypes = ['image', 'file', 'audio', 'video'];
            foreach ($blocks as $idx => $block) {
                $type = $block['type'];
                if (in_array($type, $fileTypes, true)) {
                    if (empty($block['data']['id'])) {
                        unset($blocks[$idx]);
                    } else {
                        $files[$block['data']['id']] = $type;
                    }
                }
            }
            $content['blocks'] = $blocks;
            $this->content = $content;
            $this->save();
        }

        // Save files
        foreach ($files as $id => $type) {
            $key = ['file_id' => $id, 'type' => $type];
            /** @var TopicFile|CommentFile|PageFile $contentFile */
            if (!$contentFile = $this->contentFiles()->where($key)->first()) {
                $contentFile = $this->contentFiles()->create($key);
                $contentFile->save();
            }
            /** @var File $file */
            if ($file = $contentFile->file()->where('temporary', true)->find($id)) {
                $file->temporary = false;
                $file->save();
            }
        }

        // Clean abandoned files
        $ids = array_keys($files);
        foreach ($this->contentFiles()->whereNotIn('file_id', $ids)->cursor() as $contentFile) {
            if ($contentFile->type !== 'video') {
                $contentFile->file->delete();
            }
            $contentFile->delete();
        }
    }
}
