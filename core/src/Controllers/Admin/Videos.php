<?php

namespace App\Controllers\Admin;

use App\Controllers\Traits\FileModelController;
use App\Models\Video;
use App\Models\VideoUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Videos extends ModelController
{
    use FileModelController;

    protected string|array $scope = 'videos';
    protected string $model = Video::class;
    public array $attachments = ['image'];

    protected function beforeGet(Builder $c): Builder
    {
        $c->with('image:id,uuid,updated_at');

        return $c;
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = trim($this->getProperty('query', ''))) {
            $c->where(
                static function (Builder $c) use ($query) {
                    $c->where('title', 'LIKE', "%$query%");
                    $c->orWhere('description', 'LIKE', "%$query%");
                }
            );
        }
        if ($this->getProperty('active')) {
            $c->where('active', true);
        }

        return $c;
    }

    protected function afterCount(Builder $c): Builder
    {
        $c->with('file:id,uuid,width,height,size,updated_at', 'image:id,uuid,width,height,size,updated_at');

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        foreach ($this->attachments as $attachment) {
            if ($this->getProperty("new_$attachment") === false) {
                return $this->failure('errors.video.no_image');
            }
        }

        if ($error = $this->processFiles($record)) {
            return $error;
        }

        return null;
    }

    public function prepareRow(Model $object): array
    {
        /** @var Video $object */
        $array = $object->toArray();
        if ($this->getPrimaryKey()) {
            /** @var VideoUser $status */
            $status = $object->videoUsers()->where('user_id', $this->user->id)->first();
            $array['status'] = $status?->only('quality', 'time', 'speed', 'volume');
        }

        return $array;
    }
}