<?php

use App\Enums\AdminPermission;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $guardName = 'api_admin';

        Permission::firstOrCreate(['name' => AdminPermission::ASSIGN_COURIER_TO_ORDER->value, 'guard_name' => $guardName]);
        Permission::firstOrCreate(['name' => AdminPermission::UPDATE_ORDER_STATUS->value, 'guard_name' => $guardName]);
    }

    public function down(): void
    {
        $guardName = 'api_admin';
        Permission::where('guard_name', $guardName)
            ->whereIn('name', [
                AdminPermission::ASSIGN_COURIER_TO_ORDER->value,
                AdminPermission::UPDATE_ORDER_STATUS->value,
            ])->delete();
    }
};
