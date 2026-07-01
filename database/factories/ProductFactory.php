<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => 'SKU-' . fake()->unique()->numerify('#####'),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
