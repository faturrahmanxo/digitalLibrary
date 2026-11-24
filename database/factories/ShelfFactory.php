<?php

namespace Database\Factories;

use App\Models\Shelf;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shelf>
 */
class ShelfFactory extends Factory
{
    protected $model = Shelf::class;

    public function definition(): array
    {
        return [
            'code' => 'SHF-'.strtoupper(fake()->unique()->bothify('??-###')),
            'name' => 'Rak '.fake()->words(2, true),
            'location' => 'Lantai '.fake()->numberBetween(1, 3).' Sayap '.fake()->randomElement(['Utara', 'Selatan', 'Timur', 'Barat']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
