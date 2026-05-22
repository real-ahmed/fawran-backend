<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->foreignId('order_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignId('address_id')->constrained('user_addresses')->cascadeOnDelete();
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total_delivery_fee', 8, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');
    }
};
