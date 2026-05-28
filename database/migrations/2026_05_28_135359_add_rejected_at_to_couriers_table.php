<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->timestamp('rejected_at')->nullable()->after('is_online');
            $table->index('rejected_at', 'couriers_rejected_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->dropIndex('couriers_rejected_at_idx');
            $table->dropColumn('rejected_at');
        });
    }
};
