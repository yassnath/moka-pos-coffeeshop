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
    <body class="body-gradient-page">
        <div class="page-shell">
            <div class="bg-blob -left-20 top-10 h-64 w-64 bg-moka-accent/35"></div>
            <div class="bg-blob -right-24 top-20 h-72 w-72 bg-moka-primary/20"></div>
            <div class="bg-blob bottom-10 left-1/3 h-56 w-56 bg-moka-accent/30"></div>

            @include('layouts.navigation')

            <main class="mx-auto w-full max-w-[1440px] flex-1 px-4 pb-10 pt-6 sm:px-6 lg:px-8">
                @if (session('success') || session('error'))
                    <div x-data="{ flashOpen: true }">
                        <x-ui.modal name="flashOpen" maxWidth="md">
                            <div class="moka-modal-content">
                                <div class="moka-modal-header">
                                    <div>
                                        <h3 class="moka-modal-title">{{ session('success') ? 'Berhasil' : 'Gagal' }}</h3>
                                        <p class="moka-modal-subtitle">{{ session('success') ?? session('error') }}</p>
                                    </div>
                                    <button type="button" class="moka-modal-close" @click="flashOpen = false" aria-label="Tutup popup">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="moka-modal-footer">
                                    <button type="button" class="{{ session('success') ? 'moka-btn' : 'moka-btn-danger' }}" @click="flashOpen = false">OK</button>
                                </div>
                            </div>
                        </x-ui.modal>
                    </div>
                @endif

                @isset($header)
                    <header class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        {{ $header }}
                    </header>
                @endisset

                {{ $slot }}
            </main>

        </div>
    </body>
</html>



