<?php

namespace App\Controllers\Admin;

use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Builder;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Notifications extends ModelController
{
    protected string|array $scope = 'notifications';
    protected string $model = UserNotification::class;

    protected function afterCount(Builder $c): Builder
    {
        $c->with('user:id,username,fullname');
        $c->with('topic:id,uuid,title');
        $c->with('comment:id,content');

        return $c;
    }

    public function post(): ResponseInterface
    {
        /** @var UserNotification $item */
        if ($item = UserNotification::query()->find($this->getPrimaryKey())) {
            if ($err = $item->sendEmail()) {
                return $this->failure($err);
            }
        }

        return $this->success();
    }
}