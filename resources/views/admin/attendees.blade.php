@extends('layouts.admin')
@section('title', 'Attendees')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Attendees</h1>
            <p class="text-gray-400 text-sm">
                IDIEXPO {{ $expo?->year }} &nbsp;·&nbsp;
                {{ $attendees->total() }} registered,
                {{ $attendees->where('checked_in', true)->count() }} checked in
            </p>
        </div>
        <a href="{{ route('attend') }}" target="_blank"
           class="px-4 py-2 rounded-lg text-sm font-semibold"
           style="background:rgba(24,89,9,0.3);border:1px solid #185909;color:#4ade80">
            Registration Form ↗
        </a>
    </div>

    {{-- Table --}}
    <div class="rounded-xl overflow-hidden" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08)">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:rgba(24,89,9,0.2);border-bottom:1px solid rgba(255,255,255,0.08)">
                    <th class="text-left px-4 py-3 text-gray-400 font-medium">#</th>
                    <th class="text-left px-4 py-3 text-gray-400 font-medium">Name</th>
                    <th class="text-left px-4 py-3 text-gray-400 font-medium">Organisation</th>
                    <th class="text-left px-4 py-3 text-gray-400 font-medium">Contact</th>
                    <th class="text-left px-4 py-3 text-gray-400 font-medium">Reg Number</th>
                    <th class="text-left px-4 py-3 text-gray-400 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-gray-400 font-medium">Registered</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendees as $i => $a)
                <tr class="border-b border-white border-opacity-5 hover:bg-white hover:bg-opacity-5 transition-colors">
                    <td class="px-4 py-3 text-gray-500">{{ $attendees->firstItem() + $i }}</td>
                    <td class="px-4 py-3 text-white font-medium">{{ $a->name }}</td>
                    <td class="px-4 py-3 text-gray-400">{{ $a->organisation ?? '–' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">
                        <div>{{ $a->phone }}</div>
                        @if($a->email)<div class="text-gray-500">{{ $a->email }}</div>@endif
                    </td>
                    <td class="px-4 py-3 font-mono text-xs font-bold" style="color:#D29500">
                        {{ $a->registration_number }}
                    </td>
                    <td class="px-4 py-3">
                        @if($a->checked_in)
                        <span class="px-2 py-1 rounded-full text-xs font-bold"
                              style="background:rgba(24,89,9,0.3);color:#4ade80;border:1px solid #185909">
                            Checked In
                        </span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs font-bold"
                              style="background:rgba(255,255,255,0.05);color:#9ca3af;border:1px solid rgba(255,255,255,0.1)">
                            Not Yet
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $a->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ $a->verifyUrl() }}" target="_blank"
                           class="px-3 py-1 rounded text-xs font-medium text-gray-400 hover:text-white transition-colors"
                           style="background:rgba(255,255,255,0.05)">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                        No attendees registered yet.
                        <a href="{{ route('attend') }}" class="text-green-400 underline ml-1">Share the registration link</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($attendees->hasPages())
    <div>{{ $attendees->links() }}</div>
    @endif
</div>
@endsection
