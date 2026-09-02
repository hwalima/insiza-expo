@extends('layouts.public')
@section('title', 'Welcome')

@section('content')

{{-- Hero --}}
<section
    x-data="{
        slides: [
            '/images/hero/hero1.jpeg',
            '/images/hero/hero2.jpeg',
            '/images/hero/hero3.jpeg',
        ],
        current: 0,
        loaded: [false, false, false],
        init() {
            // preload all slides
            this.slides.forEach((src, i) => {
                const img = new Image();
                img.onload  = () => { this.loaded[i] = true; };
                img.onerror = () => { this.loaded[i] = false; };
                img.src = src;
            });
            setInterval(() => {
                this.current = (this.current + 1) % this.slides.length;
            }, 5000);
        }
    }"
    class="relative overflow-hidden rounded-3xl px-6 py-20 text-center sm:py-32"
    style="min-height: 420px;"
>
    {{-- Slide images --}}
    <template x-for="(slide, i) in slides" :key="i">
        <div
            x-show="current === i && loaded[i]"
            x-transition:enter="transition duration-1000 ease-in-out"
            x-transition:enter-start="opacity-0 scale-105"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition duration-700 ease-in-out"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-none absolute inset-0"
            style="display:none"
        >
            <img :src="slide" class="hero-slide-img h-full w-full rounded-3xl object-cover object-center"
                 :alt="'Hero slide ' + (i+1)">
        </div>
    </template>

    {{-- Fallback gradient shown while images are loading or if all images fail --}}
    <div class="pointer-events-none absolute inset-0 rounded-3xl"
         x-show="!loaded.some(v => v)"
         style="background: linear-gradient(135deg, #185909 0%, #111D02 60%, #1a2e12 100%); display:none"></div>

    {{-- Dark overlay — stronger to handle both light (logo) and dark (photo) slides --}}
    <div class="pointer-events-none absolute inset-0 rounded-3xl"
         style="background: linear-gradient(160deg, rgba(17,29,2,0.88) 0%, rgba(17,29,2,0.60) 40%, rgba(17,29,2,0.85) 100%);"></div>

    {{-- Gold radial glow top-right --}}
    <div class="pointer-events-none absolute inset-0 rounded-3xl"
         style="background: radial-gradient(circle at 75% 30%, rgba(210,149,0,0.18) 0%, transparent 55%);"></div>

    {{-- Bottom fade --}}
    <div class="pointer-events-none absolute bottom-0 left-0 right-0 h-24 rounded-b-3xl bg-gradient-to-t from-[#111D02] to-transparent"></div>

    {{-- Slide dots --}}
    <div class="absolute bottom-5 left-0 right-0 flex justify-center gap-1.5 z-10">
        <template x-for="(slide, i) in slides" :key="i">
            <button @click="current = i"
                    :class="current === i ? 'bg-[#D29500] w-5' : 'bg-white/30 w-2'"
                    class="h-2 rounded-full transition-all duration-300"
                    :aria-label="'Go to slide ' + (i+1)"></button>
        </template>
    </div>

    {{-- Content --}}
    @if($expo)
        <p class="relative mb-2 text-sm font-semibold uppercase tracking-widest text-[#D29500] drop-shadow">
            {{ $expo->start_date->format('d M') }} &ndash; {{ $expo->end_date->format('d M Y') }} &bull; {{ $expo->venue }}
        </p>
        <h1 class="relative text-4xl font-extrabold leading-tight text-white drop-shadow-lg sm:text-6xl">
            {{ $expo->name }}
        </h1>
        @if($expo->theme)
            <blockquote class="relative mx-auto mt-5 max-w-2xl rounded-2xl border border-[#D29500]/40 bg-[#D29500]/15 px-6 py-3 shadow-inner shadow-[#D29500]/10 backdrop-blur-sm">
                <p class="text-base font-medium italic text-[#D29500] sm:text-lg">&ldquo;{{ $expo->theme }}&rdquo;</p>
            </blockquote>
        @endif
        <div class="relative mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
            <a href="{{ route('floor-plan') }}" class="btn-gold w-full sm:w-auto">Book a Stand</a>
            <a href="{{ route('about') }}"      class="btn-ghost w-full border border-white/20 sm:w-auto">Learn More</a>
        </div>
    @else
        <h1 class="relative text-4xl font-extrabold text-white drop-shadow-lg">Insiza District Industrial Expo</h1>
        <p class="relative mt-4 text-white/70">Stay tuned &mdash; the next expo is coming soon.</p>
    @endif
