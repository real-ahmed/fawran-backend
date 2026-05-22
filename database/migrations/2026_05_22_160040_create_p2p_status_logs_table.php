<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p2p_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p2p_delivery_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p2p_status_logs');
    }
};
