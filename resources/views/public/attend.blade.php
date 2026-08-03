@extends('layouts.public')
@section('title', 'Attendee Registration – IDIEXPO 2026')

@section('content')
<div class="min-h-screen py-16 px-4 flex items-center justify-center">
    <div class="w-full max-w-lg">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
                 style="background:rgba(24,89,9,0.3);border:2px solid #185909">
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Attendee Registration</h1>
            @if($expo)
            <p class="text-gray-400">{{ $expo->name }}</p>
            <p class="text-sm" style="color:#D29500">
                {{ \Carbon\Carbon::parse($expo->start_date)->format('d M') }}–{{ \Carbon\Carbon::parse($expo->end_date)->format('d M Y') }}
                &nbsp;·&nbsp; {{ $expo->venue }}
            </p>
            @endif
        </div>

        {{-- Form card --}}
        <div class="rounded-2xl p-8" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);backdrop-filter:blur(12px)">
            <form method="POST" action="{{ route('attend.register') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);focus:ring-color:#185909"
                           placeholder="Your full name">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Organisation / Company</label>
                    <input type="text" name="organisation" value="{{ old('organisation') }}"
                           class="w-full rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)"
                           placeholder="Your company or organisation">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)"
                           placeholder="you@example.com">
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Phone Number <span class="text-red-400">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                           class="w-full rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none"
                           style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)"
                           placeholder="+263 77 xxx xxxx">
                    @error('phone')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-lg font-semibold text-white transition-all hover:opacity-90"
                        style="background:linear-gradient(135deg,#185909,#2d7a10)">
                    Register to Attend →
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-500 mt-4">
            You will receive a QR registration card after submitting.
        </p>
    </div>
</div>
@endsection
