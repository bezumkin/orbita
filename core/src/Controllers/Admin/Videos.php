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
    public array $allowedTypes = ['image' => 'image/'];

    protected function beforeGet(Builder $c): Builder
    {
        $c->with('file:id,uuid,size,updated_at');
        $c->with('image:id,uuid,updated_at');
        $c->with('audio:id,uuid,size,updated_at');

        return $c;
    }

    protected function beforeCount(Builder $c): Builder
    {
        if ($query = trim($this->getProperty('query', ''))) {
            $c->where(
                static function (Builder $c) use ($query) {
                    $c->where('id', $query);
                    $c->orWhere('title', 'LIKE', "%$query%");
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
        $c->with('file:id,uuid,width,height,size,updated_at');
        $c->with('image:id,uuid,width,height,size,updated_at');
        $c->with('audio:id,uuid,size,updated_at');

        return $c;
    }

    protected function beforeSave(Model $record): ?ResponseInterface
    {
        /** @var Video $record */
        foreach ($this->attachments as $attachment) {
            if ($this->getProperty("new_$attachment") === false) {
                return $this->failure('errors.video.no_image');
            }
        }

        if ($chapters = $this->getProperty('chapters')) {
            preg_match_all('#([\d:]+)\s+(.*)#', $chapters, $matches);
            $record->chapters = $matches ? array_combine($matches[1], $matches[2]) : null;
        }

        if ($error = $this->processFiles($record)) {
            return $error;
        }

        return null;
    }

    protected function afterSave(Model $record): Model
    {
        /** @var Video $record */
        $record->updateContentBlocks();

        return $record;
    }

    protected function addSorting(Builder $c): Builder
    {
        if (($sort = $this->getProperty('sort')) && str_starts_with($sort, 'file.')) {
            $sort = substr($sort, 5);
            $c->join('files', 'videos.file_id', '=', 'files.id');
            $c->orderBy($sort, $this->getProperty('dir') === 'desc' ? 'desc' : 'asc');

            return $c->select('videos.*');
        }

        return parent::addSorting($c);
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