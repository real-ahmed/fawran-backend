<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendordescriptions', function (Blueprint $table) {
            $table->foreignId('vendor_id')->primary()->constrained()->cascadeOnDelete();
            $table->json('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendordescriptions');
    }
};
