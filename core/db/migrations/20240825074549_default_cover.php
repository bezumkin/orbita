<?php

declare(strict_types=1);

use App\Models\Setting;
use Vesp\Services\Migration;

final class DefaultCover extends Migration
{
    public function up(): void
    {
        $setting = new Setting();
        $setting->key = 'cover';
        $setting->type = 'image';
        $setting->rank = Setting::query()->count();
        $setting->save();
    }

    public function down(): void
    {
        Setting::query()->where('key', 'cover')->delete();
    }
}
