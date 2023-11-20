<?php

use App\Models\UserRole;
use Phinx\Seed\AbstractSeed;

class SeedUserRoles extends AbstractSeed
{
    public function run(): void
    {
        $roles = [
            'Administrator' => [
                'scope' => [
                    'profile',
                    'roles',
                    'users',
                    'videos',
                    'settings',
                    'levels',
                    'topics',
                    'comments',
                    'notifications',
                    'pages',
                ],
            ],
            'Moderator' => [
                'scope' => ['profile', 'users/get', 'comments', 'notifications'],
            ],
            'User' => [
                'scope' => ['profile', 'comments/put', 'comments/patch'],
            ],
        ];

        foreach ($roles as $title => $data) {
            if (!$group = UserRole::query()->where('title', $title)->first()) {
                $group = new UserRole(['title' => $title]);
            }
            $group->fill($data);
            $group->save();
        }
    }
}
