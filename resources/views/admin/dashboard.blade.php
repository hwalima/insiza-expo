@extends('layouts.admin')
@section('content')

<h1 class="mb-6 text-2xl font-extrabold text-white">Dashboard</h1>

@if($expo)
    <p class="mb-4 text-sm text-[#D29500]">Active: {{ $expo->name }}</p>
@endif

<div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
    <div class="glass-card rounded-2xl p-5 text-center">
        <p class="text-4xl font-extrabold text-white">{{ $stats['total_stands'] }}</p>
        <p class="mt-1 text-xs text-white/50">Total Stands</p>
    </div>
    <div class="glass-card rounded-2xl p-5 text-center">
        <p class="text-4xl font-extrabold text-green-400">{{ $stats['available_stands'] }}</p>
        <p class="mt-1 text-xs text-white/50">Available</p>
    </div>
    <div class="glass-card rounded-2xl p-5 text-center">
        <p class="text-4xl font-extrabold text-amber-400">{{ $stats['reserved_stands'] }}</p>
        <p class="mt-1 text-xs text-white/50">Reserved</p>
    </div>
    <div class="glass-card rounded-2xl p-5 text-center">
        <p class="text-4xl font-extrabold text-red-400">{{ $stats['occupied_stands'] }}</p>
        <p class="mt-1 text-xs text-white/50">Occupied</p>
    </div>
    <div class="glass-card rounded-2xl p-5 text-center">
        <p class="text-4xl font-extrabold text-[#D29500]">{{ $stats['pending_bookings'] }}</p>
        <p class="mt-1 text-xs text-white/50">Pending Bookings</p>
    </div>
    <div class="glass-card rounded-2xl p-5 text-center">
        <p class="text-4xl font-extrabold text-white">{{ $stats['total_bookings'] }}</p>
        <p class="mt-1 text-xs text-white/50">Total Bookings</p>
    </div>
</div>

<div class="mt-8 flex flex-wrap gap-3">
    <a href="{{ route('admin.floor-plan') }}" class="btn-primary">Manage Floor Plan</a>
    <a href="{{ route('admin.bookings') }}"   class="btn-gold">Review Bookings</a>
</div>

@endsection
