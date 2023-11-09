<?php

namespace App\Controllers\User;

use App\Models\Video;
use App\Models\VideoUser;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Videos extends Controller
{
    protected string|array $scope = 'profile';

    public function post(): ResponseInterface
    {
        /** @var Video $video */
        if (!$video = Video::query()->find($this->getProperty('uuid'))) {
            return $this->failure('Not Found', 404);
        }

        if (!$videoUser = $video->videoUsers()->where('user_id', $this->user->id)->first()) {
            $videoUser = new VideoUser();
            $videoUser->video_id = $video->id;
            $videoUser->user_id = $this->user->id;
        }
        if ($this->getProperty('quality')) {
            $videoUser->fill($this->getProperties());
            $videoUser->save();
        }

        return $this->success();
    }

    public function get(): ResponseInterface
    {
        /** @var Video $video */
        if (!$video = Video::query()->find($this->getProperty('uuid'))) {
            return $this->failure('Not Found', 404);
        }

        if ($videoUser = $video->videoUsers()->where('user_id', $this->user->id)->first()) {
            return $this->success($videoUser->toArray());
        }

        return $this->success();
    }
}