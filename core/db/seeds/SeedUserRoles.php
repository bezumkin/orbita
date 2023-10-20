<?php

use App\Models\UserRole;
use Phinx\Seed\AbstractSeed;

class SeedUserRoles extends AbstractSeed
{
    public function run(): void
    {
        $roles = [
            'Administrator' => [
                'scope' => ['profile', 'users', 'videos', 'settings'],
            ],
            'Moderator' => [
                'scope' => ['profile', 'users/get'],
            ],
            'User' => [
                'scope' => ['profile'],
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
