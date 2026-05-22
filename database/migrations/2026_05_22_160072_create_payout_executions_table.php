<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_executions', function (Blueprint $table) {
            $table->foreignId('payout_request_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('executed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_executions');
    }
};
