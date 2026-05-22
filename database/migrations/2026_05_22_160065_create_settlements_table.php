<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_type');
            $table->unsignedBigInteger('target_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_gross', 10, 2);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('total_net_exchange', 10, 2);
            $table->string('status');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['settlement_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
