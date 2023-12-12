<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Payments extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'payments',
            static function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')
                    ->constrained('users')->cascadeOnDelete();
                $table->foreignId('subscription_id')->nullable()
                    ->constrained('subscriptions')->nullOnDelete();
                $table->foreignId('topic_id')->nullable()
                    ->constrained('topics')->nullOnDelete();
                $table->string('service');
                $table->unsignedDecimal('amount');
                $table->boolean('paid')->nullable();
                $table->string('link')->nullable();
                $table->string('remote_id')->nullable();
                $table->timestamps();
                $table->timestamp('paid_at')->nullable();
                $table->json('metadata')->nullable();

                $table->index('paid');
                $table->index('remote_id');
            }
        );
    }

    public function down(): void
    {
        $this->schema->drop('payments');
    }
}
