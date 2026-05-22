<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_dropoffs', function (Blueprint $table) {
            $table->foreignId('p2p_delivery_id')->primary()->constrained()->cascadeOnDelete();
            $table->timestamp('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_dropoffs');
    }
};
