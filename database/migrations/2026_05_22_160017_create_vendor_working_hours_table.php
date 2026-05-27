<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week');
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('is_closed')->default(false);

            $table->unique(['vendor_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_working_hours');
    }
};