</section>

{{-- Stats strip --}}
@if($expo)
@php
    $standsTotal     = $expo->stands()->count();
    $standsAvailable = $expo->stands()->where('status','available')->count();
    $standsReserved  = $expo->stands()->where('status','reserved')->count();
    $standsOccupied  = $expo->stands()->where('status','occupied')->count();
    $daysUntil       = (int) max(0, now()->startOfDay()->diffInDays($expo->start_date->startOfDay(), false));
    $isLive          = now()->between($expo->start_date, $expo->end_date);
@endphp

<div
    x-data="{
        panel: null,
        d: 0, h: 0, m: 0, s: 0,
        live: {{ $isLive ? 'true' : 'false' }},
        ended: {{ now() > $expo->end_date ? 'true' : 'false' }},
        init() {
            const target = new Date('{{ $expo->start_date->format('Y-m-d') }}T00:00:00');
            const tick = () => {
                const diff = target - Date.now();
                if (diff <= 0) { this.live = true; return; }
                this.d = Math.floor(diff / 86400000);
                this.h = Math.floor((diff % 86400000) / 3600000);
                this.m = Math.floor((diff % 3600000)  / 60000);
                this.s = Math.floor((diff % 60000)    / 1000);
            };
            tick();
            setInterval(tick, 1000);
        }
    }"
    class="mt-6 space-y-3"
