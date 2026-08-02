<?php

use App\Models\Booking;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function bookings()
    {
        return Booking::with(['stand', 'expo'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }
};
?>

<div class="space-y-3">
    @forelse($this->bookings as $booking)
        <div class="glass-card rounded-2xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-bold text-[#D29500]">
                        Stand {{ $booking->stand?->stand_number ?? '—' }}
                        <span class="ml-1 text-xs font-normal text-white/50">{{ $booking->stand?->size?->label() }}</span>
                    </p>
                    <p class="text-sm text-white/70">{{ $booking->company_name }}</p>
                    <p class="text-xs text-white/40">{{ $booking->expo?->name }}</p>
                </div>
                <div class="text-right shrink-0">
                    <span class="inline-block rounded-full px-3 py-0.5 text-xs font-semibold {{ $booking->status->badgeClass() }} text-white">
                        {{ $booking->status->label() }}
                    </span>
                    @if($booking->payment_verified)
                        <p class="mt-1 text-xs text-green-400">✓ Payment confirmed</p>
                    @else
                        <p class="mt-1 text-xs text-white/30">Payment pending</p>
                    @endif
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-3 border-t border-white/10 pt-3 text-xs text-white/50">
                <span>{{ $booking->category->label() }}</span>
                <span>via {{ ucfirst($booking->source) }}</span>
                <span>{{ $booking->created_at->format('d M Y') }}</span>
            </div>
        </div>
    @empty
        <div class="glass-card rounded-2xl p-8 text-center text-white/40">
            <p class="text-4xl mb-3">🏢</p>
            <p>You have no bookings yet.</p>
            <a href="{{ route('floor-plan') }}" class="btn-primary mt-4 inline-block text-sm">Browse Available Stands</a>
        </div>
    @endforelse
</div>
