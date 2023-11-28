<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Subscriptions extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'subscriptions',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->constrained('users')->cascadeOnDelete();
                $table->foreignId('level_id')
                    ->constrained('levels')->cascadeOnDelete();
                $table->foreignId('next_level_id')->nullable()
                    ->constrained('levels')->nullOnDelete();
                $table->string('service');
                $table->unsignedTinyInteger('period')->default(1);
                $table->unsignedTinyInteger('next_period')->nullable();
                $table->boolean('active')->default(false);
                $table->boolean('cancelled')->default(false);
                $table->string('remote_id')->nullable();
                $table->timestamps();
                $table->timestamp('active_until')->nullable();

                $table->index('active');
                $table->index('cancelled');
            }
        );
    }

    public function down(): void
    {
        $this->schema->drop('subscriptions');
    }
}
