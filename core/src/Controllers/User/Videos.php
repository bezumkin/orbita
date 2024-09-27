<?php

namespace App\Controllers\User;

use App\Models\Video;
use App\Models\VideoQuality;
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

        if (!$quality = $this->getProperty('quality')) {
            /** @var VideoQuality $videoQuality */
            if ($videoQuality = $video->qualities()->orderByDesc('quality')->first()) {
                $quality = $videoQuality->quality;
            }
        }
        if ($quality) {
            $videoUser->fill([
                'quality' => $quality,
                'volume' => $this->getProperty('volume', 1),
                'speed' => $this->getProperty('speed', 1),
                'time' => $this->getProperty('time', 0),
            ]);
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