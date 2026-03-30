<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Haircut',
                'Beard Trim',
                'Hot Towel Shave',
                'Hair Coloring',
                'Scalp Treatment',
                'Kids Haircut',
                'Fade',
                'Buzz Cut',
                'Hair Styling',
                'Beard Grooming'
            ]),
            'duration_min' => fake()->randomElement([15, 20, 30, 45, 60, 90]),
            'price' => fake()->randomFloat(2, 10, 150),
            'active' => fake()->boolean(90), // 90% chance of being active
        ];
    }
}
