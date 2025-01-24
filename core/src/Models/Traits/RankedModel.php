<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $rank
 *
 * @method save
 */
trait RankedModel
{
    protected static function bootRankedModel(): void
    {
        static::saving(
            static function (self $record) {
                if (!$record->rank) {
                    $record->rank = $record->getCurrentRank();
                }
            }
        );

        static::deleted(
            static function (self $record) {
                $record->reOrderRank(true);
            }
        );
    }

    public function getRankedQuery(): Builder
    {
        return $this->newQuery();
    }

    public function getCurrentRank(): int
    {
        return $this->getRankedQuery()->count();
    }

    public function reOrderRank(bool $force = false): void
    {
        /** @var RankedModel $object */
        if (!$force) {
            $needSorting = $this->getRankedQuery()
                ->selectRaw('`rank`, COUNT(*) as `amount`')
                ->groupBy('rank')
                ->having('amount', '>', 1);
            $force = $needSorting->get()->isNotEmpty();
        }

        if ($force) {
            $records = $this->getRankedQuery()
                ->orderBy('rank');
            if ($this->timestamps) {
                $records->orderBy('created_at');
            } else {
                $records->orderBy($this->getKeyName());
            }

            $rank = 1;
            /** @var RankedModel $record */
            foreach ($records->get() as $record) {
                $record->rank = $rank++;
                $record->save();
            }
        }
    }
}
