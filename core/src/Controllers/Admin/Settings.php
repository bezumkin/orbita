<?php

namespace App\Controllers\Admin;

use App\Models\File;
use App\Models\Setting;
use App\Services\Redis;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\ModelController;

class Settings extends ModelController
{
    protected Redis $redis;
    protected string $model = Setting::class;
    protected string|array $scope = 'settings';
    protected string|array $primaryKey = 'key';

    public function __construct(Manager $eloquent, Redis $redis)
    {
        parent::__construct($eloquent);
        $this->redis = $redis;
    }

    public function patch(): ResponseInterface
    {
        /** @var Setting $setting */
        if (!$setting = Setting::query()->find($this->getProperty('key'))) {
            return $this->failure('Not Found', 404);
        }

        $value = $this->getProperty('value');

        /** @var Setting $setting */
        if ($setting->required && !$value) {
            return $this->failure('errors.setting.required');
        }

        if (in_array($setting->type, Setting::JSON_TYPES, true)) {
            if ($setting->required && !array_values($value)) {
                return $this->failure('errors.setting.required');
            }
            if ($setting->type === 'image') {
                if (!empty($value['file'])) {
                    if (!$file = $this->getFileFromValue($setting->value)) {
                        $file = new File();
                    }
                    $file->uploadFile($value['file'], @$value['metadata']);
                    $setting->value = json_encode($file->only('id', 'uuid', 'updated_at'));
                } elseif ($value === false && $file = $this->getFileFromValue($setting->value)) {
                    $file->delete();
                    $setting->value = null;
                }
            } else {
                $setting->value = is_array($value) ? json_encode($value) : null;
            }
        } else {
            $setting->value = $value;
        }
        $setting->save();
        $this->afterSave($setting);

        return $this->success($this->prepareRow($setting));
    }

    protected function afterSave(Model $record): Model
    {
        // Notification of the frontend about changes in settings
        $controller = new \App\Controllers\Web\Settings($this->eloquent);
        $this->redis->send('setting', $controller->prepareRow($record));
        $this->redis->clearRoutesCache();

        return $record;
    }

    protected function addSorting(Builder $c): Builder
    {
        $c->orderBy('rank');

        return $c;
    }

    public function prepareRow(Model $object): array
    {
        /** @var Setting $object */
        $array = $object->toArray();
        if (!empty($object->value) && in_array($object->type, Setting::JSON_TYPES, true)) {
            $array['value'] = json_decode($object->value, true);
        }

        return $array;
    }

    protected function getFileFromValue(?string $value): ?File
    {
        if ($value && ($arr = json_decode($value, true)) && !empty($arr['id'])) {
            if ($file = File::query()->find($arr['id'])) {
                /** @var File $file */
                return $file;
            }
        }

        return null;
    }
}