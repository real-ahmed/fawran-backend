<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorStaff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorStaff>
 */
class VendorStaffFactory extends Factory
{
    protected $model = VendorStaff::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'vendor_id' => Vendor::factory(),
        ];
    }
}
