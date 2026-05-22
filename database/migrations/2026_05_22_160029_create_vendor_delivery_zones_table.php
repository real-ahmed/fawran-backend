<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_zone_id')->constrained()->cascadeOnDelete();
            $table->decimal('min_order_amount', 10, 2);
            $table->integer('estimated_delivery_time');

            $table->unique(['vendor_id', 'delivery_zone_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_delivery_zones');
    }
};
