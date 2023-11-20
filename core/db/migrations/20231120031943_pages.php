<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Pages extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'pages',
            static function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->json('content');
                $table->string('alias');
                $table->string('position', 10)->nullable();
                $table->unsignedTinyInteger('rank')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();

                $table->index(['rank', 'active']);
                $table->unique('alias');
            }
        );

        $this->schema->create(
            'page_files',
            static function (Blueprint $table) {
                $table->foreignId('page_id')
                    ->constrained('pages')->onDelete('cascade');
                $table->foreignId('file_id')
                    ->constrained('files')->onDelete('cascade');
                $table->string('type', 50);

                $table->primary(['page_id', 'file_id']);
            }
        );
    }

    public function down(): void
    {
        $this->schema->drop('page_files');
        $this->schema->drop('pages');
    }
}
