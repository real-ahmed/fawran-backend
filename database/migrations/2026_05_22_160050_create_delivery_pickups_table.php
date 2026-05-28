<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_pickups', function (Blueprint $table) {
            $table->foreignId('delivery_id')->primary()->constrained()->cascadeOnDelete();
            $table->timestamp('picked_up_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_pickups');
    }
};
