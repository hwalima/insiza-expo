@extends('layouts.public')
@section('title', 'About')

@section('content')
<div class="mx-auto max-w-2xl">
    <h1 class="mb-4 text-3xl font-extrabold text-white">About the Expo</h1>

    @if($expo)
    <div class="glass-card rounded-3xl p-6 space-y-4">
        <h2 class="text-xl font-bold text-[#D29500]">{{ $expo->name }}</h2>
        <p class="text-sm text-white/60">
            <strong class="text-white">Dates:</strong>
            {{ $expo->start_date->format('d F Y') }} – {{ $expo->end_date->format('d F Y') }}
        </p>
        <p class="text-sm text-white/60">
            <strong class="text-white">Venue:</strong> {{ $expo->venue }}
        </p>
        @if($expo->theme)
        <p class="text-sm text-white/60">
            <strong class="text-white">Theme:</strong> {{ $expo->theme }}
        </p>
        @endif
        @if($expo->description)
        <p class="text-sm leading-relaxed text-white/70">{{ $expo->description }}</p>
        @endif

        @if($expo->contact_email || $expo->contact_phone)
        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-white/70">
            <p class="font-semibold text-[#D29500] mb-1">Contact Us</p>
            @if($expo->contact_email) <p>Email: {{ $expo->contact_email }}</p> @endif
            @if($expo->contact_phone) <p>Phone: {{ $expo->contact_phone }}</p> @endif
            <p class="mt-2">Or chat with our <strong class="text-white">WhatsApp AI Bot</strong> for instant assistance!</p>
        </div>
        @endif
    </div>
    @else
        <p class="text-white/60">No active expo information available.</p>
    @endif
</div>
@endsection
