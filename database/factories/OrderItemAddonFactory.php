<?php

namespace Database\Factories;

use App\Models\Addon;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItemAddon>
 */
class OrderItemAddonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'addon_id' => Addon::factory(),
            'name_snapshot' => fake()->word(),
            'price' => fake()->numberBetween(1000, 7000),
        ];
    }
}
