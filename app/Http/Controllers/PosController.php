<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\StoreOpenBillRequest;
use App\Models\Addon;
use App\Models\Category;
use App\Models\InvoiceCounter;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->active()
            ->whereHas('products', fn ($query) => $query->active()->whereHas('category', fn ($categoryQuery) => $categoryQuery->active()))
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->active())
            ->with([
                'category:id,name',
                'variants' => fn ($query) => $query->active()->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();

        $addons = Addon::query()->active()->orderBy('name')->get();
        $paymentMethods = PaymentMethod::query()->active()->orderBy('name')->get();

        $openBills = Order::query()
            ->where('status', 'OPEN_BILL')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get(['id', 'total', 'updated_at']);

        $resumeOpenBill = null;
        if ($request->filled('open_bill')) {
            $openBillId = (int) $request->integer('open_bill');
            $resumeOpenBill = Order::query()
                ->whereKey($openBillId)
                ->where('status', 'OPEN_BILL')
                ->where('user_id', $request->user()->id)
                ->with(['items.addons:id,order_item_id,addon_id,name_snapshot,price'])
                ->first();
        }

        $resumePayload = null;
        if ($resumeOpenBill) {
            $resumePayload = [
                'id' => $resumeOpenBill->id,
                'discount_type' => $resumeOpenBill->discount_type,
                'discount_value' => (float) $resumeOpenBill->discount_value,
                'tax_percent' => $this->deriveTaxPercent($resumeOpenBill),
                'service' => (float) $resumeOpenBill->service,
                'notes' => $resumeOpenBill->notes,
                'items' => $resumeOpenBill->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'name_snapshot' => $item->name_snapshot,
                        'price' => (float) $item->price,
                        'qty' => (int) $item->qty,
                        'notes' => $item->notes,
                        'addons' => $item->addons->map(fn ($addon) => [
                            'id' => $addon->addon_id,
                            'name' => $addon->name_snapshot,
                            'price' => (float) $addon->price,
                        ])->values()->all(),
                    ];
                })->values()->all(),
            ];
        }

        return view('pos.index', [
            'categories' => $categories,
            'products' => $products,
            'addons' => $addons,
            'paymentMethods' => $paymentMethods,
            'openBills' => $openBills,
            'resumeOpenBill' => $resumePayload,
        ]);
    }

    public function saveOpenBill(StoreOpenBillRequest $request): JsonResponse
    {
        $order = $this->persistOrderFromPayload(
            userId: $request->user()->id,
            validated: $request->validated(),
            targetStatus: 'OPEN_BILL',
        );

        return response()->json([
            'open_bill_id' => $order->id,
            'message' => 'Open bill berhasil disimpan.',
            'history' => route('pos.history'),
        ]);
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $order = $this->persistOrderFromPayload(
            userId: $request->user()->id,
            validated: $request->validated(),
            targetStatus: 'PAID',
        );

        return response()->json([
            'redirect' => route('orders.receipt', ['order' => $order->id, 'autoprint' => 1]),
        ]);
    }

    public function history(Request $request)
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->where(function ($query) {
                $query->where('status', 'OPEN_BILL')
                    ->orWhereDate('ordered_at', now()->toDateString());
            })
            ->orderByRaw("CASE WHEN status = 'OPEN_BILL' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('pos.history', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $order->load(['items.addons', 'user']);

        return view('pos.show', [
            'order' => $order,
        ]);
    }

    public function receipt(Request $request, Order $order)
    {
        Gate::authorize('view', $order);
        abort_if($order->status !== 'PAID', 404);

        $order->load(['items.addons', 'user']);

        return view('pos.receipt', [
            'order' => $order,
            'autoPrint' => $request->boolean('autoprint'),
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function persistOrderFromPayload(int $userId, array $validated, string $targetStatus): Order
    {
        $itemsInput = $validated['items'];
        $discountType = $validated['discount_type'] ?? 'none';
        $discountValue = (float) ($validated['discount_value'] ?? 0);
        $taxPercentInput = isset($validated['tax_percent']) ? (float) $validated['tax_percent'] : null;
        $service = (float) ($validated['service'] ?? 0);
        $openBillId = isset($validated['open_bill_id']) ? (int) $validated['open_bill_id'] : null;

        return DB::transaction(function () use ($itemsInput, $discountType, $discountValue, $taxPercentInput, $service, $validated, $targetStatus, $userId, $openBillId) {
            $openBill = null;
            $existingQtyByProduct = [];

            if ($openBillId) {
                $openBill = Order::query()
                    ->whereKey($openBillId)
                    ->lockForUpdate()
                    ->with(['items.product:id,track_stock'])
                    ->first();

                if (! $openBill || $openBill->status !== 'OPEN_BILL') {
                    throw ValidationException::withMessages([
                        'open_bill_id' => 'Open bill tidak ditemukan atau sudah ditutup.',
                    ]);
                }

                if ((int) $openBill->user_id !== $userId) {
                    throw ValidationException::withMessages([
                        'open_bill_id' => 'Kamu tidak memiliki akses ke open bill ini.',
                    ]);
                }

                foreach ($openBill->items as $oldItem) {
                    if (! $oldItem->product || ! $oldItem->product->track_stock) {
                        continue;
                    }

                    $existingQtyByProduct[$oldItem->product_id] = ($existingQtyByProduct[$oldItem->product_id] ?? 0) + $oldItem->qty;
                }
            }

            [$computedItems, $subtotal] = $this->computeValidatedItems($itemsInput, $existingQtyByProduct);

            $normalizedDiscountValue = 0.0;
            $discountAmount = 0.0;

            if ($discountType === 'percent') {
                $normalizedDiscountValue = min(100, max(0, $discountValue));
            } elseif ($discountType === 'amount') {
                $normalizedDiscountValue = min($subtotal, max(0, $discountValue));
            }

            $discountAmount = $this->resolveDiscountAmount($subtotal, $discountType, $normalizedDiscountValue);
            $taxPercent = is_null($taxPercentInput)
                ? ($openBill ? $this->deriveTaxPercent($openBill) : 10.0)
                : min(100, max(0, $taxPercentInput));

            $baseAfterDiscount = round(max(0, $subtotal - $discountAmount), 2);
            $tax = round($baseAfterDiscount * ($taxPercent / 100), 2);
            $total = round($baseAfterDiscount + $tax + $service, 2);

            $paymentMethodName = 'OPEN BILL';
            $cashReceived = null;
            $change = null;
            $invoiceNo = $openBill?->invoice_no ?? $this->nextOpenBillReference();

            if ($targetStatus === 'PAID') {
                $paymentMethod = PaymentMethod::query()->active()->findOrFail($validated['payment_method_id']);
                $paymentMethodName = $paymentMethod->name;

                $cashReceived = $validated['cash_received'] ?? null;
                if ($paymentMethod->code === 'cash') {
                    if ($cashReceived === null || (float) $cashReceived < $total) {
                        throw ValidationException::withMessages([
                            'cash_received' => 'Uang diterima kurang dari total pembayaran.',
                        ]);
                    }

                    $cashReceived = round((float) $cashReceived, 2);
                    $change = round($cashReceived - $total, 2);
                } else {
                    $cashReceived = null;
                }

                $invoiceNo = $this->nextInvoiceNumber();
            }

            if ($openBill) {
                $this->restoreStockFromOrder($openBill);
                $openBill->items()->delete();

                $openBill->update([
                    'invoice_no' => $invoiceNo,
                    'status' => $targetStatus,
                    'subtotal' => $subtotal,
                    'discount_type' => $discountType,
                    'discount_value' => $normalizedDiscountValue,
                    'tax' => $tax,
                    'service' => $service,
                    'total' => $total,
                    'payment_method' => $paymentMethodName,
                    'cash_received' => $cashReceived,
                    'change' => $change,
                    'notes' => $validated['notes'] ?? null,
                    'ordered_at' => now(),
                ]);

                $order = $openBill->refresh();
            } else {
                $order = Order::query()->create([
                    'invoice_no' => $invoiceNo,
                    'user_id' => $userId,
                    'status' => $targetStatus,
                    'subtotal' => $subtotal,
                    'discount_type' => $discountType,
                    'discount_value' => $normalizedDiscountValue,
                    'tax' => $tax,
                    'service' => $service,
                    'total' => $total,
                    'payment_method' => $paymentMethodName,
                    'cash_received' => $cashReceived,
                    'change' => $change,
                    'notes' => $validated['notes'] ?? null,
                    'ordered_at' => now(),
                ]);
            }

            $this->replaceOrderItems($order, $computedItems);

            return $order;
        });
    }

    private function resolveDiscountAmount(float $subtotal, string $discountType, float $discountValue): float
    {
        if ($discountType === 'percent') {
            return round($subtotal * min(100, max(0, $discountValue)) / 100, 2);
        }

        if ($discountType === 'amount') {
            return round(min($subtotal, max(0, $discountValue)), 2);
        }

        return 0.0;
    }

    private function deriveTaxPercent(Order $order): float
    {
        $subtotal = (float) $order->subtotal;
        $discountAmount = $this->resolveDiscountAmount(
            $subtotal,
            (string) $order->discount_type,
            (float) $order->discount_value,
        );

        $baseAfterDiscount = round(max(0, $subtotal - $discountAmount), 2);
        if ($baseAfterDiscount <= 0) {
            return 10.0;
        }

        return round(min(100, max(0, ((float) $order->tax / $baseAfterDiscount) * 100)), 2);
    }

    /**
     * @param array<int, array<string, mixed>> $itemsInput
     * @param array<int, int> $existingQtyByProduct
     * @return array{0: array<int, array<string, mixed>>, 1: float}
     */
    private function computeValidatedItems(array $itemsInput, array $existingQtyByProduct = []): array
    {
        $computedItems = [];
        $subtotal = 0.0;
        $requestedQtyByProduct = [];

        foreach ($itemsInput as $item) {
            $product = Product::query()
                ->active()
                ->whereHas('category', fn ($query) => $query->active())
                ->whereKey($item['product_id'])
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => 'Produk tidak ditemukan atau nonaktif.',
                ]);
            }

            $variant = null;
            if (! empty($item['variant_id'])) {
                $variant = ProductVariant::query()
                    ->whereKey($item['variant_id'])
                    ->where('product_id', $product->id)
                    ->active()
                    ->first();

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'items' => 'Varian tidak valid.',
                    ]);
                }
            }

            if ($product->variants()->active()->exists() && ! $variant) {
                throw ValidationException::withMessages([
                    'items' => 'Varian wajib dipilih untuk produk '.$product->name.'.',
                ]);
            }

            $qty = (int) $item['qty'];

            if ($product->track_stock) {
                $requestedQtyByProduct[$product->id] = ($requestedQtyByProduct[$product->id] ?? 0) + $qty;
                $availableStock = $product->stock_qty + ($existingQtyByProduct[$product->id] ?? 0);

                if ($availableStock < $requestedQtyByProduct[$product->id]) {
                    throw ValidationException::withMessages([
                        'items' => 'Stok tidak cukup untuk '.$product->name.'.',
                    ]);
                }
            }

            $basePrice = (float) $product->price;

            if ($variant) {
                if (! is_null($variant->price)) {
                    $basePrice = (float) $variant->price;
                } elseif (! is_null($variant->price_delta)) {
                    $basePrice = (float) $product->price + (float) $variant->price_delta;
                }
            }

            $addonIds = array_values(array_unique($item['addons'] ?? []));
            $addons = collect();

            if (count($addonIds) > 0) {
                $addons = Addon::query()->active()->whereIn('id', $addonIds)->get();

                if ($addons->count() !== count($addonIds)) {
                    throw ValidationException::withMessages([
                        'items' => 'Add-on tidak valid.',
                    ]);
                }
            }

            $addonTotal = (float) $addons->sum('price');
            $lineTotal = round(($basePrice + $addonTotal) * $qty, 2);
            $itemCostPrice = (float) $product->cost_price;
            $lineCostTotal = round($itemCostPrice * $qty, 2);
            $subtotal += $lineTotal;

            $computedItems[] = [
                'product' => $product,
                'variant' => $variant,
                'addons' => $addons,
                'qty' => $qty,
                'price' => $basePrice,
                'cost_price' => $itemCostPrice,
                'line_total' => $lineTotal,
                'line_cost_total' => $lineCostTotal,
                'notes' => $item['notes'] ?? null,
            ];
        }

        return [ $computedItems, round($subtotal, 2) ];
    }

    private function restoreStockFromOrder(Order $order): void
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            if (! $item->product || ! $item->product->track_stock) {
                continue;
            }

            $item->product->increment('stock_qty', $item->qty);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $computedItems
     */
    private function replaceOrderItems(Order $order, array $computedItems): void
    {
        foreach ($computedItems as $computed) {
            /** @var Product $product */
            $product = $computed['product'];

            if ($product->track_stock) {
                $product->decrement('stock_qty', $computed['qty']);
            }

            $nameSnapshot = $product->name;
            if ($computed['variant']) {
                $nameSnapshot .= ' - '.$computed['variant']->name;
            }

            $orderItem = $order->items()->create([
                'product_id' => $product->id,
                'variant_id' => $computed['variant']?->id,
                'name_snapshot' => $nameSnapshot,
                'price' => $computed['price'],
                'cost_price' => $computed['cost_price'],
                'qty' => $computed['qty'],
                'line_total' => $computed['line_total'],
                'line_cost_total' => $computed['line_cost_total'],
                'notes' => $computed['notes'],
            ]);

            /** @var Collection<int, Addon> $addons */
            $addons = $computed['addons'];
            foreach ($addons as $addon) {
                $orderItem->addons()->create([
                    'addon_id' => $addon->id,
                    'name_snapshot' => $addon->name,
                    'price' => $addon->price,
                ]);
            }
        }
    }

    private function nextOpenBillReference(): string
    {
        return 'OB-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function nextInvoiceNumber(): string
    {
        $today = now()->toDateString();

        $counter = InvoiceCounter::query()->where('date', $today)->lockForUpdate()->first();

        if (! $counter) {
            InvoiceCounter::query()->insertOrIgnore([
                'date' => $today,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $counter = InvoiceCounter::query()->where('date', $today)->lockForUpdate()->first();
        }

        if (! $counter) {
            throw new \RuntimeException('Tidak bisa membuat invoice counter.');
        }

        $counter->last_number += 1;
        $counter->save();

        return 'CS-'.now()->format('Ymd').'-'.str_pad((string) $counter->last_number, 4, '0', STR_PAD_LEFT);
    }
}
