<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Welcome')</title>
    @php $favicon = \App\Models\Setting::get('branding.favicon'); @endphp
    @if($favicon)
    <link rel="icon" href="{{ Storage::url($favicon) }}">
    @else
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="bg-expo antialiased">

    {{-- ── Scroll progress bar ── --}}
    <div
        x-data="{
            pct: 0,
            onScroll() {
                const el  = document.documentElement;
                const top = el.scrollTop  || document.body.scrollTop;
                const h   = el.scrollHeight - el.clientHeight;
                this.pct  = h > 0 ? Math.round((top / h) * 100) : 0;
            }
        }"
        @scroll.window.passive="onScroll()"
        class="fixed top-0 left-0 z-50 h-0.5 w-full"
        aria-hidden="true"
    >
        <div class="h-full bg-[#D29500] transition-all duration-100 ease-linear"
             :style="`width:${pct}%`"></div>
    </div>

    {{-- ── Navigation ── --}}
    <nav
        x-data="{ scrolled: false }"
        @scroll.window.passive="scrolled = window.scrollY > 10"
        :class="scrolled ? 'border-white/20 bg-black/60 shadow-lg shadow-black/40' : 'border-white/10 bg-black/30'"
        class="sticky top-0.5 z-40 border-b backdrop-blur-md transition-all duration-300"
    >
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="text-xl font-extrabold tracking-tight">
                    <span class="text-[#D29500]">INSIZA</span>
                    <span class="text-white"> EXPO</span>
                </span>
                <span class="hidden rounded-full bg-[#185909]/60 px-2 py-0.5 text-[10px] font-bold uppercase text-[#D29500] sm:block">2026</span>
            </a>

            {{-- Desktop nav --}}
            <div class="hidden gap-1 sm:flex">
                <a href="{{ route('home') }}"       class="{{ request()->routeIs('home')       ? 'nav-link-active' : 'nav-link' }}">Home</a>
                <a href="{{ route('floor-plan') }}" class="{{ request()->routeIs('floor-plan') ? 'nav-link-active' : 'nav-link' }}">Book a Stand</a>
                <a href="{{ route('about') }}"      class="{{ request()->routeIs('about')      ? 'nav-link-active' : 'nav-link' }}">About</a>
            </div>

            {{-- Auth links --}}
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('profile.edit') }}" class="nav-link text-xs hidden sm:inline">Dashboard</a>
                    @if(auth()->user()->hasAnyRole(['admin','super_admin']))
                        <a href="{{ route('admin.dashboard') }}" class="btn-gold text-xs !py-1.5">Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="nav-link text-xs">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link text-xs hidden sm:inline">Login</a>
                    <a href="{{ route('attend') }}" class="btn-primary text-xs !py-1.5">Attend Free</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ── Main ── --}}
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
        @yield('content')
    </main>

    {{-- ── Footer ── --}}
    <footer class="mt-12 border-t border-white/10 bg-black/30 backdrop-blur-md">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
            <div class="grid gap-6 sm:grid-cols-3">
                <div>
                    <p class="text-lg font-bold text-[#D29500]">INSIZA EXPO 2026</p>
                    <p class="mt-1 text-sm text-white/60">Filabusi Show Grounds</p>
                    <p class="text-sm text-white/60">16 – 18 September 2026</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white/80">Quick Links</p>
                    <ul class="mt-2 space-y-1 text-sm text-white/50">
                        <li><a href="{{ route('floor-plan') }}" class="hover:text-[#D29500]">Book a Stand</a></li>
                        <li><a href="{{ route('about') }}"      class="hover:text-[#D29500]">About the Expo</a></li>
                        <li><a href="{{ route('login') }}"      class="hover:text-[#D29500]">Exhibitor Login</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white/80">Contact</p>
                    <p class="mt-2 text-sm text-white/50">WhatsApp bot available 24/7</p>
                </div>
            </div>
            <div class="mt-8 border-t border-white/10 pt-4 text-center text-xs text-white/30">
                Created by <a href="https://www.hwalima.digital/" target="_blank" rel="noopener" class="text-[#D29500] hover:underline">Hwalima Digital</a>
                &bull; <a href="mailto:info@hwalima.digital" class="hover:text-white">info@hwalima.digital</a>
                &bull; +2633982747
            </div>
        </div>
    </footer>

    {{-- AI Chat Assistant (floating, available to all visitors) --}}
    <livewire:chat-assistant />

    @livewireScripts
</body>
</html>
