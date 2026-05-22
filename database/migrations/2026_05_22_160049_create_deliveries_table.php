<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->constrained()->cascadeOnDelete();
            $table->decimal('fee_share', 8, 2);
            $table->string('status');
            $table->timestamp('created_at')->useCurrent();

            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
