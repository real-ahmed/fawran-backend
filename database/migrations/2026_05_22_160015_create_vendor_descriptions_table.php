<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_descriptions', function (Blueprint $table) {
            $table->foreignId('vendor_id')->primary()->constrained()->cascadeOnDelete();
            $table->json('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_descriptions');
    }
};
