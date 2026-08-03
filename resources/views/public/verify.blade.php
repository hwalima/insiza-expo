@extends('layouts.public')
@section('title', 'Verify – ' . $attendee->registration_number)

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        @if($attendee->checked_in)
        {{-- Already checked in --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4"
                 style="background:rgba(24,89,9,0.3);border:3px solid #185909">
                <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Checked In</h1>
            <p class="text-gray-400 text-sm mt-1">
                {{ $attendee->checked_in_at?->format('d M Y, H:i') }}
            </p>
        </div>
        @else
        {{-- Valid, not yet checked in --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4"
                 style="background:rgba(210,149,0,0.15);border:3px solid #D29500">
                <svg class="w-10 h-10" style="color:#D29500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Valid Registration</h1>
            <p class="text-gray-400 text-sm mt-1">Not yet checked in</p>
        </div>
        @endif

        {{-- Attendee card --}}
        <div class="rounded-2xl p-6 space-y-4"
             style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1)">

            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-widest">Name</p>
                    <p class="text-white font-bold text-xl">{{ $attendee->name }}</p>
                    @if($attendee->organisation)
                    <p class="font-semibold text-sm" style="color:#D29500">{{ $attendee->organisation }}</p>
                    @endif
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold"
                      style="{{ $attendee->checked_in ? 'background:rgba(24,89,9,0.3);color:#4ade80;border:1px solid #185909' : 'background:rgba(210,149,0,0.15);color:#D29500;border:1px solid #D29500' }}">
                    {{ $attendee->checked_in ? 'CHECKED IN' : 'NOT CHECKED IN' }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-white border-opacity-10">
                @if($attendee->phone)
                <div>
                    <p class="text-xs text-gray-500">Phone</p>
                    <p class="text-white text-sm">{{ $attendee->phone }}</p>
                </div>
                @endif
                @if($attendee->email)
                <div>
                    <p class="text-xs text-gray-500">Email</p>
                    <p class="text-white text-sm truncate">{{ $attendee->email }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs text-gray-500">Expo</p>
                    <p class="text-white text-sm">IDIEXPO {{ $attendee->expo->year ?? '2026' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Registered</p>
                    <p class="text-white text-sm">{{ $attendee->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <div class="pt-2 border-t border-white border-opacity-10">
                <p class="text-xs text-gray-500 uppercase tracking-widest mb-1">Registration Number</p>
                <p class="font-mono font-bold text-lg" style="color:#D29500">
                    {{ $attendee->registration_number }}
                </p>
            </div>
        </div>

        {{-- Admin check-in button (shown to admin users) --}}
        @auth
        @if(!$attendee->checked_in)
        <button onclick="checkIn('{{ $attendee->registration_number }}')"
                id="checkin-btn"
                class="w-full mt-4 py-3 rounded-lg font-semibold text-white"
                style="background:linear-gradient(135deg,#185909,#2d7a10)">
            ✓ Mark as Checked In
        </button>
        @endif
        @endauth

        <p class="text-center text-xs text-gray-600 mt-4">insizaexpo.online</p>
    </div>
</div>

@auth
<script>
function checkIn(code) {
    fetch(`/admin/attendees/checkin/${code}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const btn = document.getElementById('checkin-btn');
        btn.textContent = '✓ Checked In – ' + data.name;
        btn.disabled = true;
        btn.style.background = '#185909';
        location.reload();
    });
}
</script>
@endauth
@endsection
