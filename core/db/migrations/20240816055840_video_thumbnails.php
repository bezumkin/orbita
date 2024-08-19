<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class VideoThumbnails extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'videos',
            static function (Blueprint $table) {
                $table->foreignId('thumbnail_id')->nullable()->after('audio_id')
                    ->constrained('files')->nullOnDelete();
                $table->json('thumbnails')->nullable()->after('chapters');
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'videos',
            static function (Blueprint $table) {
                $table->dropConstrainedForeignId('thumbnail_id');
                $table->dropColumn('thumbnails');
            }
        );
    }
}
