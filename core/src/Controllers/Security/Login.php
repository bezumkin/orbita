<?php

namespace App\Controllers\Security;

use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Login extends Controller
{
    public function post(): ResponseInterface
    {
        $username = trim($this->getProperty('username', ''));
        $password = trim($this->getProperty('password', ''));

        /** @var User $user */
        $user = User::query()->where('username', $username)->orWhere('email', $username)->first();
        if (!$user || !$user->verifyPassword($password)) {
            return $this->failure('errors.login.wrong');
        }
        if (!$user->active) {
            return $this->failure('errors.user.inactive');
        }
        if ($user->blocked) {
            return $this->failure('errors.user.blocked');
        }

        $token = $user->createToken($this->request->getAttribute('ip_address'));

        return $this->success(['token' => $token->token]);
    }

}
