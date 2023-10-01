<?php

use App\Models\User;
use Phinx\Seed\AbstractSeed;

class SeedUsers extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['SeedUserRoles'];
    }

    public function run(): void
    {
        $users = [
            'admin' => [
                'password' => 'admin',
                'fullname' => 'Admin',
                'role_id' => 1,
            ],
            'moderator' => [
                'password' => 'moderator',
                'fullname' => 'Moderator',
                'role_id' => 2,
            ],
            'user' => [
                'password' => 'user',
                'fullname' => 'User',
                'role_id' => 3,
            ],
        ];

        foreach ($users as $username => $data) {
            if (!$user = User::query()->where('username', $username)->first()) {
                $user = new User(['username' => $username]);
            }
            $user->fill($data);
            $user->save();
        }
    }
}
