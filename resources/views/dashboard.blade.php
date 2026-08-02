@extends('layouts.public')
@section('title', 'My Dashboard')

@section('content')

@php $user = auth()->user(); @endphp

{{-- ── Welcome header ────────────────────────────────────── --}}
<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-extrabold text-white">
            Welcome back, <span class="text-[#D29500]">{{ $user->name }}</span>
        </h1>
        <p class="text-sm text-white/50">
            @foreach($user->roles as $role)
                <span class="rounded-full bg-[#185909]/40 px-2 py-0.5 text-xs font-semibold text-[#D29500]">{{ str_replace('_',' ', ucfirst($role->name)) }}</span>
            @endforeach
            {{ $user->company ? ' · ' . $user->company : '' }}
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('floor-plan') }}" class="btn-primary text-sm">Browse Stands</a>
        <a href="{{ route('profile.edit') }}" class="btn-ghost text-sm">Edit Profile</a>
    </div>
</div>

{{-- ── Role-based panels ─────────────────────────────────── --}}
@if($user->hasAnyRole(['super_admin','admin']))

    {{-- Admin quick-access --}}
    <div class="mb-6 rounded-2xl border border-[#D29500]/30 bg-[#D29500]/10 p-4 text-sm text-[#D29500]">
        You have admin access.
        <a href="{{ route('admin.dashboard') }}" class="ml-2 font-bold underline hover:text-white">Go to Admin Panel →</a>
    </div>

@endif

{{-- ── My Bookings ────────────────────────────────────────── --}}
<h2 class="mb-3 text-lg font-bold text-white">My Booking Requests</h2>
@livewire('exhibitor.my-bookings')

@endsection
