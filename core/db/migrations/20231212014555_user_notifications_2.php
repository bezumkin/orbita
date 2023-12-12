<?php

declare(strict_types=1);

use App\Models\UserNotification;
use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class UserNotifications2 extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'user_notifications',
            static function (Blueprint $table) {
                $table->dropForeign(['comment_id']);

                $table->foreignId('comment_id')->nullable()->change()
                    ->constrained('comments')->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        UserNotification::query()->whereNull('comment_id')->delete();

        $this->schema->table(
            'user_notifications',
            static function (Blueprint $table) {
                $table->dropForeign(['comment_id']);
            }
        );

        $this->schema->table(
            'user_notifications',
            static function (Blueprint $table) {
                $table->foreignId('comment_id')->nullable(false)->change()
                    ->constrained('comments')->cascadeOnDelete();
            }
        );
    }
}
