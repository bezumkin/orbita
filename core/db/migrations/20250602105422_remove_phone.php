<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class RemovePhone extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'users',
            static function (Blueprint $table) {
                $table->dropColumn('phone');
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'users',
            static function (Blueprint $table) {
                $table->string('phone', 20)->nullable()
                    ->after('email');
            }
        );
    }
}
