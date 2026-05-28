<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zone_vehicle_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_zone_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_type');
            $table->decimal('base_delivery_fee', 8, 2);
            $table->decimal('fee_per_km', 6, 2);
            $table->decimal('max_delivery_fee', 8, 2);

            $table->unique(['delivery_zone_id', 'vehicle_type']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zone_vehicle_fees');
    }
};