>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

        <a href="{{ route('floor-plan') }}"
           class="glass-card group flex flex-col items-center justify-center rounded-2xl border-l-4 border-l-[#D29500]/40 p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-l-[#D29500] hover:bg-white/15 hover:shadow-lg hover:shadow-[#D29500]/10 active:translate-y-0">
            <p class="text-4xl font-extrabold text-[#D29500] transition-transform duration-150 group-hover:scale-110">{{ $standsTotal }}</p>
            <p class="mt-1 text-xs font-medium text-white/60">Total Stands</p>
            <span class="mt-1.5 text-[10px] text-[#D29500]/50 opacity-0 transition-opacity group-hover:opacity-100">View floor plan &rarr;</span>
        </a>

        <a href="{{ route('floor-plan') }}"
           class="glass-card group flex flex-col items-center justify-center rounded-2xl border-l-4 border-l-green-500/40 p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-l-green-400 hover:bg-white/15 hover:shadow-lg hover:shadow-green-500/10 active:translate-y-0">
            <p class="text-4xl font-extrabold text-green-400 transition-transform duration-150 group-hover:scale-110">{{ $standsAvailable }}</p>
            <p class="mt-1 text-xs font-medium text-white/60">Available</p>
            <div class="mt-1.5 flex gap-2 text-[10px] opacity-0 transition-opacity group-hover:opacity-100">
                @if($standsReserved) <span class="text-amber-400">{{ $standsReserved }} reserved</span> @endif
                @if($standsOccupied) <span class="text-red-400">{{ $standsOccupied }} taken</span> @endif
                @if(! $standsReserved && ! $standsOccupied) <span class="text-green-400/70">Book now &rarr;</span> @endif
            </div>
        </a>

        {{-- Countdown — featured: spans full width on mobile, taller, gold accent --}}
        <button @click="panel = panel === 'dates' ? null : 'dates'"
                :class="panel === 'dates' ? 'border-l-[#D29500] bg-white/15' : 'border-l-[#D29500]/60'"
                class="glass-card group col-span-2 flex flex-col items-center justify-center rounded-2xl border-l-4 px-5 py-6 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-l-[#D29500] hover:bg-white/15 hover:shadow-lg hover:shadow-[#D29500]/10 active:translate-y-0 sm:col-span-1">

            {{-- LIVE badge --}}
            <template x-if="live">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#D29500]/20 px-3 py-1 text-xs font-bold uppercase tracking-widest text-[#D29500]">
                        <span class="size-2 animate-ping rounded-full bg-[#D29500]"></span>
                        Live Now
                    </span>
                    <p class="mt-2 text-sm text-white/60">Happening now!</p>
                </div>
            </template>

            {{-- Countdown --}}
            <template x-if="!live">
                <div class="w-full">
                    <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-[#D29500]/60">Countdown to Expo</p>
                    <div class="flex items-end justify-center gap-2">
                        <div class="flex flex-col items-center">
                            <span class="rounded-xl bg-white/10 px-2.5 py-1.5 text-3xl font-extrabold leading-none text-white tabular-nums shadow-inner" x-text="String(d).padStart(2,'0')"></span>
                            <span class="mt-1.5 text-[9px] font-semibold uppercase tracking-wider text-white/40">days</span>
                        </div>
                        <span class="mb-5 text-xl font-bold text-[#D29500]/50">:</span>
                        <div class="flex flex-col items-center">
                            <span class="rounded-xl bg-white/10 px-2.5 py-1.5 text-3xl font-extrabold leading-none text-white tabular-nums shadow-inner" x-text="String(h).padStart(2,'0')"></span>
                            <span class="mt-1.5 text-[9px] font-semibold uppercase tracking-wider text-white/40">hrs</span>
                        </div>
                        <span class="mb-5 text-xl font-bold text-[#D29500]/50">:</span>
                        <div class="flex flex-col items-center">
                            <span class="rounded-xl bg-white/10 px-2.5 py-1.5 text-3xl font-extrabold leading-none text-white tabular-nums shadow-inner" x-text="String(m).padStart(2,'0')"></span>
                            <span class="mt-1.5 text-[9px] font-semibold uppercase tracking-wider text-white/40">min</span>
                        </div>
                        <span class="mb-5 text-xl font-bold text-[#D29500]/50">:</span>
                        <div class="flex flex-col items-center">
                            <span class="rounded-xl bg-[#D29500]/20 px-2.5 py-1.5 text-3xl font-extrabold leading-none text-[#D29500] tabular-nums shadow-inner" x-text="String(s).padStart(2,'0')"></span>
                            <span class="mt-1.5 text-[9px] font-semibold uppercase tracking-wider text-[#D29500]/50">sec</span>
                        </div>
                    </div>
                </div>
            </template>

            <span class="mt-3 text-[10px] text-white/30 opacity-0 transition-opacity group-hover:opacity-100"
                  x-text="panel === 'dates' ? '▲ Hide schedule' : '▼ See schedule'"></span>
        </button>

        <button @click="panel = panel === 'sectors' ? null : 'sectors'"
                :class="panel === 'sectors' ? 'border-l-white/50 bg-white/15' : 'border-l-white/20'"
                class="glass-card group flex flex-col items-center justify-center rounded-2xl border-l-4 p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-l-white/40 hover:bg-white/15 hover:shadow-lg active:translate-y-0">
            <p class="text-4xl font-extrabold text-white transition-transform duration-150 group-hover:scale-110">5</p>
            <p class="mt-1 text-xs font-medium text-white/60">Sectors</p>
            <span class="mt-1.5 text-[10px] text-white/40 opacity-0 transition-opacity group-hover:opacity-100"
                  x-text="panel === 'sectors' ? '▲ Hide sectors' : '▼ See sectors'"></span>
        </button>
    </div>

    {{-- Dates panel --}}
    <div x-show="panel === 'dates'"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-end="opacity-0"
         class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4 backdrop-blur-sm"
         style="display:none">
        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-white/40">Event Schedule</p>
        <div class="grid grid-cols-3 gap-2">
            @for($d = 0; $d <= $expo->start_date->diffInDays($expo->end_date); $d++)
                @php
                    $day     = $expo->start_date->copy()->addDays($d);
                    $isFirst = $d === 0;
                    $isLast  = $d === (int) $expo->start_date->diffInDays($expo->end_date);
                    $today   = now()->isSameDay($day);
                @endphp
                <div class="rounded-xl p-3 text-center {{ $today ? 'border border-[#D29500] bg-[#D29500]/20' : ($isFirst ? 'border border-white/20 bg-white/10' : ($isLast ? 'border border-green-500/30 bg-green-900/20' : 'border border-white/10 bg-white/5')) }}">
                    <p class="text-2xl font-extrabold {{ $today ? 'text-[#D29500]' : ($isLast ? 'text-green-400' : 'text-white') }}">
                        {{ $day->format('d') }}
                    </p>
                    <p class="text-xs text-white/60">{{ $day->format('D, M Y') }}</p>
                    <p class="mt-1 text-[10px] text-white/40">
                        {{ $isFirst ? 'Opening' : ($isLast ? 'Closing' : 'Day '.($d+1)) }}{{ $today ? ' &bull; Today' : '' }}
                    </p>
                </div>
            @endfor
        </div>
        <p class="mt-3 text-center text-xs text-white/40">{{ $expo->venue }}</p>
    </div>

    {{-- Sectors panel --}}
    <div x-show="panel === 'sectors'"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-end="opacity-0"
         class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4 backdrop-blur-sm"
         style="display:none">
        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-white/40">Exhibition Sectors</p>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
            @foreach(\App\Enums\StandCategory::cases() as $cat)
                @php $catCount = $expo->stands()->where('category', $cat->value)->count(); @endphp
                <a href="{{ route('floor-plan') }}"
                   class="flex flex-col items-center gap-2 rounded-xl p-3 text-center transition hover:brightness-110 active:scale-95"
                   style="background:{{ $cat->color() }}22; border:1px solid {{ $cat->color() }}44">
                    <span class="size-6 rounded-lg shadow-md" style="background:{{ $cat->color() }}"></span>
                    <span class="text-sm font-semibold leading-tight" style="color:{{ $cat->color() }}">{{ $cat->label() }}</span>
                    <span class="text-[10px] text-white/40">{{ $catCount }} {{ Str::plural('stand', $catCount) }}</span>
                </a>
            @endforeach
        </div>
        <p class="mt-3 text-center text-xs text-white/40">Tap a sector to browse available stands &rarr;</p>
    </div>

</div>
@endif

{{-- Guest of Honour --}}
@if($guest)
@php
    $guestPhoto = $guest->photo
        ? (Str::startsWith($guest->photo, 'http') ? $guest->photo : Storage::url($guest->photo))
        : null;
@endphp
<section class="mt-10">
    <h2 class="mb-4 flex items-center gap-3 text-xl font-bold text-[#D29500]">
        <span class="inline-block h-6 w-1 rounded-full bg-[#D29500]"></span>
        Guest of Honour
    </h2>
    <div class="glass-card overflow-hidden rounded-3xl">
        <div class="flex flex-col sm:flex-row">
            {{-- Photo --}}
            <div class="relative sm:w-64 sm:shrink-0">
                @if($guestPhoto)
                    <img src="{{ $guestPhoto }}" alt="{{ $guest->name }}"
                         class="h-64 w-full object-cover object-top sm:h-full">
                @else
                    <div class="flex h-64 w-full items-center justify-center bg-[#185909]/30 text-5xl font-extrabold text-[#D29500] sm:h-full">
                        {{ substr($guest->name, 0, 1) }}
                    </div>
                @endif
                {{-- Strong horizontal gradient on desktop, vertical on mobile --}}
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-[#111D02]/80 sm:bg-gradient-to-r sm:from-transparent sm:via-[#111D02]/10 sm:to-[#111D02]/90"></div>
                {{-- Gold bottom-left accent line --}}
                <div class="absolute bottom-0 left-0 h-1 w-16 bg-[#D29500]"></div>
            </div>
            {{-- Info --}}
            <div class="flex flex-col justify-center p-6 sm:p-8">
                <p class="text-2xl font-extrabold text-white sm:text-3xl">{{ $guest->name }}</p>
                @if($guest->title)
                    <p class="mt-1 text-sm font-semibold text-[#D29500]">{{ $guest->title }}</p>
                @endif
                @if($guest->organisation)
                    <p class="text-xs text-white/50">{{ $guest->organisation }}</p>
                @endif
                @if($guest->bio)
                    <p class="mt-4 text-sm leading-relaxed text-white/70">{{ $guest->bio }}</p>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- Sponsors --}}
