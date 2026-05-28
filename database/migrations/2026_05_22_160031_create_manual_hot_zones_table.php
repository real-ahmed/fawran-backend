<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_hot_zones', function (Blueprint $table) {
            $table->foreignId('hot_zone_id')->primary()->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_hot_zones');
    }
};
