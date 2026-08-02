@extends('layouts.public')
@section('title', 'Book a Stand')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-extrabold text-white">Exhibition Floor Plan</h1>
    @if($expo)
        <p class="mt-1 text-sm text-white/60">{{ $expo->name }} &bull; {{ $expo->venue }}</p>
    @endif
</div>

<livewire:floor-plan.public-floor-plan />
@endsection
