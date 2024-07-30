<?php

namespace App\Controllers\User;

use App\Models\Level;
use App\Models\User;
use App\Services\Socket;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Subscription extends Controller
{
    protected string|array $scope = 'profile';

    public function post(): ResponseInterface
    {
        /** @var User $user */
        $user = $this->user;
        if (!$subscription = $user->currentSubscription) {
            return $this->failure('Access Denied', 403);
        }

        $action = $this->getProperty('action');
        if ($action === 'next') {
            $levelId = $this->getProperty('level');
            /** @var Level $level */
            if (!$levelId || !$level = Level::query()->where('active', true)->find($levelId)) {
                return $this->failure('errors.payment.wrong_level');
            }
            $period = (int)$this->getProperty('period') ?: 1;
            if ($subscription->level->price > $level->price) {
                // Downgrade
                $subscription->next_level_id = $level->id;
                $subscription->next_period = $period;
                $subscription->save();
            } else {
                $left = $subscription->paidAmountLeft();
                $cost = $level->costForPeriod($period);
                if ($left >= $cost) {
                    $perDay = $level->costPerDay();
                    $days = ceil($left / $perDay);
                    $activeUntil = Carbon::now()->addDays($days);
                    // Free Upgrade
                    $subscription->level_id = $level->id;
                    $subscription->period = $period;
                    $subscription->active_until = $activeUntil;
                    $subscription->cancelled = false;
                    $subscription->next_level_id = null;
                    $subscription->next_period = null;
                    $subscription->save();
                }
            }
        } elseif ($action === 'cancel-next') {
            $subscription->next_level_id = null;
            $subscription->next_period = null;
            $subscription->save();
        } elseif ($action === 'cancel') {
            $subscription->cancelled = true;
            $subscription->next_level_id = null;
            $subscription->next_period = null;
            $subscription->save();
        } elseif ($action === 'renew') {
            $subscription->cancelled = false;
            $subscription->save();
        } else {
            return $this->failure('Not Found', 404);
        }
        Socket::send('profile', ['id' => $user->id]);

        return $this->success();
    }
}