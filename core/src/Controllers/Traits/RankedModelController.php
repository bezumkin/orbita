<?php

namespace App\Controllers\Traits;

use App\Models\Traits\RankedModel;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;

trait RankedModelController
{
    public function changeRank(Model $object, string $action): void
    {
        /** @var RankedModel $object */
        /** @var RankedModel $other */
        if ($action === 'moveUp') {
            if ($object->rank > 0) {
                --$object->rank;
                if ($other = $object->getRankedQuery()->where('rank', $object->rank)->first()) {
                    ++$other->rank;
                    $other->save();
                }
                $object->save();
            }
        } elseif ($action === 'moveDown') {
            ++$object->rank;
            if ($other = $object->getRankedQuery()->where('rank', $object->rank)->first()) {
                --$other->rank;
                $other->save();
            }
            $object->save();
        }

        $object->reOrderRank(true);
    }

    public function post(): ResponseInterface
    {
        if ($action = $this->getProperty('action')) {
            /** @var RankedModel|Model $object */
            if (!$object = (new $this->model())->newQuery()->find($this->getPrimaryKey())) {
                return $this->failure('Not Found', 404);
            }
            $this->changeRank($object, $action);
        }

        return $this->success();
    }

}
