<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class UserColors extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'user_roles',
            static function (Blueprint $table) {
                $table->string('color', 10)->nullable()->after('scope');
            }
        );
        $this->schema->table(
            'levels',
            static function (Blueprint $table) {
                $table->string('color', 10)->nullable()->after('cover_id');
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'user_roles',
            static function (Blueprint $table) {
                $table->dropColumn('color');
            }
        );
        $this->schema->table(
            'levels',
            static function (Blueprint $table) {
                $table->dropColumn('color');
            }
        );
    }
}
