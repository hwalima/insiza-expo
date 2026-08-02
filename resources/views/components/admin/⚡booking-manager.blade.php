<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $filterStatus = '';
    public string $search       = '';

    #[Computed]
    public function bookings()
    {
        return Booking::with(['stand', 'user'])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('company_name', 'like', "%{$this->search}%")
                  ->orWhere('contact_email', 'like', "%{$this->search}%")
                  ->orWhere('contact_phone', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(20);
    }

    public function approve(int $id): void
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => BookingStatus::Approved, 'approved_at' => now()]);
        $booking->stand->update(['status' => \App\Enums\StandStatus::Occupied,
            'exhibitor_name' => $booking->company_name]);
        unset($this->bookings);
    }

    public function reject(int $id): void
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => BookingStatus::Rejected]);
        // Free the stand back to available if no other approved booking exists
        $stand = $booking->stand;
        if (! $stand->bookings()->where('status', BookingStatus::Approved)->exists()) {
            $stand->update(['status' => \App\Enums\StandStatus::Available]);
        }
        unset($this->bookings);
    }

    public function togglePayment(int $id): void
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['payment_verified' => ! $booking->payment_verified]);
        unset($this->bookings);
    }
};
?>

<div>
    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap gap-2">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search company, email, phone..." class="glass-input flex-1 min-w-48">
        <select wire:model.live="filterStatus" class="glass-select">
            <option value="">All Statuses</option>
            @foreach(\App\Enums\BookingStatus::cases() as $bs)
                <option value="{{ $bs->value }}">{{ $bs->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm">
        <table class="w-full text-sm text-white/80">
            <thead class="border-b border-white/10 text-xs uppercase text-white/50">
                <tr>
                    <th class="px-4 py-3 text-left">Stand</th>
                    <th class="px-4 py-3 text-left">Company</th>
                    <th class="px-4 py-3 text-left">Contact</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Payment</th>
                    <th class="px-4 py-3 text-left">Source</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($this->bookings as $booking)
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 font-bold text-gold">{{ $booking->stand?->stand_number ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-white">{{ $booking->company_name }}</p>
                            <p class="text-xs text-white/50">{{ $booking->contact_person }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <p>{{ $booking->contact_email }}</p>
                            <p>{{ $booking->contact_phone }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $booking->category->label() }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $booking->status->badgeClass() }} text-white">
                                {{ $booking->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="togglePayment({{ $booking->id }})"
                                    class="{{ $booking->payment_verified ? 'text-green-400' : 'text-white/30' }} text-lg"
                                    title="Toggle payment">
                                {{ $booking->payment_verified ? '✓' : '○' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-xs uppercase">{{ $booking->source }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($booking->status === \App\Enums\BookingStatus::Pending)
                                <button wire:click="approve({{ $booking->id }})" class="text-xs text-green-400 hover:text-green-300 mr-2">Approve</button>
                                <button wire:click="reject({{ $booking->id }})"  class="text-xs text-red-400 hover:text-red-300">Reject</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-white/30">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $this->bookings->links() }}</div>
</div>
