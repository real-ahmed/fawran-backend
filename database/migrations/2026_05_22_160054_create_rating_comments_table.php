<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_comments', function (Blueprint $table) {
            $table->foreignId('rating_id')->primary()->constrained()->cascadeOnDelete();
            $table->text('comment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_comments');
    }
};
