<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_item_id')->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->boolean('is_required')->default(false);
            $table->integer('max_selections')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
