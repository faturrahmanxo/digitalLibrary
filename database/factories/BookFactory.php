<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(1, 10);

        return [
            'category_id' => Category::factory(),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'publisher' => fake()->company(),
            'publication_year' => fake()->numberBetween(2000, 2026),
            'stock_total' => $total,
            'stock_available' => $total,
            'cover_path' => null,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_total' => 5,
            'stock_available' => 0,
        ]);
    }
}
