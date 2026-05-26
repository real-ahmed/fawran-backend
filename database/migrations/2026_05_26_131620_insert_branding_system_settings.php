<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('system_settings')->insert([
            [
                'key' => 'app_icon',
                'value' => '',
                'group' => 'branding',
            ],
            [
                'key' => 'favicon',
                'value' => '',
                'group' => 'branding',
            ],
            [
                'key' => 'app_name',
                'value' => '{"ar": "فورا", "en": "Fawran"}',
                'group' => 'branding',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', ['app_icon', 'favicon', 'app_name'])->delete();
    }
};
