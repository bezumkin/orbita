<?php

namespace App\Controllers\Admin\Videos;

use App\Models\Video;
use App\Models\VideoQuality;
use Illuminate\Database\Eloquent\Builder;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelGetController;

class Qualities extends ModelGetController
{
    protected string|array $scope = 'videos';
    protected string $model = VideoQuality::class;
    protected string|array $primaryKey = ['video_id', 'quality'];
    protected ?Video $video = null;

    public function checkScope(string $method): ?ResponseInterface
    {
        if (!$this->video = Video::query()->find($this->getProperty('video_id'))) {
            return $this->failure('Not Found', 404);
        }

        return parent::checkScope($method);
    }

    protected function beforeCount(Builder $c): Builder
    {
        return $c->where('video_id', $this->video->id);
    }

    protected function addSorting(Builder $c): Builder
    {
        return $c->orderBy('quality');
    }

    protected function afterCount(Builder $c): Builder
    {
        return $c->with('File');
    }
}