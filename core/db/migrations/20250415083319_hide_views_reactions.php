<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class HideViewsReactions extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->boolean('hide_comments')->default(false)->after('closed');
                $table->boolean('hide_views')->default(false)->after('hide_comments');
                $table->boolean('hide_reactions')->default(false)->after('hide_views');
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->dropColumn('hide_comments', 'hide_views', 'hide_reactions');
            }
        );
    }
}
