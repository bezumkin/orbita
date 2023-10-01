<?php

namespace App\Controllers\Security;

use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Reset extends Controller
{
    public function post(): ResponseInterface
    {
        $username = trim($this->getProperty('username', ''));

        /** @var User $user */
        $user = User::query()->where('username', $username)->first();
        if (!$user && strpos($username, '@')) {
            $user = User::query()->where('email', $username)->first();
        }

        if ($user) {
            if ($user->blocked) {
                return $this->failure('errors.user.blocked');
            }
            if (!$user->email) {
                return $this->failure('errors.user.no_email');
            }

            $lang = $this->request->getHeaderLine('Content-Language') ?: 'ru';
            $subject = getenv('EMAIL_RESET_' . strtoupper($lang));
            $data = ['user' => $user->toArray(), 'code' => $user->resetPassword(10), 'lang' => $lang];
            if ($error = $user->sendEmail($subject, 'email-reset', $data)) {
                return $this->failure($error);
            }
        }

        return $this->success();
    }
}