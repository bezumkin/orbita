<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class DelayedPublishing extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->timestamp('publish_at')->nullable()->after('published_at');

                $table->index(['publish_at', 'active']);
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->dropIndex(['publish_at', 'active']);

                $table->dropColumn('publish_at');
            }
        );
    }
}
