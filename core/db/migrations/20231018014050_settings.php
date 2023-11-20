<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Schema\Blueprint;
use Vesp\Services\Migration;

final class Settings extends Migration
{
    public function up(): void
    {
        $this->schema->create(
            'settings',
            static function (Blueprint $table) {
                $table->string('key')->primary();
                $table->text('value')->nullable();
                $table->string('type')->default('string');
                $table->boolean('required')->default(false);
                $table->unsignedSmallInteger('rank')->default(0);
                $table->timestamps();
            }
        );

        $settings = [
            'title' => ['type' => 'string', 'value' => ['ru' => 'Орбита', 'en' => 'Orbita']],
            'description' => ['type' => 'text', 'value' => ['ru' => 'Описание проекта', 'en' => 'Project description']],
            'poster' => ['type' => 'image'],
            'background' => ['type' => 'image'],
            'copyright' => ['type' => 'string', 'value' =>  ['ru' => 'Василий Наумкин', 'en' => 'Vasily Naumkin']],
            'started' => ['type' => 'date', 'value' => date('Y-m-d')],
        ];

        $idx = 0;
        foreach ($settings as $key => $data) {
            $setting = new Setting();
            $setting->key = $key;
            $setting->rank = $idx++;
            if (isset($data['value']) && is_array($data['value'])) {
                $data['value'] = json_encode($data['value']);
            }
            $setting->fill($data);
            $setting->save();
        }
    }

    public function down(): void
    {
        $this->schema->drop('settings');
    }
}
