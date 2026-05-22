<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->time('opening_time');
            $table->time('closing_time');
            $table->boolean('is_closed')->default(false);

            $table->unique(['store_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_working_hours');
    }
};
