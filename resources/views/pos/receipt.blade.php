<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $order->invoice_no }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <style>
        :root {
            color-scheme: light;
        }

        body {
            margin: 0;
            padding: 20px;
            background: #f5f1eb;
            color: #2a2018;
            font-family: "Sora", Arial, sans-serif;
        }

        .receipt-shell {
            max-width: 420px;
            margin: 0 auto;
            border: 1px solid #dfd3c3;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 20px 38px -24px rgba(111, 78, 55, 0.5);
            overflow: hidden;
        }

        .receipt-head {
            padding: 16px;
            border-bottom: 1px dashed #d4c6b3;
            text-align: center;
        }

        .receipt-head img {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid #e7ded2;
        }

        .receipt-body {
            padding: 16px;
            font-size: 13px;
        }

        .mono {
            font-family: "Courier New", Courier, monospace;
        }

        .line {
            border-top: 1px dashed #d4c6b3;
            margin: 10px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 4px 0;
        }

        .actions {
            max-width: 420px;
            margin: 12px auto 0;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            border: 1px solid #dccdb9;
            background: #fff;
            color: #3f3126;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            border-color: transparent;
            background: #6f4e37;
            color: #fff;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                background: #fff;
                padding: 0;
            }

            .receipt-shell {
                max-width: 80mm;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .actions {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-shell">
        <header class="receipt-head">
            <img src="{{ asset('logo.png') }}" alt="Logo">
            <h2 style="margin:8px 0 0;font-size:18px;">Moka POS</h2>
            <p style="margin:2px 0 0;font-size:12px;color:#766454;">Solvix Coffee Shop</p>
        </header>
        <section class="receipt-body mono">
            <div class="row"><span>Invoice</span><strong>{{ $order->invoice_no }}</strong></div>
            <div class="row"><span>Tanggal</span><span>{{ optional($order->ordered_at)->format('d-m-Y H:i') }}</span></div>
            <div class="row"><span>Kasir</span><span>{{ $order->user?->name }}</span></div>
            <div class="row"><span>Bayar</span><span>{{ $order->payment_method }}</span></div>

            <div class="line"></div>

            @foreach($order->items as $item)
                <div style="margin-bottom:8px;">
                    <div class="row">
                        <span>{{ $item->name_snapshot }}</span>
                    </div>
                    <div class="row">
                        <span>{{ $item->qty }} x Rp {{ number_format((float) $item->price, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</span>
                    </div>
                    @foreach($item->addons as $addon)
                        <div class="row" style="font-size:12px;color:#5f5043;">
                            <span>+ {{ $addon->name_snapshot }}</span>
                            <span>Rp {{ number_format((float) $addon->price, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    @if($item->notes)
                        <div style="font-size:12px;color:#5f5043;">Catatan: {{ $item->notes }}</div>
                    @endif
                </div>
            @endforeach

            <div class="line"></div>

            <div class="row"><span>Subtotal</span><span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span></div>
            <div class="row"><span>Diskon</span><span>{{ $order->discount_type === 'percent' ? number_format((float) $order->discount_value, 2, ',', '.').'%' : 'Rp '.number_format((float) $order->discount_value, 0, ',', '.') }}</span></div>
            <div class="row"><span>Pajak</span><span>Rp {{ number_format((float) $order->tax, 0, ',', '.') }}</span></div>
            <div class="row"><span>Service</span><span>Rp {{ number_format((float) $order->service, 0, ',', '.') }}</span></div>
            <div class="row" style="font-size:16px;font-weight:bold;">
                <span>TOTAL</span>
                <span>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
            </div>
            @if($order->cash_received !== null)
                <div class="row"><span>Cash</span><span>Rp {{ number_format((float) $order->cash_received, 0, ',', '.') }}</span></div>
                <div class="row"><span>Kembalian</span><span>Rp {{ number_format((float) $order->change, 0, ',', '.') }}</span></div>
            @endif

            <div class="line"></div>
            <p style="margin:0;text-align:center;font-size:12px;">Terima kasih, selamat menikmati.</p>
        </section>
    </div>

    <div class="actions">
        <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.orders.show', $order) }}" class="btn">Kembali ke Detail</a>
        @else
            <a href="{{ route('pos.history') }}" class="btn">Kembali ke Riwayat</a>
            <a href="{{ route('pos.index') }}" class="btn">Kembali ke POS</a>
        @endif
    </div>

    @if($autoPrint)
        <script>
            window.addEventListener('load', () => {
                window.print();
            });
        </script>
    @endif
</body>
</html>
