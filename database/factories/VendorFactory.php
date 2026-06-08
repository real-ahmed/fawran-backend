<?php

namespace Database\Factories;

use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Models\User;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => json_encode(['en' => fake()->company(), 'ar' => fake()->company()]),
            'type' => fake()->randomElement(VendorType::cases())->value,
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->unique()->numerify('05########'),
            'latitude' => fake()->latitude(24.5, 25.5),
            'longitude' => fake()->longitude(46.5, 47.0),
            'formatted_address' => fake()->address(),
            'is_active' => true,
            'status' => VendorStatus::OFFLINE->value,
        ];
    }

    /**
     * Indicate the vendor is a restaurant.
     */
    public function restaurant(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VendorType::RESTAURANT->value,
        ]);
    }

    /**
     * Indicate the vendor is a grocery store.
     */
    public function grocery(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VendorType::GROCERY->value,
        ]);
    }

    /**
     * Indicate the vendor is a pharmacy.
     */
    public function pharmacy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VendorType::PHARMACY->value,
        ]);
    }

    /**
     * Indicate the vendor is online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VendorStatus::ONLINE->value,
        ]);
    }

    /**
     * Indicate the vendor is blocked (inactive).
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
