<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Comments extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'comments',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->constrained('users')->cascadeOnDelete();
                $table->foreignId('topic_id')
                    ->constrained('topics')->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()
                    ->constrained('comments')->cascadeOnDelete();
                $table->json('content');
                $table->boolean('active')->default(true);
                $table->timestamps();

                $table->index(['topic_id', 'active']);
            }
        );

        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->foreignId('last_comment_id')->nullable()->after('views_count')
                    ->constrained('comments')->nullOnDelete();
            }
        );

        $this->schema->create(
            'comment_files',
            function (Blueprint $table) {
                $table->foreignId('comment_id')
                    ->constrained('comments')->cascadeOnDelete();
                $table->foreignId('file_id')
                    ->constrained('files')->cascadeOnDelete();
                $table->string('type', 50);

                $table->primary(['comment_id', 'file_id']);
            }
        );
    }

    public function down(): void
    {
        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->dropConstrainedForeignId('last_comment_id');
            }
        );
        $this->schema->drop('comment_files');
        $this->schema->drop('comments');
    }
}
