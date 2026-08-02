@extends('layouts.public')
@section('title', 'Welcome')

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden rounded-3xl px-6 py-16 text-center sm:py-24">
    <div class="pointer-events-none absolute inset-0 rounded-3xl bg-gradient-to-br from-[#185909]/40 via-transparent to-[#D29500]/10"></div>

    @if($expo)
        <p class="relative mb-2 text-sm font-semibold uppercase tracking-widest text-[#D29500]">
            {{ $expo->start_date->format('d M') }} &ndash; {{ $expo->end_date->format('d M Y') }} &bull; {{ $expo->venue }}
        </p>
        <h1 class="relative text-4xl font-extrabold leading-tight text-white sm:text-6xl">
            {{ $expo->name }}
        </h1>
        @if($expo->theme)
            <blockquote class="relative mx-auto mt-5 max-w-2xl rounded-2xl border border-[#D29500]/20 bg-[#D29500]/10 px-6 py-3">
                <p class="text-base italic text-[#D29500] sm:text-lg">&ldquo;{{ $expo->theme }}&rdquo;</p>
            </blockquote>
        @endif
        <div class="relative mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
            <a href="{{ route('floor-plan') }}" class="btn-gold w-full sm:w-auto">Book a Stand</a>
            <a href="{{ route('about') }}"      class="btn-ghost w-full sm:w-auto">Learn More</a>
        </div>
    @else
        <h1 class="text-4xl font-extrabold text-white">Insiza District Industrial Expo</h1>
        <p class="mt-4 text-white/60">Stay tuned &mdash; the next expo is coming soon.</p>
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
           class="glass-card group flex flex-col items-center justify-center rounded-2xl p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-[#D29500]/50 hover:bg-white/15 hover:shadow-lg hover:shadow-[#D29500]/10 active:translate-y-0">
            <p class="text-4xl font-extrabold text-[#D29500] transition-transform duration-150 group-hover:scale-110">{{ $standsTotal }}</p>
            <p class="mt-1 text-xs font-medium text-white/60">Total Stands</p>
            <span class="mt-1.5 text-[10px] text-[#D29500]/50 opacity-0 transition-opacity group-hover:opacity-100">View floor plan &rarr;</span>
        </a>

        <a href="{{ route('floor-plan') }}"
           class="glass-card group flex flex-col items-center justify-center rounded-2xl p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-green-500/50 hover:bg-white/15 hover:shadow-lg hover:shadow-green-500/10 active:translate-y-0">
            <p class="text-4xl font-extrabold text-green-400 transition-transform duration-150 group-hover:scale-110">{{ $standsAvailable }}</p>
            <p class="mt-1 text-xs font-medium text-white/60">Available</p>
            <div class="mt-1.5 flex gap-2 text-[10px] opacity-0 transition-opacity group-hover:opacity-100">
                @if($standsReserved) <span class="text-amber-400">{{ $standsReserved }} reserved</span> @endif
                @if($standsOccupied) <span class="text-red-400">{{ $standsOccupied }} taken</span> @endif
                @if(! $standsReserved && ! $standsOccupied) <span class="text-green-400/70">Book now &rarr;</span> @endif
            </div>
        </a>

        <button @click="panel = panel === 'dates' ? null : 'dates'"
                :class="panel === 'dates' ? 'border-white/30 bg-white/15' : ''"
                class="glass-card group flex flex-col items-center justify-center rounded-2xl p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-white/30 hover:bg-white/15 hover:shadow-lg active:translate-y-0">

            {{-- LIVE badge --}}
            <template x-if="live">
                <div>
                    <p class="text-2xl font-extrabold text-[#D29500] animate-pulse">LIVE</p>
                    <p class="mt-0.5 text-xs font-medium text-white/60">Happening now!</p>
                </div>
            </template>

            {{-- Countdown --}}
            <template x-if="!live">
                <div class="w-full">
                    <div class="flex items-end justify-center gap-1">
                        <div class="flex flex-col items-center">
                            <span class="text-2xl font-extrabold leading-none text-white tabular-nums" x-text="String(d).padStart(2,'0')"></span>
                            <span class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider text-white/40">days</span>
                        </div>
                        <span class="mb-3 text-lg font-bold text-[#D29500]/60">:</span>
                        <div class="flex flex-col items-center">
                            <span class="text-2xl font-extrabold leading-none text-white tabular-nums" x-text="String(h).padStart(2,'0')"></span>
                            <span class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider text-white/40">hrs</span>
                        </div>
                        <span class="mb-3 text-lg font-bold text-[#D29500]/60">:</span>
                        <div class="flex flex-col items-center">
                            <span class="text-2xl font-extrabold leading-none text-white tabular-nums" x-text="String(m).padStart(2,'0')"></span>
                            <span class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider text-white/40">min</span>
                        </div>
                        <span class="mb-3 text-lg font-bold text-[#D29500]/60">:</span>
                        <div class="flex flex-col items-center">
                            <span class="text-2xl font-extrabold leading-none text-[#D29500] tabular-nums" x-text="String(s).padStart(2,'0')"></span>
                            <span class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider text-[#D29500]/50">sec</span>
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] font-medium text-white/40">Until Expo Opens</p>
                </div>
            </template>

            <span class="mt-1.5 text-[10px] text-white/30 opacity-0 transition-opacity group-hover:opacity-100"
                  x-text="panel === 'dates' ? 'Hide ^ schedule' : 'See schedule v'"></span>
        </button>

        <button @click="panel = panel === 'sectors' ? null : 'sectors'"
                :class="panel === 'sectors' ? 'border-white/30 bg-white/15' : ''"
                class="glass-card group flex flex-col items-center justify-center rounded-2xl p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-white/30 hover:bg-white/15 hover:shadow-lg active:translate-y-0">
            <p class="text-4xl font-extrabold text-white transition-transform duration-150 group-hover:scale-110">5</p>
            <p class="mt-1 text-xs font-medium text-white/60">Sectors</p>
            <span class="mt-1.5 text-[10px] text-white/40 opacity-0 transition-opacity group-hover:opacity-100"
                  x-text="panel === 'sectors' ? 'Hide ^ sectors' : 'See sectors v'"></span>
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
    <h2 class="mb-4 text-xl font-bold text-[#D29500]">Guest of Honour</h2>
    <div class="glass-card overflow-hidden rounded-3xl">
        <div class="flex flex-col sm:flex-row">
            {{-- Photo --}}
            <div class="relative sm:w-56 sm:shrink-0">
                @if($guestPhoto)
                    <img src="{{ $guestPhoto }}" alt="{{ $guest->name }}"
                         class="h-56 w-full object-cover object-top sm:h-full">
                @else
                    <div class="flex h-56 w-full items-center justify-center bg-[#185909]/30 text-5xl font-extrabold text-[#D29500] sm:h-full">
                        {{ substr($guest->name, 0, 1) }}
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-[#111D02]/60 sm:block hidden"></div>
            </div>
            {{-- Info --}}
            <div class="flex flex-col justify-center p-6">
                <p class="text-xl font-extrabold text-white sm:text-2xl">{{ $guest->name }}</p>
                @if($guest->title)
                    <p class="mt-1 text-sm font-medium text-[#D29500]">{{ $guest->title }}</p>
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
    <h2 class="mb-4 text-xl font-bold text-[#D29500]">Sponsors &amp; Partners</h2>
    <div class="flex flex-wrap gap-4">
        @foreach($sponsors as $sponsor)
            <div class="glass-card flex items-center gap-3 rounded-2xl px-5 py-4">
                @if($sponsor->logo)
                    @php $logoSrc = str_starts_with($sponsor->logo,'http') ? $sponsor->logo : Storage::url($sponsor->logo); @endphp
                    <img src="{{ $logoSrc }}" alt="{{ $sponsor->name }}" class="h-10 w-auto max-w-28 object-contain">
                @endif
                <span class="font-semibold text-white">{{ $sponsor->name }}</span>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- Past Expos --}}
