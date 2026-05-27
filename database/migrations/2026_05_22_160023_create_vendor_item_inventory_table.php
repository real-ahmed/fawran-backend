<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendoritem_inventory', function (Blueprint $table) {
            $table->foreignId('vendoritem_id')->primary()->constrained()->cascadeOnDelete();
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('low_stock_threshold', 10, 3)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendoritem_inventory');
    }
};
