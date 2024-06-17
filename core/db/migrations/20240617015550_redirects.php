<?php

declare(strict_types=1);

use App\Models\UserRole;
use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Redirects extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'redirects',
            static function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('from');
                $table->text('to');
                $table->unsignedSmallInteger('code')->nullable();
                $table->unsignedInteger('rank')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();

                $table->index('active');
            }
        );

        /** @var UserRole $admin */
        if ($admin = UserRole::query()->find(1)) {
            $admin->scope = [...$admin->scope, 'redirects'];
            $admin->save();
        }
    }

    public function down(): void
    {
        $this->schema->drop('redirects');

        /** @var UserRole $admin */
        if ($admin = UserRole::query()->find(1)) {
            $admin->scope = array_filter($admin->scope, static function ($val) {
                return $val !== 'redirects';
            });
            $admin->save();
        }
    }
}
