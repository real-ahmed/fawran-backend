<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_zone_vehicle_fees', function (Blueprint $table) {
            $table->decimal('intra_zone_flat_fee', 8, 2)->nullable()->after('vehicle_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_zone_vehicle_fees', function (Blueprint $table) {
            $table->dropColumn('intra_zone_flat_fee');
        });
    }
};
