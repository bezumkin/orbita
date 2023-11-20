<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class UserNotifications extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'user_notifications',
            static function (Blueprint $table) {
                $table->uuid('id');
                $table->foreignId('user_id')
                    ->constrained('users')->cascadeOnDelete();
                $table->foreignId('topic_id')
                    ->constrained('topics')->cascadeOnDelete();
                $table->foreignId('comment_id')
                    ->constrained('comments')->cascadeOnDelete();
                $table->string('type', 50);
                $table->boolean('active')->default(true);
                $table->boolean('sent')->default(false);
                $table->timestamps();
                $table->timestamp('sent_at')->nullable();

                $table->index('active');
                $table->index('sent');
            }
        );

        $this->schema->table(
            'users',
            static function (Blueprint $table) {
                $table->char('lang', 2)->nullable()->after('blocked');
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'users',
            static function (Blueprint $table) {
                $table->dropColumn('lang');
            }
        );
        $this->schema->drop('user_notifications');
    }
}
