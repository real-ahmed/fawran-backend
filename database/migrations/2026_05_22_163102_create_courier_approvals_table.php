<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_approvals', function (Blueprint $table) {
            $table->foreignId('courier_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained()->restrictOnDelete();
            $table->timestamp('approved_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_approvals');
    }
};
