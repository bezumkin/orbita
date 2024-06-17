<?php

namespace App\Controllers\Web;

use App\Models\Redirect;
use FastRoute\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Redirects extends Controller
{
    public function get(): ResponseInterface
    {
        $uri = '/' . trim($this->getProperty('uri'), '/');
        $res = Redirect::getDispatcher()
            ->dispatch('GET', $uri);

        if ($res[0] === Dispatcher::FOUND && is_callable($res[1]) && $redirect = $res[1]($res[2])) {
            return $this->success($redirect);
        }

        return $this->failure('Not Found', 404);
    }
}