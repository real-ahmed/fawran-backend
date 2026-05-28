<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_dish_details', function (Blueprint $table) {
            $table->foreignId('vendor_item_id')->primary()->constrained()->cascadeOnDelete();
            $table->integer('preparation_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_dish_details');
    }
};