@if($sponsors && $sponsors->isNotEmpty())
<section class="mt-10">
    <h2 class="mb-4 flex items-center gap-3 text-xl font-bold text-[#D29500]">
        <span class="inline-block h-6 w-1 rounded-full bg-[#D29500]"></span>
        Sponsors &amp; Partners
    </h2>
    <div class="flex flex-wrap gap-3">
        @foreach($sponsors as $sponsor)
            @php
                $tierColor = $sponsor->tier->color();
                $logoSrc   = $sponsor->logo
                    ? (str_starts_with($sponsor->logo, 'http') ? $sponsor->logo : Storage::url($sponsor->logo))
                    : null;
            @endphp
            <div class="glass-card flex items-center gap-3 overflow-hidden rounded-2xl px-5 py-3"
                 style="border-top: 2px solid {{ $tierColor }}40; box-shadow: 0 -1px 0 0 {{ $tierColor }}30 inset;">
                {{-- Tier colour dot --}}
                <span class="size-2 shrink-0 rounded-full" style="background: {{ $tierColor }}" title="{{ $sponsor->tier->label() }}"></span>
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" alt="{{ $sponsor->name }}"
                         class="h-9 w-auto max-w-24 object-contain">
                @endif
                <div class="min-w-0">
                    <span class="block font-semibold text-white truncate">{{ $sponsor->name }}</span>
                    <span class="text-[10px] font-medium uppercase tracking-wider" style="color: {{ $tierColor }}">{{ $sponsor->tier->label() }}</span>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- Gallery --}}
