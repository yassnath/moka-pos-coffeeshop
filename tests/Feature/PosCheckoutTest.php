<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_checkout_sukses_membuat_order_dan_item(): void
    {
        $kasir = User::factory()->create([
            'role' => User::ROLE_KASIR,
        ]);

        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 25000,
            'cost_price' => 14000,
            'is_active' => true,
            'track_stock' => true,
            'stock_qty' => 7,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => null,
            'price_delta' => 3000,
            'is_active' => true,
        ]);

        $addon = Addon::factory()->create([
            'price' => 4000,
            'is_active' => true,
        ]);

        $paymentMethod = PaymentMethod::factory()->create([
            'name' => 'Cash',
            'code' => 'cash',
            'is_active' => true,
        ]);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'qty' => 2,
                    'addons' => [$addon->id],
                    'notes' => 'less sugar',
                ],
            ],
            'discount_type' => 'amount',
            'discount_value' => 2000,
            'tax_percent' => 12,
            'service' => 500,
            'payment_method_id' => $paymentMethod->id,
            'cash_received' => 80000,
            'notes' => 'take away',
        ];

        $response = $this->actingAs($kasir)->postJson(route('pos.checkout'), $payload);

        $response->assertOk();
        $response->assertJsonStructure(['redirect']);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('order_item_addons', 1);

        $order = Order::query()->with('items.addons')->firstOrFail();

        $this->assertSame($kasir->id, $order->user_id);
        $this->assertMatchesRegularExpression('/^CS-\d{8}-\d{4}$/', $order->invoice_no);
        $this->assertSame('PAID', $order->status);
        $this->assertEqualsWithDelta(69940, (float) $order->total, 0.01);
        $this->assertEqualsWithDelta(7440, (float) $order->tax, 0.01);
        $this->assertEqualsWithDelta(2000, (float) $order->discount_value, 0.01);

        $item = $order->items->first();
        $this->assertNotNull($item);
        $this->assertSame(2, $item->qty);
        $this->assertEqualsWithDelta(28000, (float) $item->price, 0.01);
        $this->assertEqualsWithDelta(14000, (float) $item->cost_price, 0.01);
        $this->assertEqualsWithDelta(64000, (float) $item->line_total, 0.01);
        $this->assertEqualsWithDelta(28000, (float) $item->line_cost_total, 0.01);

        $this->assertCount(1, $item->addons);
        $this->assertSame($addon->id, $item->addons->first()->addon_id);

        $product->refresh();
        $this->assertSame(5, $product->stock_qty);
    }
}