@if($archives->isNotEmpty())
<section class="mt-10">
    <h2 class="mb-5 text-xl font-bold text-[#D29500]">Past Expos</h2>
    <div class="space-y-4">
        @foreach($archives as $arch)
        @php
            $archGuest = $arch->guestOfHonor;
            $archPhoto = $archGuest?->photo
                ? (Str::startsWith($archGuest->photo, 'http') ? $archGuest->photo : Storage::url($archGuest->photo))
                : null;
        @endphp
        <div class="glass-card overflow-hidden rounded-2xl">
            <div class="flex flex-col sm:flex-row">

                {{-- Guest photo or year badge --}}
                <div class="relative flex sm:w-40 sm:shrink-0">
                    @if($archPhoto)
                        <img src="{{ $archPhoto }}" alt="{{ $archGuest->name }}"
                             class="h-40 w-full object-cover object-top sm:h-full">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent"></div>
                        <span class="absolute bottom-2 left-2 rounded-lg bg-[#D29500] px-2 py-0.5 text-sm font-extrabold text-[#111D02]">
                            {{ $arch->year }}
                        </span>
                    @else
                        <div class="flex h-40 w-full items-center justify-center bg-[#185909]/30 sm:h-full">
                            <span class="text-4xl font-extrabold text-[#D29500]">{{ $arch->year }}</span>
                        </div>
                    @endif
                </div>

                {{-- Details --}}
                <div class="flex flex-1 flex-col justify-center p-5">
                    <p class="text-lg font-extrabold text-white">{{ $arch->name }}</p>

                    @if($arch->theme)
                        <blockquote class="mt-2 border-l-2 border-[#D29500]/50 pl-3">
                            <p class="text-sm italic text-[#D29500]/80">&ldquo;{{ $arch->theme }}&rdquo;</p>
                        </blockquote>
                    @endif

                    @if($archGuest)
                        <div class="mt-3 flex items-center gap-2">
                            <span class="text-xs text-white/40 uppercase tracking-wider">Guest of Honour</span>
                        </div>
                        <p class="text-sm font-semibold text-white">{{ $archGuest->name }}</p>
                        @if($archGuest->title)
                            <p class="text-xs text-white/50">{{ $archGuest->title }}</p>
                        @endif
                    @endif

                    @if($arch->previous_winner)
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            @php
                                $wLogo  = $arch->previous_winner_logo  ? (str_starts_with($arch->previous_winner_logo,  'http') ? $arch->previous_winner_logo  : Storage::url($arch->previous_winner_logo))  : null;
                                $wImage = $arch->previous_winner_image ? (str_starts_with($arch->previous_winner_image, 'http') ? $arch->previous_winner_image : Storage::url($arch->previous_winner_image)) : null;
                            @endphp
                            @if($wLogo)
                                <img src="{{ $wLogo }}" alt="{{ $arch->previous_winner }}" class="h-8 rounded-lg object-contain bg-white/10 px-1">
                            @endif
                            @if($wImage)
                                <img src="{{ $wImage }}" alt="{{ $arch->previous_winner }}" class="h-10 w-10 rounded-full object-cover">
                            @endif
                            <div class="rounded-lg bg-[#D29500]/15 px-3 py-1 text-xs">
                                <span class="text-white/50">Winner:</span>
                                <span class="ml-1 font-semibold text-[#D29500]">{{ $arch->previous_winner }}</span>
                                @if($arch->previous_winner_category)
                                    <span class="ml-1 text-white/30">&bull; {{ ucfirst($arch->previous_winner_category) }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

@endsection