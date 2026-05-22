<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hot_zones', function (Blueprint $table) {
            $table->id();
            $table->decimal('center_latitude', 10, 8);
            $table->decimal('center_longitude', 11, 8);
            $table->integer('radius_meters');
            $table->string('intensity');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at');

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hot_zones');
    }
};
