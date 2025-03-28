<?php

namespace App\Controllers\User;

use App\Controllers\Traits\FileModelController;
use App\Models\User;
use Illuminate\Database\Capsule\Manager;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Profile extends \Vesp\Controllers\User\Profile
{
    use FileModelController;

    protected string|array $scope = 'profile';
    public array $attachments = ['avatar'];
    public array $allowedTypes = ['avatar' => ['image/png', 'image/jpeg']];
    public array $maximumSize = ['avatar' => 5242880];

    public function __construct(Manager $eloquent)
    {
        parent::__construct($eloquent);

        if ($avatarExtensions = getenv('AVATAR_UPLOAD_EXTENSIONS')) {
            $this->allowedTypes = ['avatar' => []];
            $extensions = array_map('trim', explode(',', $avatarExtensions));
            foreach ($extensions as $extension) {
                $extension = strtolower($extension);
                if ($extension === 'jpg') {
                    $this->allowedTypes['avatar'][] = 'image/jpeg';
                } else {
                    $this->allowedTypes['avatar'][] = 'image/' . $extension;
                }
            }
        }
        if ($avatarLimit = (int)getenv('AVATAR_UPLOAD_LIMIT')) {
            $this->maximumSize['avatar'] = $avatarLimit * 1024 * 1024;
        }
    }

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
        $data = array_filter($this->getProperties(), static function ($key) {
            return in_array($key, ['username', 'fullname', 'email', 'phone', 'notify', 'password']);
        }, ARRAY_FILTER_USE_KEY);

        try {
            $this->user->fillData($data);
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
