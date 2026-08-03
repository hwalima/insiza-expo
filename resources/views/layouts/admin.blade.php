<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-expo antialiased">

<div class="flex min-h-screen">

    {{-- ── Sidebar ──────────────────────────────────────── --}}
    <aside class="hidden w-56 shrink-0 flex-col border-r border-white/10 bg-black/40 backdrop-blur-md lg:flex">
        <div class="px-4 py-5">
            <p class="text-lg font-extrabold">
                <span class="text-[#D29500]">INSIZA</span>
                <span class="text-white text-sm"> Admin</span>
            </p>
        </div>
        <nav class="flex-1 space-y-1 px-2 pb-4">
            <a href="{{ route('admin.dashboard') }}"  class="{{ request()->routeIs('admin.dashboard')  ? 'nav-link-active' : 'nav-link' }} block">Dashboard</a>
            <a href="{{ route('admin.expo') }}"        class="{{ request()->routeIs('admin.expo*')      ? 'nav-link-active' : 'nav-link' }} block">Expo Management</a>
            <a href="{{ route('admin.floor-plan') }}"  class="{{ request()->routeIs('admin.floor-plan')  ? 'nav-link-active' : 'nav-link' }} block">Floor Plan Editor</a>
            <a href="{{ route('admin.bookings') }}"    class="{{ request()->routeIs('admin.bookings')    ? 'nav-link-active' : 'nav-link' }} block">Bookings</a>
            <a href="{{ route('admin.attendees') }}"  class="{{ request()->routeIs('admin.attendees*')   ? 'nav-link-active' : 'nav-link' }} block">Attendees</a>
        </nav>
        <div class="border-t border-white/10 p-3">
            <a href="{{ route('home') }}" class="nav-link block text-xs">← Public Site</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-full text-left text-xs text-red-400">Logout</button>
            </form>
        </div>
    </aside>

    {{-- ── Content area ──────────────────────────────────── --}}
    <div class="flex flex-1 flex-col overflow-hidden">

        {{-- Mobile top-bar --}}
        <header class="flex items-center justify-between border-b border-white/10 bg-black/40 px-4 py-3 lg:hidden">
            <p class="font-bold text-[#D29500]">INSIZA Admin</p>
            <div class="flex gap-2 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-xs">Dash</a>
                <a href="{{ route('admin.expo') }}"       class="nav-link text-xs">Expo</a>
                <a href="{{ route('admin.floor-plan') }}" class="nav-link text-xs">Floor</a>
                <a href="{{ route('admin.bookings') }}"   class="nav-link text-xs">Bookings</a>
                <a href="{{ route('admin.attendees') }}" class="nav-link text-xs">Attendees</a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6">
            @yield('content')
        </main>

        <footer class="border-t border-white/10 px-6 py-3 text-center text-xs text-white/30">
            Created by <a href="https://www.hwalima.digital/" target="_blank" rel="noopener" class="text-[#D29500]">Hwalima Digital</a>
            &bull; info@hwalima.digital &bull; +2633982747
        </footer>
    </div>
</div>

@livewireScripts
</body>
</html>
