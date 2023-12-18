<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class TopicTags extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'tags',
            static function (Blueprint $table) {
                $table->id();
                $table->string('title');

                $table->unique('title');
            }
        );

        $this->schema->create(
            'topic_tags',
            static function (Blueprint $table) {
                $table->foreignId('topic_id')
                    ->constrained('topics')->cascadeOnDelete();
                $table->foreignId('tag_id')
                    ->constrained('tags')->cascadeOnDelete();

                $table->primary(['topic_id', 'tag_id']);
            }
        );
    }

    public function down(): void
    {
        $this->schema->drop('topic_tags');
        $this->schema->drop('tags');
    }
}
