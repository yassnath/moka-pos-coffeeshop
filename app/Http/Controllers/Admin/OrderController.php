<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(Order $order): View
    {
        $order->load(['items.addons', 'user']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function void(Order $order): RedirectResponse
    {
        $this->authorize('void', $order);

        DB::transaction(function () use ($order): void {
            $order->loadMissing('items.product');

            if ($order->status === 'VOID') {
                return;
            }

            foreach ($order->items as $item) {
                if (! $item->product || ! $item->product->track_stock) {
                    continue;
                }

                $item->product->increment('stock_qty', $item->qty);
            }

            $order->update([
                'status' => 'VOID',
                'payment_method' => 'CANCELED',
            ]);
        });

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
