<?php

namespace App\Controllers\Traits;

use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;

trait StatModelController
{
    public function get(): ResponseInterface
    {
        if (!$minDate = $this->getMinDate()) {
            return $this->success([
                'total' => 0,
                'pages' => 0,
                'rows' => [],
                'sum' => 0,
                'previous' => 0,
            ]);
        }
        $minDate = Carbon::createFromTimestamp(strtotime($minDate))->toImmutable();

        $pages = 0;
        $previous = false;

        if ($date = $this->getProperty('date')) {
            $start = Carbon::createFromTimestamp(strtotime($date[0]))->setTimezone(getenv('TZ'))->toImmutable();
            $end = Carbon::createFromTimestamp(strtotime($date[1]))->setTimezone(getenv('TZ'))->toImmutable();
        } elseif ($filter = $this->getProperty('filter')) {
            $page = $this->getProperty('page', 1) - 1;
            $now = Carbon::now()->toImmutable();
            switch ($filter) {
                case 'year':
                    $end = $now->subYears($page);
                    $start = $end->subYear();
                    $previous = $start->subYear();
                    $pages = (int)$minDate->diffInYears($now);
                    break;
                case 'quarter':
                    $end = $now->subQuarters($page);
                    $start = $end->subQuarter();
                    $previous = $start->subQuarter();
                    $pages = (int)$minDate->diffInQuarters($now);
                    break;
                case 'month':
                    $end = $now->subMonths($page);
                    $start = $end->subMonth();
                    $previous = $start->subMonth();
                    $pages = (int)$minDate->diffInMonths($now);
                    break;
                default:
                    $end = $now->subWeeks($page);
                    $start = $end->subWeek();
                    $previous = $start->subWeek();
                    $pages = (int)$minDate->diffInWeeks($now);
            }
        } else {
            $start = $minDate;
            $end = Carbon::createFromTimestamp(strtotime($this->getMaxDate()))->toImmutable();
        }

        $sum = 0;
        $rows = [];
        if ($days = $start->diffInDays($end)) {
            $dates = [];
            $condition = $this->getCondition([
                $start->toDateString() . ' 00:00:00',
                $end->toDateString() . ' 23:59:59',
            ]);
            foreach ($condition->cursor() as $record) {
                $dates[$record->date] = $record->amount;
            }

            for ($i = 0; $i <= $days; $i++) {
                $date = $start->addDays($i)->toDateString();
                $rows[] = [
                    'date' => $date,
                    'amount' => $dates[$date] ?? 0,
                ];
                $sum += $dates[$date] ?? 0;
            }
        }

        if ($previous) {
            $dates = [$previous->toDateString() . ' 00:00:00', $start->toDateString() . ' 23:59:59'];
            $previous = $this->getPrevious($dates);
        }

        return $this->success([
            'total' => count($rows),
            'pages' => $pages + 1,
            'rows' => $rows,
            'sum' => $sum,
            'previous' => $previous,
        ]);
    }
}