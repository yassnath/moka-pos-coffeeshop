<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    private function rowCostExpression(): string
    {
        return "CASE
            WHEN order_items.line_cost_total > 0 THEN order_items.line_cost_total
            ELSE (
                CASE
                    WHEN order_items.cost_price > 0 THEN order_items.cost_price
                    ELSE COALESCE(products.cost_price, 0)
                END * order_items.qty
            )
        END";
    }

    /**
     * @return array{0:\Illuminate\Support\Carbon,1:\Illuminate\Support\Carbon,2:string,3:string}
     */
    private function resolveDateRange(Request $request): array
    {
        $fromInput = $request->string('from')->toString();
        $toInput = $request->string('to')->toString();

        try {
            $fromDate = $fromInput !== '' ? Carbon::parse($fromInput)->startOfDay() : now()->startOfDay();
        } catch (\Throwable $exception) {
            $fromDate = now()->startOfDay();
        }

        try {
            $toDate = $toInput !== '' ? Carbon::parse($toInput)->endOfDay() : now()->endOfDay();
        } catch (\Throwable $exception) {
            $toDate = now()->endOfDay();
        }

        if ($fromDate->greaterThan($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        return [$fromDate, $toDate, $fromDate->toDateString(), $toDate->toDateString()];
    }

    public function index(Request $request): View
    {
        [$fromDate, $toDate, $from, $to] = $this->resolveDateRange($request);

        $paidOrders = Order::query()
            ->where('status', 'PAID')
            ->whereBetween('ordered_at', [$fromDate, $toDate]);

        $rowCostExpression = $this->rowCostExpression();
        $totalOmzet = (float) (clone $paidOrders)->sum('total');
        $totalModal = (float) OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'PAID')
            ->whereBetween('orders.ordered_at', [$fromDate, $toDate])
            ->sum(DB::raw($rowCostExpression));
        $transactionCount = (int) (clone $paidOrders)->count();
        $grossProfit = $totalOmzet - $totalModal;

        $breakdown = (clone $paidOrders)
            ->selectRaw('payment_method, COUNT(*) as transaksi, SUM(total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $topItems = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'PAID')
            ->whereBetween('orders.ordered_at', [$fromDate, $toDate])
            ->selectRaw("order_items.name_snapshot, SUM(order_items.qty) as qty, SUM({$rowCostExpression}) as modal, SUM(order_items.line_total) as total")
            ->groupBy('order_items.name_snapshot')
            ->orderByDesc('qty')
            ->limit(4)
            ->get();

        $orders = Order::query()
            ->with([
                'user:id,name',
                'items:id,order_id,product_id,cost_price,qty,line_cost_total',
                'items.product:id,cost_price',
            ])
            ->where(function ($query) use ($fromDate, $toDate): void {
                $query->whereBetween('ordered_at', [$fromDate, $toDate])
                    ->orWhere('status', 'OPEN_BILL');
            })
            ->orderByRaw("CASE WHEN status = 'OPEN_BILL' THEN 0 ELSE 1 END")
            ->orderByDesc('ordered_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'totalOmzet' => $totalOmzet,
            'totalModal' => $totalModal,
            'grossProfit' => $grossProfit,
            'transactionCount' => $transactionCount,
            'breakdown' => $breakdown,
            'topItems' => $topItems,
            'orders' => $orders,
        ]);
    }

    public function export(Request $request)
    {
        [$fromDate, $toDate, $from, $to] = $this->resolveDateRange($request);

        $orders = Order::query()
            ->with([
                'user:id,name',
                'items:id,order_id,product_id,cost_price,qty,line_cost_total',
                'items.product:id,cost_price',
            ])
            ->where(function ($query) use ($fromDate, $toDate): void {
                $query->whereBetween('ordered_at', [$fromDate, $toDate])
                    ->orWhere('status', 'OPEN_BILL');
            })
            ->orderByRaw("CASE WHEN status = 'OPEN_BILL' THEN 0 ELSE 1 END")
            ->orderBy('ordered_at')
            ->get();

        $fileName = 'laporan-'.$from.'-'.$to.'.csv';

        $callback = static function () use ($orders): void {
            $stream = fopen('php://output', 'w');

            fputcsv($stream, ['Invoice', 'Tanggal', 'Kasir', 'Status', 'Metode', 'Total', 'Modal', 'Laba Kotor']);

            foreach ($orders as $order) {
                $orderCost = (float) $order->items->sum(fn ($item) => (float) $item->resolved_line_cost_total);
                $orderProfit = (float) $order->total - $orderCost;

                fputcsv($stream, [
                    $order->status === 'OPEN_BILL' ? 'Open Bill #'.$order->id : $order->invoice_no,
                    optional($order->ordered_at)->format('Y-m-d H:i:s'),
                    $order->user?->name,
                    $order->status,
                    $order->status === 'OPEN_BILL' ? '-' : $order->payment_method,
                    $order->total,
                    $orderCost,
                    $orderProfit,
                ]);
            }

            fclose($stream);
        };

        return Response::streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
