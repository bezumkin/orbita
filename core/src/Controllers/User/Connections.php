<?php

namespace App\Controllers\User;

use App\Models\User;
use App\Services\ConnectionService;
use App\Services\Redis;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Vesp\Controllers\Controller;

/**
 * @property User $user
 */
class Connections extends Controller
{
    protected string|array $scope = 'profile';

    protected function getService(string $name): ConnectionService
    {
        $serviceClass = '\App\Services\Connections\\' . $name;
        if (!class_exists($serviceClass)) {
            throw new RuntimeException('errors.connection.no_service');
        }

        return new $serviceClass();
    }

    public function get(): ResponseInterface
    {
        $rows = [];
        $services = array_map('trim', explode(',', getenv('CONNECTION_SERVICES')));
        foreach ($services as $serviceName) {
            $service = $this->getService($serviceName);
            $rows[] = [
                'link' => $service->getConnectionLink($this->user),
                'service' => $serviceName,
                'connected' => $this->user->connections()->where('service', $serviceName)->count() > 0,
            ];
        }

        return $this->success($rows);
    }

    public function delete(): ResponseInterface
    {
        $serviceName = implode('', array_map('ucfirst', explode('-', $this->getProperty('service'))));
        if ($serviceName && $service = $this->getService($serviceName)) {
            if ($service->disconnectUser($this->user)) {
                (new Redis())->send('connections', ['user_id' => $this->user->id]);
            }
        }

        return $this->success();
    }
}