<?php

use App\Enums\BookingStatus;
use App\Enums\StandCategory;
use App\Enums\StandSize;
use App\Enums\StandStatus;
use App\Models\Booking;
use App\Models\Expo;
use App\Models\FloorArea;
use App\Models\Stand;
use Livewire\Attributes\Computed;
use Livewire\Component;

const PUB_CELL  = 48;
const PUB_COLS  = 22;
const PUB_ROWS  = 16;

new class extends Component
{
    public ?int   $selectedStandId = null;
    public bool   $showBookingForm = false;
    public string $filterCategory  = '';
    public string $filterStatus    = '';

    public string $company_name    = '';
    public string $contact_person  = '';
    public string $contact_email   = '';
    public string $contact_phone   = '';
    public string $category        = '';
    public string $description     = '';

    #[Computed]
    public function expo(): ?Expo
    {
        return Expo::active();
    }

    #[Computed]
    public function stands()
    {
        return Stand::where('expo_id', $this->expo?->id)
            ->where('is_placed', true)
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterStatus,   fn($q) => $q->where('status',   $this->filterStatus))
            ->get();
    }

    #[Computed]
    public function areas()
    {
        return FloorArea::where('expo_id', $this->expo?->id)->get();
    }

    #[Computed]
    public function selectedStand(): ?Stand
    {
        return $this->selectedStandId ? Stand::find($this->selectedStandId) : null;
    }

    public function selectStand(int $id): void
    {
        $this->selectedStandId = $id;
        $this->showBookingForm = false;
        $this->resetBookingForm();
        $this->dispatch('open-stand-modal');
    }

    public function openBookingForm(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));
            return;
        }
        $this->showBookingForm = true;
        if (auth()->user()) {
            $this->contact_person = auth()->user()->name;
            $this->contact_email  = auth()->user()->email;
            $this->contact_phone  = auth()->user()->phone ?? '';
            $this->company_name   = auth()->user()->company ?? '';
        }
    }

    public function submitBooking(): void
    {
        $this->validate([
            'company_name'   => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_email'  => 'required|email|max:255',
            'contact_phone'  => 'required|string|max:30',
            'category'       => 'required|string',
            'description'    => 'nullable|string|max:1000',
        ]);

        $stand = Stand::findOrFail($this->selectedStandId);

        if ($stand->status !== StandStatus::Available) {
            $this->addError('stand', 'This stand is no longer available.');
            return;
        }

        Booking::create([
            'stand_id'       => $stand->id,
            'user_id'        => auth()->id(),
            'expo_id'        => $stand->expo_id,
            'company_name'   => $this->company_name,
            'contact_person' => $this->contact_person,
            'contact_email'  => $this->contact_email,
            'contact_phone'  => $this->contact_phone,
            'category'       => $this->category,
            'description'    => $this->description,
            'status'         => BookingStatus::Pending,
            'source'         => 'web',
        ]);

        $stand->update(['status' => StandStatus::Reserved]);

        session()->flash('booking_success', 'Booking request submitted! We will review and confirm shortly.');
        $this->showBookingForm = false;
        $this->selectedStandId = null;
        unset($this->stands);
        $this->dispatch('close-stand-modal');
    }

    private function resetBookingForm(): void
    {
        $this->company_name = $this->contact_person = $this->contact_email =
            $this->contact_phone = $this->category = $this->description = '';
    }
};
?>

