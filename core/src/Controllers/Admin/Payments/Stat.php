<?php

namespace App\Controllers\Admin\Payments;

use App\Controllers\Traits\StatModelController;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Controllers\Controller;

class Stat extends Controller
{
    use StatModelController;

    protected string|array $scope = 'payments';

    protected function getMinDate(): ?string
    {
        return Payment::query()->where('paid', true)->min('created_at');
    }

    protected function getMaxDate(): ?string
    {
        return Payment::query()->where('paid', true)->max('created_at');
    }

    protected function getPrevious(array $dates): float
    {
        return (float)Payment::query()
            ->where('paid', true)
            ->whereBetween('created_at', $dates)
            ->sum('amount');
    }

    protected function getCondition(array $dates): Builder
    {
        return Payment::query()
            ->where('paid', true)
            ->whereBetween('created_at', $dates)
            ->selectRaw('DATE(`created_at`) as `date`, SUM(`amount`) as `amount`')
            ->groupByRaw('`date`');
    }
}