<?php

namespace App\Controllers\Admin;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class UserRoles extends ModelController
{
    protected string|array $scope = 'roles';
    protected string $model = UserRole::class;

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = $this->getProperty('query')) {
            $c->where('title', 'LIKE', "%$query%");
        }

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        return $c->withCount('users');
    }

    protected function beforeDelete(Model $record): ?ResponseInterface
    {
        /** @var UserRole $record */
        if ($this->user->role_id === $record->id) {
            return $this->failure('errors.user_role.delete_own');
        }

        return null;
    }
}
