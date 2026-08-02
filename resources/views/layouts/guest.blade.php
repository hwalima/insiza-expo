<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-expo font-sans antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">

        <a href="/" class="mb-8 text-center">
            <p class="text-3xl font-extrabold">
                <span class="text-[#D29500]">INSIZA</span>
                <span class="text-white"> EXPO</span>
            </p>
            <p class="text-xs text-white/40">Filabusi Show Grounds &bull; 16–18 Sep 2026</p>
        </a>

        <div class="glass-card w-full max-w-md rounded-3xl p-8 shadow-2xl">
            {{ $slot }}
        </div>

        <p class="mt-8 text-center text-xs text-white/30">
            Created by <a href="https://www.hwalima.digital/" target="_blank" class="text-[#D29500] hover:underline">Hwalima Digital</a>
        </p>
    </div>
</body>
</html>
