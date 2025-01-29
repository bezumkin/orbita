<?php

namespace App\Controllers\Security;

use App\Models\User;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Activate extends Controller
{
    public function post(): ResponseInterface
    {
        $username = trim($this->getProperty('username', ''));
        $code = trim($this->getProperty('code', ''));

        /** @var User $user */
        if (!$user = User::query()->where('username', $username)->whereNotNull('reset_password')->first()) {
            return $this->failure('errors.activate.no_user', 404);
        }

        $now = Carbon::now();
        $linkTTL = (int)getenv('REGISTER_LINK_TTL') ?: 60;
        if ($user->reset_at && $user->reset_at->addMinutes($linkTTL) < $now) {
            return $this->failure('errors.activate.ttl');
        }
        if (!$user->activatePassword($code)) {
            return $this->failure('errors.activate.wrong');
        }

        $token = $user->createToken($this->request->getAttribute('ip_address'));

        return $this->success(['token' => $token->token]);
    }
}