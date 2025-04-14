<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class TopicTypes extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->string('type', 50)->nullable()->after('teaser');

                $table->index('type');
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->dropColumn('type');
            }
        );
    }
}
