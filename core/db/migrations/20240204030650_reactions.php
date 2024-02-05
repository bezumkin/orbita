<?php

declare(strict_types=1);

use App\Models\UserRole;
use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

require_once __DIR__ . '/../seeds/SeedReactions.php';

final class Reactions extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'reactions',
            static function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('emoji');
                $table->unsignedInteger('rank')->nullable();
                $table->boolean('active')->default(true);

                $table->unique('title');
                $table->index('active');
            }
        );

        $this->schema->create(
            'topic_reactions',
            static function (Blueprint $table) {
                $table->foreignId('topic_id')
                    ->constrained('topics')->cascadeOnDelete();
                $table->foreignId('reaction_id')
                    ->constrained('reactions')->cascadeOnDelete();
                $table->foreignId('user_id')
                    ->constrained('users')->cascadeOnDelete();
                $table->timestamp('timestamp')->useCurrent();

                $table->primary(['topic_id', 'user_id']);
            }
        );

        $this->schema->create(
            'comment_reactions',
            static function (Blueprint $table) {
                $table->foreignId('comment_id')
                    ->constrained('comments')->cascadeOnDelete();
                $table->foreignId('reaction_id')
                    ->constrained('reactions')->cascadeOnDelete();
                $table->foreignId('user_id')
                    ->constrained('users')->cascadeOnDelete();
                $table->timestamp('timestamp')->useCurrent();

                $table->primary(['comment_id', 'user_id']);
            }
        );

        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->unsignedInteger('reactions_count')->default(0)->after('views_count');
            }
        );

        $this->schema->table(
            'comments',
            static function (Blueprint $table) {
                $table->unsignedInteger('reactions_count')->default(0)->after('active');
            }
        );

        /** @var UserRole $admin */
        if ($admin = UserRole::query()->find(1)) {
            $admin->scope = [...$admin->scope, 'reactions'];
            $admin->save();
        }

        if ($adapter = $this->getAdapter()) {
            $input = $this->getInput();
            $output = $this->getOutput();
            (new SeedReactions())->setAdapter($adapter)->setInput($input)->setOutput($output)->run();
        }
    }

    public function down(): void
    {
        $this->schema->table(
            'comments',
            static function (Blueprint $table) {
                $table->dropColumn('reactions_count');
            }
        );

        $this->schema->table(
            'topics',
            static function (Blueprint $table) {
                $table->dropColumn('reactions_count');
            }
        );

        $this->schema->drop('topic_reactions');
        $this->schema->drop('comment_reactions');
        $this->schema->drop('reactions');

        /** @var UserRole $admin */
        if ($admin = UserRole::query()->find(1)) {
            $admin->scope = array_filter($admin->scope, static function ($val) {
                return $val !== 'reactions';
            });
            $admin->save();
        }
    }
}
