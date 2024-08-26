<?php

namespace App\Controllers\Security;

use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Vesp\Controllers\Controller;

class Register extends Controller
{
    public function post(): ResponseInterface
    {
        $data = array_filter($this->getProperties(), static function($key) {
            return in_array($key, ['username', 'fullname', 'password', 'email', 'phone']);
        }, ARRAY_FILTER_USE_KEY);

        try {
            $user = User::createUser($data);
        } catch (Throwable $e) {
            return $this->failure($e->getMessage());
        }

        $lang = $this->request->getHeaderLine('Content-Language') ?: 'en';
        $subject = getenv('EMAIL_REGISTER_' . strtoupper($lang));
        $data = ['user' => $user->toArray(), 'code' => $user->resetPassword(), 'lang' => $lang];
        if ($error = $user->sendEmail($subject, 'user-register', $data)) {
            return $this->failure($error);
        }

        return $this->success();
    }
}