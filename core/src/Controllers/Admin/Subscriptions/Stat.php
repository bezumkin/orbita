<?php

namespace App\Controllers\Admin\Subscriptions;

use App\Controllers\Traits\StatModelController;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Controllers\Controller;

class Stat extends Controller
{
    use StatModelController;

    protected string|array $scope = 'levels';

    protected function getMinDate(): ?string
    {
        return Subscription::query()->whereNotNull('active_until')->min('created_at');
    }

    protected function getMaxDate(): ?string
    {
        return Subscription::query()->whereNotNull('active_until')->max('created_at');
    }

    protected function getPrevious(array $dates): int
    {
        return Subscription::query()
            ->whereNotNull('active_until')
            ->whereBetween('created_at', $dates)
            ->count('id');
    }

    protected function getCondition(array $dates): Builder
    {
        return Subscription::query()
            ->whereNotNull('active_until')
            ->whereBetween('created_at', $dates)
            ->selectRaw('DATE(`created_at`) as `date`, COUNT(`id`) as `amount`')
            ->groupByRaw('`date`');
    }
}