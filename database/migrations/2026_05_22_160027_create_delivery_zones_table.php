<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->geometry('polygon');
            $table->boolean('is_active')->default(true);

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
