<?php

namespace Database\Factories;

use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publisher>
 */
class PublisherFactory extends Factory
{
    protected $model = Publisher::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'city' => fake()->city(),
            'website' => fake()->optional()->url(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