<div x-data="{ modalOpen: false }" @open-stand-modal.window="modalOpen = true" @close-stand-modal.window="modalOpen = false">

    @if(session('booking_success'))
        <div class="mb-4 rounded-xl border border-green-500/30 bg-green-900/30 px-4 py-3 text-sm text-green-300">
            {{ session('booking_success') }}
        </div>
    @endif

    @if(! $this->expo?->is_layout_published)
        <div class="flex h-64 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-white/40">
            Floor plan is being prepared. Check back soon.
        </div>
    @else

    {{-- Filters --}}
    {{-- ── Filters ─────────────────────────────────────────── --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:flex sm:flex-wrap">
        <select wire:model.live="filterCategory" class="glass-select col-span-1 w-full text-sm">
            <option value="">All Categories</option>
            @foreach(StandCategory::cases() as $cat)
                <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterStatus" class="glass-select col-span-1 w-full text-sm">
            <option value="">All Statuses</option>
            @foreach(StandStatus::cases() as $st)
                <option value="{{ $st->value }}">{{ $st->label() }}</option>
            @endforeach
        </select>
    </div>

    {{-- ── Legend ───────────────────────────────────────────── --}}
    <div class="mb-5 space-y-3 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">

        {{-- Categories --}}
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-widest text-white/40">Stand Categories</p>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:gap-2">
                @foreach(StandCategory::cases() as $cat)
                    <div class="flex items-center gap-2 rounded-lg px-3 py-2"
                         style="background:{{ $cat->color() }}22; border:1px solid {{ $cat->color() }}55">
                        <span class="h-4 w-4 shrink-0 rounded" style="background:{{ $cat->color() }}"></span>
                        <span class="text-sm font-semibold" style="color:{{ $cat->color() }}">{{ $cat->label() }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-white/10"></div>

        {{-- Status --}}
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-widest text-white/40">Booking Status</p>
            <div class="flex flex-wrap gap-2">
                @foreach(StandStatus::cases() as $st)
                    <div class="flex items-center gap-2 rounded-lg px-3 py-2"
                         style="background:{{ $st->color() }}18; border:1px solid {{ $st->color() }}55">
                        <span class="h-4 w-4 shrink-0 rounded-full" style="background:{{ $st->color() }}"></span>
                        <span class="text-sm font-semibold" style="color:{{ $st->color() }}">{{ $st->label() }}</span>
                        <span class="text-xs text-white/40">
                            @if($st === StandStatus::Available) — click to book
                            @elseif($st === StandStatus::Reserved) — pending approval
                            @else — not available
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tip --}}
        <p class="text-xs text-white/30">
            💡 Tap any stand on the map to view details and submit a booking request.
        </p>
    </div>

    {{-- Canvas --}}
    <div class="overflow-auto rounded-2xl border border-white/15">
        <div
            class="relative"
            style="
                width:  {{ PUB_COLS * PUB_CELL }}px;
                height: {{ PUB_ROWS * PUB_CELL }}px;
                background-color: #192b1b;
                background-image:
                    linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
                background-size: {{ PUB_CELL }}px {{ PUB_CELL }}px;
            "
        >
            @if($this->expo->floor_plan_image)
                <img src="{{ Storage::url($this->expo->floor_plan_image) }}"
                     class="pointer-events-none absolute inset-0 h-full w-full select-none object-contain opacity-25"
                     draggable="false" alt="Floor plan">
            @endif

            <div class="pointer-events-none absolute bottom-1 right-2 select-none text-[10px] text-white/15">
                {{ PUB_COLS * 1.5 }}m × {{ PUB_ROWS * 1.5 }}m
            </div>

            {{-- Non-bookable zones (behind stands) --}}
            @foreach($this->areas as $area)
                <div
                    class="pointer-events-none absolute flex flex-col items-center justify-center rounded text-center select-none"
                    style="left:{{ $area->grid_x * PUB_CELL }}px; top:{{ $area->grid_y * PUB_CELL }}px;
                           width:{{ $area->grid_w * PUB_CELL }}px; height:{{ $area->grid_h * PUB_CELL }}px;
                           background:{{ $area->bg_color }}bb; border:2px dashed {{ $area->bg_color }};
                           color:{{ $area->text_color }}; z-index:1;"
                >
                    <span class="text-xs font-bold uppercase tracking-widest drop-shadow">{{ $area->label }}</span>
                    <span class="text-[10px] capitalize opacity-50">{{ $area->type }}</span>
                </div>
            @endforeach

            {{-- Placed stands (z-index 2, above zones) --}}
            @foreach($this->stands as $stand)
                @php
                    $px     = $stand->grid_x * PUB_CELL;
                    $py     = $stand->grid_y * PUB_CELL;
                    $pw     = $stand->grid_w * PUB_CELL;
                    $ph     = $stand->grid_h * PUB_CELL;
                    $catC   = $stand->category->color();
                    $txtC   = $stand->category->textColor();
                    $bdrC   = $stand->status->color();
                    $cursor = $stand->status === StandStatus::Available ? 'cursor-pointer' : 'cursor-not-allowed';
                    $dimmed = $stand->status === StandStatus::Occupied   ? 'opacity-50' : '';
                @endphp
                <button
                    wire:click="selectStand({{ $stand->id }})"
                    class="group absolute flex flex-col items-center justify-center overflow-hidden rounded text-center shadow-md transition-transform duration-100 hover:scale-[1.03] hover:z-20 focus:outline-none {{ $cursor }} {{ $dimmed }}"
                    style="left:{{ $px }}px; top:{{ $py }}px; width:{{ $pw }}px; height:{{ $ph }}px;
                           background-color:{{ $catC }}dd; border:2px solid {{ $bdrC }};
                           color:{{ $txtC }}; z-index:2;"
                    title="{{ $stand->stand_number }} — {{ $stand->size->label() }} · {{ $stand->category->label() }} · {{ $stand->status->label() }}"
                >
                    {{-- Status dot top-right --}}
                    <span class="absolute right-1 top-1 size-2 rounded-full" style="background:{{ $bdrC }}"></span>

                    <span class="text-xs font-extrabold leading-tight drop-shadow">{{ $stand->stand_number }}</span>
                    @if($stand->exhibitor_name)
                        <span class="mt-0.5 max-w-full truncate px-1 text-[9px] opacity-80">{{ $stand->exhibitor_name }}</span>
                    @else
                        <span class="text-[9px] opacity-60">{{ $stand->size->label() }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    @endif

    {{-- Stand detail + booking modal --}}
    <div x-show="modalOpen" x-transition class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center" style="display:none">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="modalOpen=false"></div>
        <div class="glass-card relative w-full max-w-md rounded-3xl p-6 shadow-2xl">

            @if($this->selectedStand)
                <button @click="modalOpen=false" class="absolute right-4 top-4 text-xl text-white/50 hover:text-white leading-none">&times;</button>

                {{-- Stand header with category color strip --}}
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-lg font-extrabold"
                         style="background:{{ $this->selectedStand->category->color() }}33;
                                border:2px solid {{ $this->selectedStand->status->color() }};
                                color:{{ $this->selectedStand->category->color() }}">
                        {{ $this->selectedStand->stand_number }}
                    </div>
                    <div>
                        <p class="font-bold text-white">{{ $this->selectedStand->size->label() }}</p>
                        <p class="text-sm" style="color:{{ $this->selectedStand->category->color() }}">
                            {{ $this->selectedStand->category->label() }}
                        </p>
                    </div>
                    <div class="ml-auto text-right">
                        <span class="inline-flex items-center rounded-full px-3 py-0.5 text-xs font-semibold text-white"
                              style="background:{{ $this->selectedStand->status->color() }}44; border:1px solid {{ $this->selectedStand->status->color() }}">
                            {{ $this->selectedStand->status->label() }}
                        </span>
                        @if($this->selectedStand->price > 0)
                            <p class="mt-1 text-sm font-bold text-[#D29500]">${{ number_format($this->selectedStand->price, 2) }}</p>
                        @endif
                    </div>
                </div>

                @if($this->selectedStand->section)
                    <p class="mb-3 text-sm text-white/50">Section: {{ $this->selectedStand->section }}</p>
                @endif

                @if($this->selectedStand->exhibitor_name)
                    <p class="mb-3 text-sm font-semibold text-white">Booked by: {{ $this->selectedStand->exhibitor_name }}</p>
                @endif

                @if(! $this->showBookingForm)
                    @if($this->selectedStand->status === StandStatus::Available)
                        <button wire:click="openBookingForm" class="btn-primary w-full mt-2">Book This Stand</button>
                    @endif
                @else
                    <form wire:submit="submitBooking" class="mt-2 space-y-3">
                        @error('stand') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        <input wire:model="company_name"   type="text"  placeholder="Company / Organisation" class="glass-input" required>
                        @error('company_name') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        <input wire:model="contact_person" type="text"  placeholder="Contact Person"         class="glass-input" required>
                        <input wire:model="contact_email"  type="email" placeholder="Email Address"           class="glass-input" required>
                        <input wire:model="contact_phone"  type="tel"   placeholder="Phone Number"            class="glass-input" required>
                        <select wire:model="category" class="glass-select w-full" required>
                            <option value="">Select your business category</option>
                            @foreach(StandCategory::cases() as $cat)
                                <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                            @endforeach
                        </select>
                        @error('category') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                        <textarea wire:model="description" rows="3" placeholder="Brief description of your business…" class="glass-input resize-none"></textarea>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-primary flex-1">Submit Request</button>
                            <button type="button" wire:click="$set('showBookingForm',false)" class="btn-ghost flex-1">Cancel</button>
                        </div>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>