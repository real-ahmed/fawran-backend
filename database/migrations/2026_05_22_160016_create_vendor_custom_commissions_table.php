<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_custom_commissions', function (Blueprint $table) {
            $table->foreignId('vendor_id')->primary()->constrained()->cascadeOnDelete();
            $table->decimal('commission_percentage', 5, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_custom_commissions');
    }
};
