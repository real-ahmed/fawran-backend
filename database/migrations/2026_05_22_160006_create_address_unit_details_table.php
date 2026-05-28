<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('address_unit_details', function (Blueprint $table) {
            $table->foreignId('address_id')->primary()->constrained('user_addresses')->cascadeOnDelete();
            $table->string('floor_number', 50);
            $table->string('apartment_number', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_unit_details');
    }
};
