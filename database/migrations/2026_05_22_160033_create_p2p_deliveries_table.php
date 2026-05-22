<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delivery_zone_id')->constrained()->cascadeOnDelete();
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->string('pickup_address');
            $table->decimal('dropoff_latitude', 10, 8);
            $table->decimal('dropoff_longitude', 11, 8);
            $table->string('dropoff_address');
            $table->string('recipient_name');
            $table->string('recipient_phone', 20);
            $table->decimal('distance_km', 8, 3);
            $table->decimal('delivery_fee', 8, 2);
            $table->string('status');
            $table->timestamp('created_at')->useCurrent();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_deliveries');
    }
};
