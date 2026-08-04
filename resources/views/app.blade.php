<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- Dipakai unggahan XHR di komponen uploader. --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Warna latar awal <html> agar tidak berkedip putih sebelum CSS termuat. --}}
        <style>
            html {
                background-color: var(--t-background, #FAFAF8);
                scroll-behavior: smooth;
            }
        </style>

        {{-- Token tema aktif; dirender server agar tidak ada kedipan warna
             default sebelum hidrasi. Nilainya sudah divalidasi hex ketat. --}}
        <style id="theme-tokens">{!! $page['props']['theme']['css'] ?? '' !!}</style>

        {{-- Favicon dari Pengaturan Situs bila sudah diunggah; berkas statis di
             `public/` hanya cadangan supaya tab tidak pernah kosong. --}}
        @php($favicon = $page['props']['brand']['favicon'] ?? null)
        @if ($favicon)
            <link rel="icon" href="{{ $favicon }}">
            <link rel="apple-touch-icon" href="{{ $favicon }}">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
            <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @endif

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ $page['props']['brand']['name'] ?? config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
