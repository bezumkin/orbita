<?php

namespace App\Middlewares;

use App\Models\User;
use App\Models\UserToken;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Auth extends \Vesp\Middlewares\Auth
{
    protected string $model = User::class;

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        if ($token = $this->getToken($request)) {
            /** @var UserToken $userToken */
            $userToken = UserToken::query()
                ->where(['token' => $token->token, 'active' => true])
                ->first();
            if ($userToken) {
                $now = Carbon::now();
                if ($userToken->valid_till > $now) {
                    /** @var User $user */
                    if ($user = $userToken->user()->where('active', true)->first()) {
                        $user->active_at = $now;
                        $user->lang = $_COOKIE['i18n_redirected'] ?? 'ru';
                        $user->timestamps = false;
                        $user->save();

                        $request = $request->withAttribute('user', $user);
                        $request = $request->withAttribute('token', $token->token);
                    }
                } else {
                    $userToken->active = false;
                    $userToken->save();
                }
            }
        }

        return $handler->handle($request);
    }
}
