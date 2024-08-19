<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class AudioTracks extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'videos',
            static function (Blueprint $table) {
                $table->foreignId('audio_id')->nullable()->after('image_id')
                    ->constrained('files')->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'videos',
            static function (Blueprint $table) {
                $table->dropConstrainedForeignId('audio_id');
            }
        );
    }
}
