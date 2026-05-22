<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_documents', function (Blueprint $table) {
            $table->foreignId('courier_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('criminal_record_file');
            $table->string('contract_number')->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_documents');
    }
};