@if($gallery->isNotEmpty())
<section class="mt-10" x-data="galleryLightbox()">
    <h2 class="mb-5 flex items-center gap-3 text-xl font-bold text-[#D29500]">
        <span class="inline-block h-6 w-1 rounded-full bg-[#D29500]"></span>
        Expo Gallery
    </h2>

    {{-- Grid --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach($gallery as $index => $item)
            @php $isVideo = $item->isVideo(); @endphp

            <button
                @click="open({{ $index }})"
                class="group relative overflow-hidden rounded-2xl bg-black/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#D29500]"
                aria-label="{{ $item->caption ?? ($isVideo ? 'Play video' : 'View image') }}">

                @if($isVideo)
                    {{-- Video thumbnail: try YouTube thumbnail, else branded placeholder --}}
                    @php
                        $ytId = null;
                        if (preg_match('#youtu\.be/([a-zA-Z0-9_\-]+)#', $item->url, $m)) $ytId = $m[1];
                        elseif (preg_match('#youtube\.com/watch\?.*v=([a-zA-Z0-9_\-]+)#', $item->url, $m)) $ytId = $m[1];
                    @endphp
                    @if($ytId)
                        <img src="https://img.youtube.com/vi/{{ $ytId }}/hqdefault.jpg"
                             alt="{{ $item->caption ?? 'Video thumbnail' }}"
                             class="h-40 w-full object-cover transition duration-300 group-hover:scale-105 group-hover:brightness-75">
                    @else
                        <div class="flex h-40 w-full items-center justify-center bg-[#185909]/40 transition group-hover:brightness-75">
                            <svg class="size-12 text-[#D29500]/60" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    @endif
                    {{-- Play overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="flex size-12 items-center justify-center rounded-full bg-black/50 text-white shadow-lg transition group-hover:bg-[#D29500] group-hover:text-[#111D02]">
                            <svg class="size-6 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>
                @else
                    <img src="{{ $item->resolvedUrl() }}"
                         alt="{{ $item->caption ?? 'Gallery image' }}"
                         class="h-40 w-full object-cover transition duration-300 group-hover:scale-105 group-hover:brightness-75"
                         loading="lazy">
                    {{-- Zoom icon on hover --}}
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 transition group-hover:opacity-100">
                        <div class="flex size-10 items-center justify-center rounded-full bg-black/50 text-white shadow">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0zm-3-3v6m-3-3h6"/>
                            </svg>
                        </div>
                    </div>
                @endif

                @if($item->caption)
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent px-3 py-2 opacity-0 transition group-hover:opacity-100">
                        <p class="truncate text-xs font-medium text-white">{{ $item->caption }}</p>
                    </div>
                @endif

                {{-- Type badge --}}
                <span class="absolute right-2 top-2 rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase
                    {{ $isVideo ? 'bg-[#D29500] text-[#111D02]' : 'bg-black/40 text-white/70' }}">
                    {{ $isVideo ? 'video' : 'photo' }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- Lightbox overlay --}}
    <div x-show="isOpen"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="close()"
         @keydown.arrow-left.window="prev()"
         @keydown.arrow-right.window="next()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
         style="display:none"
         role="dialog" aria-modal="true" aria-label="Gallery lightbox">

        {{-- Backdrop click closes --}}
        <div class="absolute inset-0" @click="close()"></div>

        {{-- Content --}}
        <div class="relative z-10 flex max-h-full w-full max-w-4xl flex-col items-center gap-4"
             @click.stop>

            {{-- Close --}}
            <button @click="close()"
                    class="absolute -top-2 right-0 flex size-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
                    aria-label="Close">
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Media --}}
            <div class="w-full overflow-hidden rounded-2xl bg-black">
                @foreach($gallery as $index => $item)
                    @php $isVideo = $item->isVideo(); $embed = $isVideo ? $item->embedUrl() : null; @endphp
                    <div x-show="current === {{ $index }}" style="display:none">
                        @if($isVideo && $embed)
                            <div class="aspect-video w-full">
                                <iframe x-show="current === {{ $index }}"
                                        :src="current === {{ $index }} ? '{{ $embed }}?autoplay=1' : ''"
                                        class="h-full w-full"
                                        allow="autoplay; fullscreen"
                                        allowfullscreen
                                        frameborder="0"
                                        title="{{ $item->caption ?? 'Video' }}">
                                </iframe>
                            </div>
                        @elseif($isVideo)
                            {{-- Direct video file --}}
                            <video controls class="aspect-video w-full" src="{{ $item->resolvedUrl() }}"></video>
                        @else
                            <img src="{{ $item->resolvedUrl() }}"
                                 alt="{{ $item->caption ?? 'Gallery image' }}"
                                 class="max-h-[75vh] w-full object-contain">
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Caption + counter --}}
            <div class="flex w-full items-center justify-between px-1">
                <p class="text-sm text-white/60">
                    @foreach($gallery as $index => $item)
                        <span x-show="current === {{ $index }}" style="display:none">
                            {{ $item->caption ?? '' }}
                        </span>
                    @endforeach
                </p>
                <p class="shrink-0 text-xs text-white/40">
                    <span x-text="current + 1"></span> / {{ $gallery->count() }}
                </p>
            </div>

            {{-- Prev / Next --}}
            @if($gallery->count() > 1)
            <div class="absolute inset-y-0 left-0 flex items-center pl-2">
                <button @click="prev()"
                        class="flex size-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
                        aria-label="Previous">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                <button @click="next()"
                        class="flex size-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
                        aria-label="Next">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            @endif
        </div>
    </div>
