<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class SubscriptionWarning extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'subscriptions',
            static function (Blueprint $table) {
                $table->timestamp('warned_at')->nullable()->after('updated_at');

                $table->index('warned_at');
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'subscriptions',
            static function (Blueprint $table) {
                $table->dropColumn('warned_at');
            }
        );
    }
}
