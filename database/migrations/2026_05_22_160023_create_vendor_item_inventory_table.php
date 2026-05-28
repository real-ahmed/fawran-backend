<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_item_inventory', function (Blueprint $table) {
            $table->foreignId('vendor_item_id')->primary()->constrained()->cascadeOnDelete();
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('low_stock_threshold', 10, 3)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_item_inventory');
    }
};
