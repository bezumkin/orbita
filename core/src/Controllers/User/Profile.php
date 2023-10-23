<?php

namespace App\Controllers\User;

use App\Controllers\Traits\FileModelController;
use Psr\Http\Message\ResponseInterface;

class Profile extends \Vesp\Controllers\User\Profile
{
    use FileModelController;

    protected string|array $scope = 'profile';
    public array $attachments = ['avatar'];

    public function get(): ResponseInterface
    {
        if ($this->user) {
            $data = $this->user->only('id', 'username', 'fullname', 'email', 'phone');
            $data['avatar'] = $this->user->avatar?->only('uuid', 'updated_at');
            $data['role'] = $this->user->role->only('id', 'title', 'scope');

            return $this->success(['user' => $data]);
        }

        return $this->failure('Authentication required', 401);
    }

    public function patch(): ResponseInterface
    {
        try {
            $this->user->fillData($this->getProperties());
        } catch (\Exception $e) {
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
