<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_zone_id')->constrained()->cascadeOnDelete();

            $table->unique(['admin_id', 'delivery_zone_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_delivery_zones');
    }
};
