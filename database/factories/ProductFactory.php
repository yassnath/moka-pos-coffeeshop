<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $trackStock = fake()->boolean(30);
        $price = fake()->numberBetween(12000, 45000);
        $costPrice = (int) round($price * fake()->randomFloat(2, 0.45, 0.75));

        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(2, true),
            'sku' => strtoupper(fake()->unique()->bothify('PRD-###??')),
            'price' => $price,
            'cost_price' => $costPrice,
            'is_active' => true,
            'track_stock' => $trackStock,
            'stock_qty' => $trackStock ? fake()->numberBetween(1, 50) : 0,
            'image_path' => null,
        ];
    }
}
