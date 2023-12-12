<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Topics extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'topics',
            static function (Blueprint $table) {
                $table->id();
                $table->uuid();
                $table->string('title');
                $table->json('content');
                $table->text('teaser')->nullable();
                $table->foreignId('user_id')
                    ->constrained('users')->onDelete('cascade');
                $table->foreignId('cover_id')->nullable()
                    ->constrained('files')->nullOnDelete();
                $table->foreignId('level_id')->nullable()
                    ->constrained('levels')->nullOnDelete();
                $table->unsignedDecimal('price')->nullable();
                $table->boolean('active')->default(true);
                $table->boolean('closed')->default(false);
                $table->unsignedInteger('comments_count')->default(0);
                $table->unsignedInteger('views_count')->default(0);
                $table->timestamps();
                $table->timestamp('published_at')->nullable();

                $table->unique('uuid');
                $table->index('comments_count');
                $table->index('views_count');
                $table->index(['published_at', 'active']);
            }
        );

        $this->schema->create(
            'topic_files',
            static function (Blueprint $table) {
                $table->foreignId('topic_id')
                    ->constrained('topics')->onDelete('cascade');
                $table->foreignId('file_id')
                    ->constrained('files')->onDelete('cascade');
                $table->string('type', 50);

                $table->primary(['topic_id', 'file_id']);
            }
        );

        $this->schema->create(
            'topic_views',
            static function (Blueprint $table) {
                $table->foreignId('topic_id')
                    ->constrained('topics')->onDelete('cascade');
                $table->foreignId('user_id')
                    ->constrained('users')->onDelete('cascade');
                $table->timestamp('timestamp');

                $table->primary(['topic_id', 'user_id']);
            }
        );
    }

    public function down(): void
    {
        $this->schema->drop('topic_files');
        $this->schema->drop('topic_views');
        $this->schema->drop('topics');
    }
}
