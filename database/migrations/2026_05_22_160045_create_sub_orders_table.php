<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('sub_total', 10, 2);
            $table->string('status');

            $table->index('status');
            $table->unique(['order_id', 'vendor_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_orders');
    }
};
