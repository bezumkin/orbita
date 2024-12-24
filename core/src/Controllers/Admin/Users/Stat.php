<?php

namespace App\Controllers\Admin\Users;

use App\Controllers\Traits\StatModelController;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Controllers\Controller;

class Stat extends Controller
{
    use StatModelController;

    protected string|array $scope = 'users';

    protected function getMinDate(): ?string
    {
        return User::query()->where('active', true)->min('created_at');
    }

    protected function getMaxDate(): ?string
    {
        return User::query()->where('active', true)->max('created_at');
    }

    protected function getPrevious(array $dates): int
    {
        return User::query()
            ->where('active', true)
            ->whereBetween('created_at', $dates)
            ->count('id');
    }

    protected function getCondition(array $dates): Builder
    {
        return User::query()
            ->where('active', true)
            ->whereBetween('created_at', $dates)
            ->selectRaw('DATE(`created_at`) as `date`, COUNT(`id`) as `amount`')
            ->groupByRaw('`date`');
    }
}