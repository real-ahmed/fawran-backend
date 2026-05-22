<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->foreignId('payment_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
