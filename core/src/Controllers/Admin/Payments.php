<?php

namespace App\Controllers\Admin;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Payments extends ModelController
{
    protected string|array $scope = 'payments';
    protected string $model = Payment::class;
    protected ?Builder $query = null;

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = trim($this->getProperty('query', ''))) {
            $c->whereHas('user', static function (Builder $c) use ($query) {
                $c->where('username', 'LIKE', "%$query%");
                $c->orWhere('fullname', 'LIKE', "%$query%");
                $c->orWhere('email', 'LIKE', "%$query%");
                $c->orWhere('phone', 'LIKE', "%$query%");
            });
            $c->whereHas('topic', static function (Builder $c) use ($query) {
                $c->where('title', 'LIKE', "%$query%");
            });
            $c->whereHas('subscription', static function (Builder $c) use ($query) {
                $c->whereHas('level', static function (Builder $c) use ($query) {
                    $c->where('title', 'LIKE', "%$query%");
                });
            });
        }
        $status = $this->getProperty('status');
        if ($status !== null) {
            $c->where('paid', (bool)$status);
        }
        if ($date = $this->getProperty('date')) {
            $c->whereBetween('created_at', $date);
        }
        $this->query = clone $c;

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->with('user:id,role_id,avatar_id,username,fullname', 'user.avatar:id,uuid,updated_at');
        $c->with('topic:id,uuid,title');

        return $c;
    }

    protected function beforeDelete(Model $record): ?ResponseInterface
    {
        /** @var Payment $record */
        if ($record->paid) {
            return $this->failure('errors.payment.delete_paid');
        }

        return parent::beforeDelete($record);
    }

    public function prepareList(array $array): array
    {
        $array['sum'] = $this->query->sum('amount');

        return $array;
    }

}