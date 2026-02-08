<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ trim(($title ?? '').' | '.config('app.name', 'Moka POS'), ' |') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="page-shell">
            <div class="relative flex flex-1 items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
                <div class="bg-blob -left-16 top-16 h-60 w-60 bg-moka-accent/35"></div>
                <div class="bg-blob -right-16 bottom-12 h-72 w-72 bg-moka-primary/20"></div>

                <div class="grid w-full max-w-5xl gap-5 lg:grid-cols-[1.1fr_1fr]">
                    <section class="glass-card hidden p-10 lg:flex lg:flex-col lg:justify-between">
                        <div>
                            <div class="mb-8 inline-flex items-center gap-3 rounded-full border border-moka-line bg-white px-4 py-2">
                                <img src="{{ asset('logo.png') }}" alt="Moka POS" class="h-8 w-8 rounded-lg object-cover">
                                <span class="font-display text-sm font-semibold text-moka-primary">Moka Kasir</span>
                            </div>
                            <h1 class="font-display text-4xl font-bold leading-tight text-moka-ink">POS coffeeshop yang cepat, rapi, dan nyaman dipakai kasir.</h1>
                            <p class="mt-4 max-w-md text-sm leading-relaxed text-moka-muted">
                                Kelola transaksi harian, menu, dan laporan dalam satu tampilan yang clean ala Moka POS.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-moka-line bg-white/80 p-4 text-xs text-moka-muted">
                            Dibuat untuk operasional outlet harian dengan fokus speed checkout.
                        </div>
                    </section>

                    <section class="glass-card p-6 sm:p-8">
                        <div class="mb-6 flex items-center justify-center gap-3 lg:hidden">
                            <img src="{{ asset('logo.png') }}" alt="Moka POS" class="h-10 w-10 rounded-xl object-cover">
                            <p class="font-display text-lg font-bold text-moka-primary">Moka Kasir</p>
                        </div>
                        {{ $slot }}
                    </section>
                </div>
            </div>

        </div>
    </body>
</html>
