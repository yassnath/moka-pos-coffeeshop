<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenBillFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_bisa_simpan_open_bill_dan_kasir_lain_tidak_bisa_mengambilnya(): void
    {
        $kasirA = User::factory()->create([
            'role' => User::ROLE_KASIR,
        ]);
        $kasirB = User::factory()->create([
            'role' => User::ROLE_KASIR,
        ]);

        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 20000,
            'cost_price' => 9000,
            'is_active' => true,
            'track_stock' => true,
            'stock_qty' => 10,
        ]);

        $openBillPayload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                ],
            ],
            'discount_type' => 'none',
            'discount_value' => 0,
            'service' => 0,
            'notes' => 'open bill test',
        ];

        $saveResponse = $this->actingAs($kasirA)->postJson(route('pos.open-bill.save'), $openBillPayload);
        $saveResponse->assertOk()->assertJsonStructure(['open_bill_id']);

        $openBillId = (int) $saveResponse->json('open_bill_id');
        $order = Order::query()->with('items')->findOrFail($openBillId);

        $this->assertSame('OPEN_BILL', $order->status);
        $this->assertSame($kasirA->id, $order->user_id);
        $this->assertSame('OPEN BILL', $order->payment_method);

        $this->assertCount(1, $order->items);
        $this->assertEqualsWithDelta(9000, (float) $order->items->first()->cost_price, 0.01);
        $this->assertEqualsWithDelta(18000, (float) $order->items->first()->line_cost_total, 0.01);

        $product->refresh();
        $this->assertSame(8, $product->stock_qty);

        $cashMethod = PaymentMethod::factory()->create([
            'name' => 'Cash',
            'code' => 'cash',
            'is_active' => true,
        ]);

        $checkoutPayload = [
            'open_bill_id' => $openBillId,
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                ],
            ],
            'discount_type' => 'none',
            'discount_value' => 0,
            'service' => 0,
            'payment_method_id' => $cashMethod->id,
            'cash_received' => 50000,
        ];

        $forbiddenResponse = $this->actingAs($kasirB)->postJson(route('pos.checkout'), $checkoutPayload);
        $forbiddenResponse->assertStatus(422)->assertJsonValidationErrors(['open_bill_id']);

        $order->refresh();
        $this->assertSame('OPEN_BILL', $order->status);
    }

    public function test_kasir_bisa_lanjutkan_open_bill_milik_sendiri_sampai_paid(): void
    {
        $kasir = User::factory()->create([
            'role' => User::ROLE_KASIR,
        ]);

        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 20000,
            'cost_price' => 8000,
            'is_active' => true,
            'track_stock' => true,
            'stock_qty' => 10,
        ]);

        $cashMethod = PaymentMethod::factory()->create([
            'name' => 'Cash',
            'code' => 'cash',
            'is_active' => true,
        ]);

        $saveResponse = $this->actingAs($kasir)->postJson(route('pos.open-bill.save'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                ],
            ],
            'discount_type' => 'none',
            'discount_value' => 0,
            'service' => 0,
        ]);
        $saveResponse->assertOk();

        $openBillId = (int) $saveResponse->json('open_bill_id');

        $checkoutResponse = $this->actingAs($kasir)->postJson(route('pos.checkout'), [
            'open_bill_id' => $openBillId,
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 3,
                ],
            ],
            'discount_type' => 'none',
            'discount_value' => 0,
            'service' => 0,
            'payment_method_id' => $cashMethod->id,
            'cash_received' => 100000,
        ]);

        $checkoutResponse->assertOk()->assertJsonStructure(['redirect']);

        $this->assertDatabaseCount('orders', 1);
        $order = Order::query()->with('items')->findOrFail($openBillId);

        $this->assertSame('PAID', $order->status);
        $this->assertMatchesRegularExpression('/^CS-\d{8}-\d{4}$/', $order->invoice_no);
        $this->assertEqualsWithDelta(66000, (float) $order->total, 0.01);
        $this->assertCount(1, $order->items);
        $this->assertSame(3, (int) $order->items->first()->qty);

        $product->refresh();
        $this->assertSame(7, $product->stock_qty);
    }

    public function test_admin_dapat_melihat_semua_open_bill_di_laporan(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
        $kasirA = User::factory()->create([
            'role' => User::ROLE_KASIR,
        ]);
        $kasirB = User::factory()->create([
            'role' => User::ROLE_KASIR,
        ]);

        $orderA = Order::factory()->create([
            'user_id' => $kasirA->id,
            'invoice_no' => 'OB-TEST-0001',
            'status' => 'OPEN_BILL',
            'payment_method' => 'OPEN BILL',
            'cash_received' => null,
            'change' => null,
        ]);
        $orderB = Order::factory()->create([
            'user_id' => $kasirB->id,
            'invoice_no' => 'OB-TEST-0002',
            'status' => 'OPEN_BILL',
            'payment_method' => 'OPEN BILL',
            'cash_received' => null,
            'change' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee("Open Bill #{$orderA->id}");
        $response->assertSee("Open Bill #{$orderB->id}");
    }
}
