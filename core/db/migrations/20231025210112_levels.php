<?php
declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Levels extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'levels',
            function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content')->nullable();
                $table->unsignedDecimal('price');
                $table->foreignId('cover_id')->nullable()
                    ->constrained('files')->nullOnDelete();
                $table->boolean('active')->default(true);
                $table->timestamps();

                $table->unique('title');
                $table->unique('price');
            }
        );
    }

    public function down(): void
    {
        $this->schema->drop('levels');
    }
}
