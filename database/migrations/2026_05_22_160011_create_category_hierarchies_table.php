<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_hierarchies', function (Blueprint $table) {
            $table->foreignId('child_category_id')->primary()->constrained('categories')->cascadeOnDelete();
            $table->foreignId('parent_category_id')->constrained('categories')->cascadeOnDelete();

            $table->index('parent_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_hierarchies');
    }
};
