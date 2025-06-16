<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class UserReadOnly extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'users',
            static function (Blueprint $table) {
                $table->boolean('readonly')->after('lang')->default(false);
                $table->timestamp('readonly_until')->after('readonly')->nullable();
                $table->string('readonly_reason')->after('readonly_until')->nullable();

                $table->index(['readonly', 'readonly_until']);
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'users',
            static function (Blueprint $table) {
                $table->dropColumn('readonly', 'readonly_until', 'readonly_reason');
            }
        );
    }
}