</section>

<script>
function galleryLightbox() {
    return {
        isOpen: false,
        current: 0,
        total: {{ $gallery->count() }},
        open(index) { this.current = index; this.isOpen = true; document.body.style.overflow = 'hidden'; },
        close()     { this.isOpen = false; document.body.style.overflow = ''; },
        prev()      { this.current = (this.current - 1 + this.total) % this.total; },
        next()      { this.current = (this.current + 1) % this.total; },
    };
}
</script>
@endif

{{-- Past Expos --}}
@if($archives->isNotEmpty())
<section class="mt-10">
    <h2 class="mb-5 flex items-center gap-3 text-xl font-bold text-[#D29500]">
        <span class="inline-block h-6 w-1 rounded-full bg-[#D29500]"></span>
        Past Expos
    </h2>
    <div class="space-y-3">
        @foreach($archives as $arch)
        @php
            $archGuest   = $arch->guestOfHonor;
            $archGallery = $arch->galleryItems;
            $archPhoto   = $archGuest?->photo
                ? (Str::startsWith($archGuest->photo, 'http') ? $archGuest->photo : Storage::url($archGuest->photo))
                : null;
        @endphp

        <div class="glass-card overflow-hidden rounded-2xl"
             x-data="{ open: false }">

            {{-- ── Clickable header row ── --}}
            <button
                @click="open = !open"
                class="flex w-full items-stretch text-left transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#D29500]"
                :aria-expanded="open"
            >
                {{-- Guest photo or year badge --}}
                <div class="relative shrink-0 w-36 sm:w-44">
                    @if($archPhoto)
                        <img src="{{ $archPhoto }}" alt="{{ $archGuest->name }}"
                             class="h-full w-full object-cover object-top" style="min-height:120px">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent"></div>
                        <span class="absolute bottom-2 left-2 rounded-lg bg-[#D29500] px-2 py-0.5 text-sm font-extrabold text-[#111D02]">
                            {{ $arch->year }}
                        </span>
                    @else
                        <div class="flex min-h-[120px] h-full w-full items-center justify-center bg-[#185909]/30">
                            <span class="text-4xl font-extrabold text-[#D29500]">{{ $arch->year }}</span>
                        </div>
                    @endif
                </div>

                {{-- Details --}}
                <div class="flex flex-1 flex-col justify-center p-5">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-base font-extrabold text-white sm:text-lg">{{ $arch->name }}</p>
                        {{-- Chevron + gallery count badge --}}
                        <div class="flex shrink-0 items-center gap-2 pt-0.5">
                            @if($archGallery->isNotEmpty())
                                <span class="rounded-full bg-[#D29500]/20 px-2 py-0.5 text-[10px] font-bold text-[#D29500]">
                                    {{ $archGallery->count() }} photos
                                </span>
                            @endif
                            <svg class="size-4 shrink-0 text-white/40 transition-transform duration-300"
                                 :class="open ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    @if($arch->theme)
                        <blockquote class="mt-2 border-l-2 border-[#D29500]/50 pl-3">
                            <p class="text-sm italic text-[#D29500]/80">&ldquo;{{ $arch->theme }}&rdquo;</p>
                        </blockquote>
                    @endif

                    @if($archGuest)
                        <span class="mt-2 text-[10px] uppercase tracking-wider text-white/40">Guest of Honour</span>
                        <p class="text-sm font-semibold text-white">{{ $archGuest->name }}</p>
                        @if($archGuest->title)
                            <p class="text-xs text-white/50">{{ $archGuest->title }}</p>
                        @endif
                    @endif

                    @if($arch->previous_winner)
                        @php
                            $wLogo  = $arch->previous_winner_logo  ? (str_starts_with($arch->previous_winner_logo,  'http') ? $arch->previous_winner_logo  : Storage::url($arch->previous_winner_logo))  : null;
                            $wImage = $arch->previous_winner_image ? (str_starts_with($arch->previous_winner_image, 'http') ? $arch->previous_winner_image : Storage::url($arch->previous_winner_image)) : null;
                        @endphp
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            @if($wLogo)
                                <img src="{{ $wLogo }}" alt="{{ $arch->previous_winner }}" class="h-7 rounded-lg object-contain bg-white/10 px-1">
                            @endif
                            @if($wImage)
                                <img src="{{ $wImage }}" alt="{{ $arch->previous_winner }}" class="h-8 w-8 rounded-full object-cover">
                            @endif
                            <div class="rounded-lg bg-[#D29500]/15 px-2.5 py-1 text-xs">
                                <span class="text-white/50">Winner:</span>
                                <span class="ml-1 font-semibold text-[#D29500]">{{ $arch->previous_winner }}</span>
                                @if($arch->previous_winner_category)
                                    <span class="ml-1 text-white/30">&bull; {{ ucfirst($arch->previous_winner_category) }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- "View Gallery" hint when closed --}}
                    @if($archGallery->isNotEmpty())
                        <p class="mt-3 text-xs text-[#D29500]/50 transition"
                           x-show="!open">
                            Click to view gallery &darr;
                        </p>
                    @endif
                </div>
            </button>

            {{-- ── Accordion gallery panel ── --}}
            @if($archGallery->isNotEmpty())
            <div
                x-show="open"
                x-transition:enter="transition duration-300 ease-out"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition duration-200 ease-in"
                x-transition:leave-end="opacity-0"
                x-data="archGallery{{ $arch->id }}()"
                class="border-t border-white/10 px-4 pb-5 pt-4"
                style="display:none"
            >
                <p class="mb-3 text-xs font-bold uppercase tracking-widest text-white/30">
                    {{ $arch->name }} Gallery
                </p>

                {{-- Masonry-style grid --}}
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($archGallery as $gi)
                    @php
                        $giIsVideo = $gi->isVideo();
                        $ytId = null;
                        if ($giIsVideo) {
                            if (preg_match('#youtu\.be/([a-zA-Z0-9_\-]+)#', $gi->url, $ym)) $ytId = $ym[1];
                            elseif (preg_match('#youtube\.com/watch\?.*v=([a-zA-Z0-9_\-]+)#', $gi->url, $ym)) $ytId = $ym[1];
                        }
                    @endphp
                    <button
                        @click="lightbox({{ $loop->index0 }})"
                        class="group relative overflow-hidden rounded-xl bg-black/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#D29500]"
                        aria-label="{{ $gi->caption ?? ($giIsVideo ? 'Play video' : 'View image') }}"
                    >
                        @if($giIsVideo && $ytId)
                            <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg"
                                 alt="{{ $gi->caption ?? 'Video' }}"
                                 class="h-32 w-full object-cover transition duration-300 group-hover:scale-105 group-hover:brightness-75">
                        @elseif($giIsVideo)
                            <div class="flex h-32 w-full items-center justify-center bg-[#185909]/40 transition group-hover:brightness-75">
                                <svg class="size-10 text-[#D29500]/60" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        @else
                            <img src="{{ $gi->resolvedUrl() }}"
                                 alt="{{ $gi->caption ?? 'Gallery image' }}"
                                 class="h-32 w-full object-cover transition duration-300 group-hover:scale-105 group-hover:brightness-75"
                                 loading="lazy">
                        @endif

                        {{-- Play icon overlay for videos --}}
                        @if($giIsVideo)
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="flex size-10 items-center justify-center rounded-full bg-black/50 text-white shadow transition group-hover:bg-[#D29500] group-hover:text-[#111D02]">
                                    <svg class="size-5 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                        @endif

                        @if($gi->caption)
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent px-2 py-1.5 opacity-0 transition group-hover:opacity-100">
                                <p class="truncate text-[10px] font-medium text-white">{{ $gi->caption }}</p>
                            </div>
                        @endif

                        <span class="absolute right-1.5 top-1.5 rounded px-1.5 py-0.5 text-[9px] font-bold uppercase
                            {{ $giIsVideo ? 'bg-[#D29500] text-[#111D02]' : 'bg-black/40 text-white/60' }}">
                            {{ $giIsVideo ? 'video' : 'photo' }}
                        </span>
                    </button>
                    @endforeach
                </div>

                {{-- Inline lightbox --}}
                <div x-show="lb.open"
                     x-transition:enter="transition duration-200 ease-out"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition duration-150 ease-in"
                     x-transition:leave-end="opacity-0"
                     @keydown.escape.window="lb.open && (lb.open = false)"
                     @keydown.arrow-left.window="lb.open && prev()"
                     @keydown.arrow-right.window="lb.open && next()"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                     style="display:none"
                     role="dialog" aria-modal="true">
                    <div class="absolute inset-0" @click="lb.open = false"></div>
                    <div class="relative z-10 flex max-h-full w-full max-w-4xl flex-col items-center gap-3" @click.stop>
                        <button @click="lb.open = false"
                                class="absolute -top-1 right-0 flex size-8 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
                                aria-label="Close">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <div class="w-full overflow-hidden rounded-2xl bg-black">
                            @foreach($archGallery as $gi)
                            @php $giIsVideo = $gi->isVideo(); $embed = $giIsVideo ? $gi->embedUrl() : null; @endphp
                            <div x-show="lb.current === {{ $loop->index0 }}" style="display:none">
                                @if($giIsVideo && $embed)
                                    <div class="aspect-video w-full">
                                        <iframe :src="lb.current === {{ $loop->index0 }} ? '{{ $embed }}?autoplay=1' : ''"
                                                class="h-full w-full" allow="autoplay; fullscreen" allowfullscreen frameborder="0"
                                                title="{{ $gi->caption ?? 'Video' }}"></iframe>
                                    </div>
                                @elseif($giIsVideo)
                                    <video controls class="aspect-video w-full" src="{{ $gi->resolvedUrl() }}"></video>
                                @else
                                    <img src="{{ $gi->resolvedUrl() }}" alt="{{ $gi->caption ?? '' }}"
                                         class="max-h-[75vh] w-full object-contain">
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <div class="flex w-full items-center justify-between px-1">
                            <p class="text-sm text-white/60">
                                @foreach($archGallery as $gi)
                                    <span x-show="lb.current === {{ $loop->index0 }}" style="display:none">{{ $gi->caption ?? '' }}</span>
                                @endforeach
                            </p>
                            <p class="text-xs text-white/40"><span x-text="lb.current + 1"></span> / {{ $archGallery->count() }}</p>
                        </div>
                        @if($archGallery->count() > 1)
                        <div class="absolute inset-y-0 left-0 flex items-center pl-1">
                            <button @click="prev()" class="flex size-8 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Previous">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                        </div>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-1">
                            <button @click="next()" class="flex size-8 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Next">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <script>
            function archGallery{{ $arch->id }}() {
                return {
                    lb: { open: false, current: 0 },
                    total: {{ $archGallery->count() }},
                    lightbox(i) { this.lb.current = i; this.lb.open = true; document.body.style.overflow = 'hidden'; },
                    prev() { this.lb.current = (this.lb.current - 1 + this.total) % this.total; },
                    next() { this.lb.current = (this.lb.current + 1) % this.total; },
                };
            }
            </script>
            @else
            {{-- No gallery — show a placeholder when expanded --}}
            <div x-show="open" x-transition class="border-t border-white/10 px-5 py-6 text-center text-sm text-white/30" style="display:none">
                No gallery items for this expo yet.
            </div>
            @endif

        </div>{{-- end glass-card --}}
        @endforeach
    </div>
</section>
@endif

@endsection