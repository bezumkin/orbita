<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Videos extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'videos',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('file_id')
                    ->constrained('files')->cascadeOnDelete();
                $table->foreignId('image_id')->nullable()
                    ->constrained('files')->nullOnDelete();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->unsignedSmallInteger('duration')->nullable();
                $table->boolean('active')->default(true);
                $table->boolean('processed')->nullable();
                $table->boolean('moved')->default(false);
                $table->unsignedDecimal('progress', 5)->nullable();
                $table->text('manifest')->nullable();
                $table->json('chapters')->nullable();
                $table->text('error')->nullable();
                $table->timestamps();
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('moved_at')->nullable();
            }
        );

        $this->schema->create(
            'video_qualities',
            function (Blueprint $table) {
                $table->unsignedSmallInteger('quality');
                $table->foreignUuid('video_id')
                    ->constrained('videos')->cascadeOnDelete();
                $table->foreignId('file_id')
                    ->constrained('files')->cascadeOnDelete();
                $table->unsignedDecimal('progress', 5)->nullable();
                $table->boolean('processed')->default(false);
                $table->boolean('moved')->default(false);
                $table->string('bandwidth', 10)->nullable();
                $table->string('resolution', 10)->nullable();
                $table->mediumText('manifest')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('moved_at')->nullable();

                $table->primary(['quality', 'video_id']);
            }
        );

        $this->schema->create(
            'video_users',
            function (Blueprint $table) {
                $table->uuid('video_id');
                $table->foreignId('user_id')
                    ->constrained('users')->cascadeOnDelete();
                $table->unsignedSmallInteger('quality');
                $table->unsignedSmallInteger('time')->default(0);
                $table->unsignedDecimal('speed', 3)->default(1);
                $table->unsignedDecimal('volume', 3)->default(1);
                $table->timestamps();

                $table->primary(['video_id', 'user_id']);
                $table->foreign(['video_id', 'quality'])
                    ->references(['video_id', 'quality'])
                    ->on('video_qualities')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema->drop('video_users');
        $this->schema->drop('video_qualities');
        $this->schema->drop('videos');
    }
}
