<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 3);
        $price = fake()->numberBetween(10000, 40000);
        $costPrice = (int) round($price * fake()->randomFloat(2, 0.45, 0.75));

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'name_snapshot' => fake()->words(2, true),
            'price' => $price,
            'cost_price' => $costPrice,
            'qty' => $qty,
            'line_total' => $price * $qty,
            'line_cost_total' => $costPrice * $qty,
            'notes' => null,
        ];
    }
}
