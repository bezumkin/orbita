<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class NotifyUser extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'users',
            static function (Blueprint $table) {
                $table->boolean('notify')->default(true)->after('blocked');

                $table->index('notify');
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'users',
            static function (Blueprint $table) {
                $table->dropColumn('notify');
            }
        );
    }
}
