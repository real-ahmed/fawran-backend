<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('vendorcommission_percentage', 5, 2);
            $table->decimal('vendorcommission_amount', 10, 2);
            $table->decimal('app_delivery_share', 10, 2);
            $table->decimal('net_platform_profit', 10, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['order_id', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_commissions');
    }
};
