<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_cash_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained()->cascadeOnDelete();
            $table->morphs('source');
            $table->decimal('amount_collected', 10, 2);
            $table->decimal('courier_fee_share', 10, 2);
            $table->decimal('amount_owed_to_platform', 10, 2);
            $table->boolean('is_settled')->default(false);
            $table->timestamp('collected_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_cash_collections');
    }
};
