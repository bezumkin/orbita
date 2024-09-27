<?php

namespace App\Controllers\User;

use App\Models\File;
use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

/** @property User $user */
class Avatar extends Controller
{
    protected string|array $scope = 'profile';
    protected string $model = File::class;

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
        if (!$file = $this->getProperty('file')) {
            return $this->failure('errors.user.file.no_file');
        }

        if (!$object = $this->user->avatar) {
            $object = new File();
        }
        $object->uploadFile($file, $this->getProperty('metadata'));
        $this->user->avatar_id = $object->id;
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