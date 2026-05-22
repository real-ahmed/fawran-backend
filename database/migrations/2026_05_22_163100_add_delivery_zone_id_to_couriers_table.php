<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->foreignId('delivery_zone_id')->after('user_id')->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->dropForeign(['delivery_zone_id']);
            $table->dropColumn('delivery_zone_id');
        });
    }
};
