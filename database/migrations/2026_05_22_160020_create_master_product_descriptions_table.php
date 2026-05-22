<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_product_descriptions', function (Blueprint $table) {
            $table->foreignId('master_product_id')->primary()->constrained()->cascadeOnDelete();
            $table->json('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_product_descriptions');
    }
};
