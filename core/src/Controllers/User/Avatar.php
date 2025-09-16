<?php

namespace App\Controllers\User;

use App\Controllers\Traits\FileModelController;
use App\Models\File;
use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

/** @property User $user */
class Avatar extends Controller
{
    use FileModelController;

    protected string|array $scope = 'profile';
    protected string $model = File::class;
    public array $attachments = [];
    public array $allowedTypes = [];
    public array $maximumSize = [];

    public function get(): ResponseInterface
    {
        if ($this->user->avatar) {
            $file = 'data:' . $this->user->avatar->type . ';base64,' . base64_encode($this->user->avatar->getFile());

            return $this->success([
                'file' => $file,
                'size' => $this->user->avatar->size,
                'metadata' => $this->user->avatar->metadata,
            ]);
        }

        return $this->success();
    }

    public function post(): ResponseInterface
    {
        if (!$this->getProperty('file')) {
            return $this->failure('errors.user.file.no_file');
        }

        // Use upload settings from user profile controller
        $controller = (new \DI\Container())->get(Profile::class);
        $this->attachments = $controller->attachments;
        $this->allowedTypes = $controller->allowedTypes;
        $this->maximumSize = $controller->maximumSize;

        $this->setProperty('new_avatar', [
            'file' => $this->getProperty('file'),
            'metadata' => $this->getProperty('metadata'),
        ]);
        $this->unsetProperty('file');
        if ($error = $this->processFiles($this->user)) {
            return $error;
        }
        $this->user->save();

        return $this->success();
    }

    public function delete(): ResponseInterface
    {
        if ($this->user->avatar) {
            $this->user->avatar->delete();
        }

        return $this->success();
    }

    protected function getPrimaryKey(): int
    {
        return $this->user->avatar_id ?: -1;
    }
}