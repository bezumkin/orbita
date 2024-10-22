<?php

namespace App\Controllers\User;

use App\Models\VideoUser;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Videos extends Controller
{
    protected string|array $scope = 'profile';

    public function post(): ResponseInterface
    {
        try {
            if ($quality = $this->getProperty('quality')) {
                VideoUser::query()->updateOrCreate([
                    'video_id' => $this->getProperty('uuid'),
                    'user_id' => $this->user->id,
                ], [
                    'quality' => $quality,
                    'volume' => $this->getProperty('volume', 1),
                    'speed' => $this->getProperty('speed', 1),
                    'time' => $this->getProperty('time', 0),
                ]);
            }
        } catch (\Exception $e) {
        }

        return $this->success();
    }

    public function get(): ResponseInterface
    {
        $videoUser = VideoUser::query()
            ->where(['video_id' => $this->getProperty('uuid'), 'user_id' => $this->user->id])
            ->select('quality', 'time', 'speed', 'volume')
            ->first();

        return $this->success($videoUser?->toArray());
    }
}