<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class UserConnections extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'user_connections',
            static function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->constrained('users')->cascadeOnDelete();
                $table->string('service', 100);
                $table->string('remote_id');
                $table->json('data')->nullable();
                $table->timestamps();

                $table->primary(['user_id', 'service']);
            }
        );
    }

    public function down(): void
    {
        $this->schema->drop('user_connections');
    }
}
