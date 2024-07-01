<?php

namespace App\Controllers\Admin;

use App\Controllers\Traits\FileModelController;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Vesp\Controllers\ModelController;

class Users extends ModelController
{
    use FileModelController;

    protected string|array $scope = 'users';
    protected string $model = User::class;
    public array $attachments = ['avatar'];

    protected function beforeGet(Builder $c): Builder
    {
        return $c->with('avatar:id,uuid,updated_at');
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = trim($this->getProperty('query', ''))) {
            $c->where(
                static function (Builder $c) use ($query) {
                    $c->where('username', 'LIKE', "%$query%");
                    $c->orWhere('fullname', 'LIKE', "%$query%");
                    $c->orWhere('email', 'LIKE', "%$query%");
                    $c->orWhere('phone', 'LIKE', "%$query%");
                }
            );
        }

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        try {
            /** @var User $record */
            $record->fillData($this->getProperties());
        } catch (Throwable $e) {
            return $this->failure($e->getMessage());
        }

        if ($error = $this->processFiles($record)) {
            return $error;
        }

        return null;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->with('role:id,title');
        $c->with('avatar:id,uuid,updated_at');

        return $c;
    }

    protected function beforeDelete(Model $record): ?ResponseInterface
    {
        /** @var User $record */
        if ($this->user->id === $record->id) {
            return $this->failure('errors.user.delete_own');
        }

        return null;
    }
}