<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('system_settings')->insertOrIgnore([
            'key'   => 'currency',
            'value' => 'EGP',
            'group' => 'general',
        ]);
    }

    public function down(): void
    {
        DB::table('system_settings')->where('key', 'currency')->delete();
    }
};
