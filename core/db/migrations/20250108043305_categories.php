<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Categories extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'categories',
            static function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('uri');
                $table->boolean('active')->default(true);
                $table->unsignedInteger('rank')->default(0);
                $table->timestamps();

                $table->unique('title');
                $table->unique('uri');
                $table->index(['uri', 'active']);
                $table->index('rank');
            }
        );

        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->foreignId('category_id')->after('uuid')
                    ->nullable()
                    ->constrained('categories')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->dropConstrainedForeignId('category_id');
            }
        );
        $this->schema->drop('categories');
    }
}
