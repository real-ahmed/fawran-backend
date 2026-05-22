<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->json('name');
            $table->string('unit_type');
            $table->boolean('is_active')->default(true);

            $table->index('category_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_products');
    }
};
