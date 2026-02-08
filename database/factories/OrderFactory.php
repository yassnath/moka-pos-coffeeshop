<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(20000, 150000);

        return [
            'invoice_no' => 'CS-'.now()->format('Ymd').'-'.str_pad((string) fake()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'status' => 'PAID',
            'subtotal' => $subtotal,
            'discount_type' => 'none',
            'discount_value' => 0,
            'tax' => 0,
            'service' => 0,
            'total' => $subtotal,
            'payment_method' => 'Cash',
            'cash_received' => $subtotal,
            'change' => 0,
            'notes' => null,
            'ordered_at' => now(),
        ];
    }
}
