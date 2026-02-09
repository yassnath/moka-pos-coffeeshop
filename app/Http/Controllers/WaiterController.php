<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWaiterOrderRequest;
use App\Models\Addon;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WaiterController extends PosController
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

        $resumeWaiterOrder = null;
        if ($request->filled('waiter_order')) {
            $waiterOrderId = (int) $request->integer('waiter_order');
            $resumeWaiterOrder = Order::query()
                ->whereKey($waiterOrderId)
                ->where('status', 'WAITING')
                ->where('waiter_id', $request->user()->id)
                ->with(['items.addons:id,order_item_id,addon_id,name_snapshot,price'])
                ->first();
        }

        $resumeWaiterPayload = null;
        if ($resumeWaiterOrder) {
            $resumeWaiterPayload = [
                'id' => $resumeWaiterOrder->id,
                'discount_type' => $resumeWaiterOrder->discount_type,
                'discount_value' => (float) $resumeWaiterOrder->discount_value,
                'tax_percent' => $this->deriveTaxPercent($resumeWaiterOrder),
                'service' => (float) $resumeWaiterOrder->service,
                'notes' => $resumeWaiterOrder->notes,
                'items' => $resumeWaiterOrder->items->map(function ($item) {
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
            'paymentMethods' => collect(),
            'openBills' => collect(),
            'resumeOpenBill' => null,
            'waiterOrders' => collect(),
            'resumeWaiterOrder' => $resumeWaiterPayload,
            'mode' => 'waiter',
        ]);
    }

    public function store(StoreWaiterOrderRequest $request): JsonResponse
    {
        $order = $this->persistOrderFromPayload(
            userId: $request->user()->id,
            validated: $request->validated(),
            targetStatus: 'WAITING',
        );

        return response()->json([
            'order_id' => $order->id,
            'message' => 'Pesanan berhasil dikirim ke kasir.',
        ]);
    }

    public function history(Request $request): View
    {
        $orders = Order::query()
            ->where('waiter_id', $request->user()->id)
            ->where(function ($query): void {
                $query->where('status', 'WAITING')
                    ->orWhereDate('ordered_at', now()->toDateString());
            })
            ->orderByRaw("CASE WHEN status = 'WAITING' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('waiter.history', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['items.addons', 'user', 'waiter']);

        return view('waiter.show', [
            'order' => $order,
        ]);
    }
}
