<?php

namespace Database\Factories;

use App\Models\Barber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barber>
 */
class BarberFactory extends Factory
{
    protected $model = Barber::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null, // Can be set manually or via relationship
            'name' => fake()->name(),
            'phone' => fake()->optional(0.8)->phoneNumber(),
            'bio' => fake()->optional(0.7)->paragraph(),
            'active' => fake()->boolean(85), // 85% chance of being active
        ];
    }
}
