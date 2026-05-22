<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_customers', function (Blueprint $table) {
            $table->foreignId('order_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_customers');
    }
};
