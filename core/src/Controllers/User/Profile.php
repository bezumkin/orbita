<?php

namespace App\Controllers\User;

use App\Controllers\Traits\FileModelController;
use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Profile extends \Vesp\Controllers\User\Profile
{
    use FileModelController;

    protected string|array $scope = 'profile';
    public array $attachments = ['avatar'];

    public function get(): ResponseInterface
    {
        /** @var User $user */
        if ($user = $this->user) {
            $data = $user->only('id', 'username', 'fullname', 'email', 'phone', 'notify');
            $data['role'] = $user->role->only('id', 'title', 'scope');
            $data['avatar'] = $user->avatar?->only('uuid', 'updated_at');
            $data['subscription'] = $user->currentSubscription?->only(
                'level_id',
                'next_level_id',
                'active_until',
                'cancelled'
            );

            return $this->success(['user' => $data]);
        }

        return $this->failure('Authentication required', 401);
    }

    public function patch(): ResponseInterface
    {
        try {
            $this->user->fillData($this->getProperties());
        } catch (Throwable $e) {
            return $this->failure($e->getMessage());
        }

        if ($error = $this->processFiles($this->user)) {
            return $error;
        }

        $this->user->save();
        $this->user->refresh();

        return $this->get();
    }
}
