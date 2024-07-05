<?php

declare(strict_types=1);

use App\Models\Page;
use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class ExternalPages extends Migration
{
    public function up(): void
    {
        $this->schema->table(
            'pages',
            static function (Blueprint $table) {
                $table->string('title')->nullable()->change();
                $table->json('content')->nullable()->change();
                $table->string('alias')->nullable()->change();

                $table->string('name')->nullable()->after('id');
                $table->boolean('external')->default(false)->after('title');
                $table->string('link')->nullable()->after('position');
                $table->boolean('blank')->default(true)->after('link');
            }
        );

        /** @var \App\Models\Page $page */
        foreach (Page::query()->cursor() as $page) {
            $page->name = $page->title;
            $page->save();
        }

        $this->schema->table(
            'pages',
            static function (Blueprint $table) {
                $table->string('name')->nullable(false)->change();
            }
        );
    }

    public function down(): void
    {
        /** @var Page $page */
        foreach (Page::query()->cursor() as $page) {
            if (!$page->title) {
                $page->title = $page->name;
            }
            if (!$page->content) {
                $page->content = [];
            }
            if (!$page->alias) {
                $page->alias = '';
            }
            $page->save();
        }

        $this->schema->table(
            'pages',
            static function (Blueprint $table) {
                $table->string('title')->nullable(false)->change();
                $table->json('content')->nullable(false)->change();
                $table->string('alias')->nullable(false)->change();

                $table->dropColumn('name', 'external', 'link', 'blank');
            }
        );
    }
}
