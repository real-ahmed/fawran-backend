<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->json('display_name')->nullable()->after('name');
        });

        // Seed the primary Super Admin role with bilingual display name
        DB::table('roles')->where('name', 'Super Admin')->update([
            'display_name' => json_encode([
                'en' => 'Super Admin',
                'ar' => 'المشرف العام',
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });
    }
};
