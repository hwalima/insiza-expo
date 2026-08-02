<?php

use App\Enums\StandCategory;
use App\Enums\StandSize;
use App\Enums\StandStatus;
use App\Models\Expo;
use App\Models\FloorArea;
use App\Models\Stand;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

// 1 grid cell = 1.5 m   =>  3x3m stand = 2x2 cells = 96x96 px (square)
//                        =>  6x3m stand = 4x2 cells = 192x96 px (rectangle)
const CELL  = 48;
const GCOLS = 22;
const GROWS = 16;

new class extends Component
{
    use WithFileUploads;

    public $bgImage = null;

    public bool   $showForm     = false;
    public ?int   $editingId    = null;
    public string $stand_number = '';
    public string $size         = '3x3';
    public string $category     = 'general';
    public string $status       = 'available';
    public string $price        = '0';
    public string $section      = '';

    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    // Zone drawing form
    public bool   $showAreaForm  = false;
    public string $areaLabel     = '';
    public string $areaType      = 'stage';
    public string $areaBgColor   = '#1e3a5f';
    public string $areaTxtColor  = '#93c5fd';
    public int    $pendingAreaX  = 0;
    public int    $pendingAreaY  = 0;
    public int    $pendingAreaW  = 2;
    public int    $pendingAreaH  = 2;

    #[Computed]
    public function expo(): ?Expo
    {
        return Expo::active();
    }

    #[Computed]
    public function placedStands()
    {
        return Stand::where('expo_id', $this->expo?->id)
            ->where('is_placed', true)
            ->get();
    }

    #[Computed]
    public function paletteStands()
    {
        return Stand::where('expo_id', $this->expo?->id)
            ->where('is_placed', false)
            ->orderBy('stand_number')
            ->get();
    }

    public function placeStand(int $id, int $x, int $y): void
    {
        $stand = Stand::findOrFail($id);
        $x = max(0, min($x, GCOLS - $stand->grid_w));
        $y = max(0, min($y, GROWS - $stand->grid_h));
        $stand->update(['is_placed' => true, 'grid_x' => $x, 'grid_y' => $y]);
        unset($this->placedStands, $this->paletteStands);
    }

    public function removeFromCanvas(int $id): void
    {
        Stand::findOrFail($id)->update(['is_placed' => false, 'grid_x' => 0, 'grid_y' => 0]);
        unset($this->placedStands, $this->paletteStands);
    }

    public function openCreate(): void
    {
        $this->resetStandForm();
        $this->editingId = null;
        $this->showForm  = true;
    }

    public function openEdit(int $id): void
    {
        $s = Stand::findOrFail($id);
        $this->editingId    = $id;
        $this->stand_number = $s->stand_number;
        $this->size         = $s->size->value;
        $this->category     = $s->category->value;
        $this->status       = $s->status->value;
        $this->price        = (string) $s->price;
        $this->section      = $s->section ?? '';
        $this->showForm     = true;
    }

    public function saveStand(): void
    {
        $this->validate([
            'stand_number' => 'required|string|max:20',
            'size'         => 'required|in:6x3,3x3',
            'category'     => 'required',
            'status'       => 'required',
            'price'        => 'numeric|min:0',
        ]);

        [$w, $h] = $this->size === '6x3' ? [4, 2] : [2, 2];

        $data = [
            'expo_id'      => $this->expo->id,
            'stand_number' => $this->stand_number,
            'size'         => $this->size,
            'category'     => $this->category,
            'status'       => $this->status,
            'price'        => $this->price,
            'grid_w'       => $w,
            'grid_h'       => $h,
            'section'      => $this->section ?: null,
        ];

        if ($this->editingId) {
            Stand::findOrFail($this->editingId)->update($data);
        } else {
            Stand::create($data);
        }

        $this->showForm = false;
        unset($this->placedStands, $this->paletteStands);
        session()->flash('success', 'Stand saved.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id; $this->confirmDelete = true;
    }

    public function deleteStand(): void
    {
        if ($this->deleteId) {
            Stand::findOrFail($this->deleteId)->delete();
            unset($this->placedStands, $this->paletteStands);
        }
        $this->confirmDelete = false; $this->deleteId = null;
    }

    public function uploadBg(): void
    {
        $this->validate(['bgImage' => 'image|max:8192']);
        $path = $this->bgImage->store('expo-plans', 'public');
        $this->expo->update(['floor_plan_image' => $path]);
        $this->bgImage = null;
        unset($this->expo);
        session()->flash('success', 'Background image updated.');
    }

    public function publishLayout(): void
    {
        $this->expo->update(['is_layout_published' => true]);
        unset($this->expo);
        session()->flash('success', 'Layout published! Exhibitors can now book stands.');
    }

    public function unpublishLayout(): void
    {
        $this->expo->update(['is_layout_published' => false]);
        unset($this->expo);
    }

    #[Computed]
    public function areas()
    {
        return FloorArea::where('expo_id', $this->expo?->id)->get();
    }

    /** Called by Alpine after the user draws a rectangle on the canvas */
    public function openAreaForm(int $x, int $y, int $w, int $h): void
    {
        $this->pendingAreaX = $x;
        $this->pendingAreaY = $y;
        $this->pendingAreaW = max(1, $w);
        $this->pendingAreaH = max(1, $h);
        $this->areaLabel    = '';
        $this->areaType     = 'stage';
        $this->areaBgColor  = '#1e3a5f';
        $this->areaTxtColor = '#93c5fd';
        $this->showAreaForm = true;
    }

    public function applyAreaTypePreset(string $type): void
    {
        $preset = FloorArea::$typePresets[$type] ?? FloorArea::$typePresets['other'];
        $this->areaType     = $type;
        $this->areaBgColor  = $preset['bg'];
        $this->areaTxtColor = $preset['text'];
    }

    public function saveArea(): void
    {
        $this->validate(['areaLabel' => 'required|string|max:100']);

        FloorArea::create([
            'expo_id'    => $this->expo->id,
            'label'      => $this->areaLabel,
            'type'       => $this->areaType,
            'bg_color'   => $this->areaBgColor,
            'text_color' => $this->areaTxtColor,
            'grid_x'     => $this->pendingAreaX,
            'grid_y'     => $this->pendingAreaY,
            'grid_w'     => $this->pendingAreaW,
            'grid_h'     => $this->pendingAreaH,
        ]);

        $this->showAreaForm = false;
        unset($this->areas);
    }

    public function deleteArea(int $id): void
    {
        FloorArea::findOrFail($id)->delete();
        unset($this->areas);
    }

    private function resetStandForm(): void
    {
        $this->stand_number = $this->section = '';
        $this->size = '3x3'; $this->category = 'general';
        $this->status = 'available'; $this->price = '0';
    }
};
?>

{{-- Cell=48px=1.5m | 3x3m = 2x2 cells = 96x96 square | 6x3m = 4x2 cells = 192x96 rectangle --}}
<div
    x-data="{
        dragging: null,
        cellSize: 48,
        drawMode: false,
        drawing:  false,
        drawStart: null,
        drawEnd:   null,

        startDrag(id) { if (!this.drawMode) this.dragging = id; },
        cancelDrag()  { this.dragging = null; },

        dropOnCanvas(event) {
            if (this.drawMode || this.dragging === null) return;
            const canvas = this.$refs.canvas;
            const rect   = canvas.getBoundingClientRect();
            const x = Math.max(0, Math.floor((event.clientX - rect.left  + canvas.scrollLeft) / this.cellSize));
            const y = Math.max(0, Math.floor((event.clientY - rect.top   + canvas.scrollTop)  / this.cellSize));
            this.$wire.placeStand(this.dragging, x, y);
            this.dragging = null;
        },

        startDraw(event) {
            if (!this.drawMode) return;
            event.preventDefault();
            const canvas = this.$refs.canvas;
            const rect   = canvas.getBoundingClientRect();
            const x = Math.max(0, Math.floor((event.clientX - rect.left + canvas.scrollLeft) / this.cellSize));
            const y = Math.max(0, Math.floor((event.clientY - rect.top  + canvas.scrollTop)  / this.cellSize));
            this.drawing   = true;
            this.drawStart = { x, y };
            this.drawEnd   = { x, y };
        },

        updateDraw(event) {
            if (!this.drawing) return;
            const canvas = this.$refs.canvas;
            const rect   = canvas.getBoundingClientRect();
            const x = Math.max(0, Math.floor((event.clientX - rect.left + canvas.scrollLeft) / this.cellSize));
            const y = Math.max(0, Math.floor((event.clientY - rect.top  + canvas.scrollTop)  / this.cellSize));
            this.drawEnd = { x, y };
        },

        endDraw() {
            if (!this.drawing || !this.drawStart || !this.drawEnd) return;
            this.drawing = false;
            const x1 = Math.min(this.drawStart.x, this.drawEnd.x);
            const y1 = Math.min(this.drawStart.y, this.drawEnd.y);
            const w  = Math.abs(this.drawEnd.x - this.drawStart.x) + 1;
            const h  = Math.abs(this.drawEnd.y - this.drawStart.y) + 1;
            this.drawStart = null;
            this.drawEnd   = null;
            this.$wire.openAreaForm(x1, y1, w, h);
        },

        previewStyle() {
            if (!this.drawing || !this.drawStart || !this.drawEnd) return 'display:none;pointer-events:none';
            const x1 = Math.min(this.drawStart.x, this.drawEnd.x) * this.cellSize;
            const y1 = Math.min(this.drawStart.y, this.drawEnd.y) * this.cellSize;
            const w  = (Math.abs(this.drawEnd.x - this.drawStart.x) + 1) * this.cellSize;
            const h  = (Math.abs(this.drawEnd.y - this.drawStart.y) + 1) * this.cellSize;
            return `position:absolute;left:${x1}px;top:${y1}px;width:${w}px;height:${h}px;z-index:30;pointer-events:none;border:2px dashed #fbbf24;background:rgba(251,191,36,0.18);`;
        }
    }"
    @dragend="cancelDrag"
>

@if(session('success'))
    <div class="mb-3 rounded-xl border border-green-500/30 bg-green-900/30 px-4 py-2 text-sm text-green-300">{{ session('success') }}</div>
@endif

{{-- Toolbar --}}
<div class="mb-4 flex flex-wrap items-center gap-2">
    <button wire:click="openCreate" class="btn-primary text-sm">+ Add Stand</button>

    <button
        @click="drawMode = !drawMode; drawing = false; drawStart = null; drawEnd = null"
        :class="drawMode ? 'btn-gold' : 'btn-ghost'"
        class="text-sm"
        title="Click then drag on the canvas to draw a non-bookable zone"
    >
        <span x-text="drawMode ? '✕ Exit Draw Mode' : '✏ Draw Zone'"></span>
    </button>

    <label class="btn-ghost cursor-pointer text-sm">
        📷 Set Background
        <input type="file" wire:model="bgImage" class="sr-only" accept="image/*">
    </label>
    @if($bgImage)
        <button wire:click="uploadBg" class="btn-gold text-xs">Upload</button>
    @endif

    <div class="ml-auto flex items-center gap-2">
        @if($this->expo?->is_layout_published)
            <span class="rounded-full bg-green-700/40 px-3 py-1 text-xs font-semibold text-green-300">● Published</span>
            <button wire:click="unpublishLayout" class="btn-ghost text-xs">Unpublish</button>
        @else
            <span class="rounded-full bg-amber-700/40 px-3 py-1 text-xs font-semibold text-amber-300">● Draft</span>
            <button wire:click="publishLayout" class="btn-gold text-sm">Publish Layout</button>
        @endif
    </div>
</div>

{{-- Draw mode indicator bar --}}
<div x-show="drawMode" class="mb-3 flex items-center gap-2 rounded-xl border border-amber-500/40 bg-amber-900/20 px-4 py-2 text-sm text-amber-300">
    ✏ <strong>Draw Mode</strong> — click &amp; drag on the canvas grid to define a non-bookable zone (stage, tent, entrance…)
</div>

{{-- Legend --}}
<div class="mb-3 flex flex-wrap items-center gap-4 text-xs text-white/50">
    <span><span class="mr-1 inline-block h-3 w-3 rounded-sm bg-green-600"></span>Available</span>
    <span><span class="mr-1 inline-block h-3 w-3 rounded-sm bg-amber-600"></span>Reserved</span>
    <span><span class="mr-1 inline-block h-3 w-3 rounded-sm bg-red-700"></span>Occupied</span>
    <span class="ml-2 text-white/25">3x3m = square (96px) &nbsp;|&nbsp; 6x3m = rectangle (192x96px) &nbsp;|&nbsp; Drag from palette onto canvas</span>
</div>

{{-- Two-column layout --}}
<div class="flex gap-4" style="min-height:520px">

    {{-- Palette --}}
    <div class="flex w-52 shrink-0 flex-col gap-2">
        <p class="text-xs font-bold uppercase tracking-widest text-white/40">
            Palette <span class="text-white/20">({{ $this->paletteStands->count() }})</span>
        </p>

        <div class="flex-1 space-y-2 overflow-y-auto pr-1" style="max-height:600px">
            @forelse($this->paletteStands as $stand)
                @php
                    $isLarge = $stand->size === StandSize::Large;
                    $bdColor = match($stand->status) {
                        StandStatus::Available => 'border-white/20 bg-white/5',
                        StandStatus::Reserved  => 'border-amber-500/40 bg-amber-900/20',
                        StandStatus::Occupied  => 'border-red-500/40  bg-red-900/20',
                    };
                @endphp
                <div
                    draggable="true"
                    @dragstart="startDrag({{ $stand->id }})"
                    :class="dragging === {{ $stand->id }} ? 'opacity-30' : ''"
                    class="cursor-grab rounded-xl border p-2 transition-opacity {{ $bdColor }}"
                >
                    <div class="flex items-center gap-2">
                        {{-- Shape uses category color, border uses status color --}}
                        <div class="shrink-0 rounded"
                             style="width:{{ $isLarge ? 36 : 18 }}px; height:18px;
                                    background:{{ $stand->category->color() }}99;
                                    border:1.5px solid {{ $stand->status->color() }}">
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold text-white">{{ $stand->stand_number }}</p>
                            <p class="text-[10px] text-white/40">{{ $stand->size->label() }} · {{ $stand->category->label() }}</p>
                        </div>
                        <button wire:click="openEdit({{ $stand->id }})" @click.stop class="ml-auto shrink-0 text-[10px] text-white/25 hover:text-white">✎</button>
                    </div>
                </div>
            @empty
                <p class="pt-6 text-center text-xs text-white/20">All stands placed on canvas</p>
            @endforelse
        </div>

        <div class="rounded-xl border border-white/10 bg-white/5 p-2 text-center text-[10px] text-white/30">
            {{ $this->placedStands->count() }} placed &bull; {{ $this->paletteStands->count() }} in palette
        </div>
    </div>

    {{-- Canvas --}}
    <div class="flex-1 overflow-auto rounded-2xl border border-white/20" style="max-height:680px">
        <div
            x-ref="canvas"
            class="relative"
            :style="`cursor: ${drawMode ? 'crosshair' : 'default'}; width:{{ GCOLS * CELL }}px; height:{{ GROWS * CELL }}px; background-color:#192b1b; background-image:linear-gradient(rgba(255,255,255,0.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.05) 1px,transparent 1px); background-size:{{ CELL }}px {{ CELL }}px;`"
            @dragover.prevent
            @drop.prevent="dropOnCanvas($event)"
            @mousedown="startDraw($event)"
            @mousemove="updateDraw($event)"
            @mouseup="endDraw()"
            @mouseleave="drawing && endDraw()"
        >
            @if($this->expo?->floor_plan_image)
                <img src="{{ Storage::url($this->expo->floor_plan_image) }}"
                     class="pointer-events-none absolute inset-0 h-full w-full select-none object-contain opacity-30"
                     draggable="false" alt="Floor plan reference">
            @else
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center select-none">
                    <p class="text-sm text-white/10">Drop stands here to build the floor plan</p>
                </div>
            @endif

            {{-- Canvas size label --}}
            <div class="pointer-events-none absolute bottom-1 right-2 select-none text-[10px] text-white/15">
                {{ GCOLS * 1.5 }}m × {{ GROWS * 1.5 }}m
            </div>

            {{-- Draw preview rectangle (shown while dragging in draw mode) --}}
            <div :style="previewStyle()" aria-hidden="true"></div>

            {{-- Non-bookable zones (rendered behind stands) --}}
            @foreach($this->areas as $area)
                @php
                    $zx = $area->grid_x * CELL;
                    $zy = $area->grid_y * CELL;
                    $zw = $area->grid_w * CELL;
                    $zh = $area->grid_h * CELL;
                @endphp
                <div
                    class="group absolute flex flex-col items-center justify-center rounded text-center"
                    style="left:{{ $zx }}px; top:{{ $zy }}px; width:{{ $zw }}px; height:{{ $zh }}px;
                           background:{{ $area->bg_color }}cc; border:2px dashed {{ $area->bg_color }};
                           color:{{ $area->text_color }}; z-index:1;"
                    title="{{ $area->label }} ({{ $area->type }})"
                >
                    <button
                        wire:click="deleteArea({{ $area->id }})" @click.stop
                        class="absolute -right-2 -top-2 z-10 hidden size-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white shadow group-hover:flex"
                    >×</button>
                    <span class="select-none text-xs font-bold uppercase tracking-widest drop-shadow">{{ $area->label }}</span>
                    <span class="select-none text-[10px] capitalize opacity-60">{{ $area->type }}</span>
                </div>
            @endforeach

            {{-- Placed stands: category fill + status border (z-index 2, above zones) --}}
            @foreach($this->placedStands as $stand)
                @php
                    $px   = $stand->grid_x * CELL;
                    $py   = $stand->grid_y * CELL;
                    $pw   = $stand->grid_w * CELL;
                    $ph   = $stand->grid_h * CELL;
                    $catC = $stand->category->color();
                    $txtC = $stand->category->textColor();
                    $bdrC = $stand->status->color();
                    $dimmed = $stand->status === StandStatus::Occupied ? 'opacity-60' : '';
                @endphp
                <div
                    draggable="true"
                    @dragstart="startDrag({{ $stand->id }})"
                    :class="dragging === {{ $stand->id }} ? 'opacity-25 cursor-grabbing' : 'cursor-grab'"
                    class="group absolute flex flex-col items-center justify-center overflow-hidden rounded text-center shadow-lg transition-opacity {{ $dimmed }}"
                    style="left:{{ $px }}px; top:{{ $py }}px; width:{{ $pw }}px; height:{{ $ph }}px;
                           background-color:{{ $catC }}cc; border:2px solid {{ $bdrC }}; color:{{ $txtC }};
                           z-index:2;"
                    title="{{ $stand->stand_number }} — {{ $stand->size->label() }} · {{ $stand->category->label() }}"
                >
                    <button wire:click="removeFromCanvas({{ $stand->id }})" @click.stop
                            class="absolute -right-2 -top-2 z-10 hidden size-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white shadow group-hover:flex">×</button>
                    <button wire:click="openEdit({{ $stand->id }})" @click.stop
                            class="absolute -left-2 -top-2 z-10 hidden size-5 items-center justify-center rounded-full bg-[#111D02] text-xs text-white/70 shadow group-hover:flex hover:text-white">✎</button>

                    <span class="text-sm font-extrabold leading-tight drop-shadow">{{ $stand->stand_number }}</span>
                    @if($stand->exhibitor_name)
                        <span class="max-w-full truncate px-1 text-[9px] opacity-80">{{ $stand->exhibitor_name }}</span>
                    @else
                        <span class="text-[9px] opacity-60">{{ $stand->size->label() }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
@if($showForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showForm',false)"></div>
    <div class="glass-card relative w-full max-w-md rounded-3xl p-6 shadow-2xl">
        <h3 class="mb-5 text-lg font-bold text-[#D29500]">{{ $editingId ? 'Edit Stand' : 'New Stand' }}</h3>
        <form wire:submit="saveStand" class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
                <label class="label">Stand Number</label>
                <input wire:model="stand_number" type="text" placeholder="e.g. A1" class="glass-input" required>
                @error('stand_number') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Size</label>
                <select wire:model.live="size" class="glass-select w-full">
                    @foreach(StandSize::cases() as $s)
                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                    @endforeach
                </select>
                {{-- Live shape preview --}}
                <div class="mt-2 flex items-center gap-2">
                    <div class="rounded border border-white/30 bg-[#185909]/40 transition-all duration-200"
                         style="width:{{ $size === '6x3' ? 64 : 32 }}px; height:32px"></div>
                    <span class="text-xs text-white/40">{{ $size === '6x3' ? '2:1 rectangle' : 'square' }}</span>
                </div>
            </div>
            <div>
                <label class="label">Category</label>
                <select wire:model="category" class="glass-select w-full">
                    @foreach(StandCategory::cases() as $c)
                        <option value="{{ $c->value }}">{{ $c->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Status</label>
                <select wire:model="status" class="glass-select w-full">
                    @foreach(StandStatus::cases() as $st)
                        <option value="{{ $st->value }}">{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Price (USD)</label>
                <input wire:model="price" type="number" min="0" step="0.01" class="glass-input">
            </div>
            <div class="col-span-2">
                <label class="label">Section / Hall</label>
                <input wire:model="section" type="text" placeholder="e.g. Hall A, Outdoor…" class="glass-input">
            </div>
            <div class="col-span-2 flex gap-2 pt-2">
                <button type="submit" class="btn-primary flex-1">Save Stand</button>
                @if($editingId)
                    <button type="button" wire:click="confirmDelete({{ $editingId }})"
                            class="rounded-xl border border-red-500/40 px-4 py-2 text-sm text-red-400 hover:bg-red-900/30">Delete</button>
                @endif
                <button type="button" wire:click="$set('showForm',false)" class="btn-ghost flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Delete confirm --}}
@if($confirmDelete)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="glass-card relative w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl">
        <p class="mb-4 text-white">Delete this stand? This cannot be undone.</p>
        <div class="flex gap-3">
            <button wire:click="deleteStand" class="flex-1 rounded-xl bg-red-600 py-2 text-sm font-semibold text-white hover:bg-red-500">Delete</button>
            <button wire:click="$set('confirmDelete',false)" class="btn-ghost flex-1">Cancel</button>
        </div>
    </div>
</div>
@endif

{{-- Zone / Area form modal --}}
@if($showAreaForm)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showAreaForm',false)"></div>
    <div class="glass-card relative w-full max-w-sm rounded-3xl p-6 shadow-2xl">
        <h3 class="mb-1 text-lg font-bold text-[#D29500]">Define Zone</h3>
        <p class="mb-4 text-xs text-white/40">
            {{ $pendingAreaW * 1.5 }}m × {{ $pendingAreaH * 1.5 }}m &nbsp;·&nbsp; grid ({{ $pendingAreaX }}, {{ $pendingAreaY }})
        </p>

        <div class="space-y-4">
            <div>
                <label class="label">Zone Label</label>
                <input wire:model.live="areaLabel" type="text" placeholder="e.g. Main Stage" class="glass-input" required>
                @error('areaLabel') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Zone Type</label>
                <div class="grid grid-cols-4 gap-1.5">
                    @foreach(App\Models\FloorArea::$typePresets as $type => $preset)
                        <button
                            type="button"
                            wire:click="applyAreaTypePreset('{{ $type }}')"
                            class="rounded-lg px-2 py-2 text-xs font-semibold capitalize transition {{ $areaType === $type ? 'ring-2 ring-white/60' : 'opacity-70 hover:opacity-100' }}"
                            style="background:{{ $preset['bg'] }}; color:{{ $preset['text'] }};"
                        >{{ $type }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Live preview of the zone --}}
            <div class="flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 p-4">
                <div class="flex items-center justify-center rounded-xl"
                     style="width:144px; height:64px; background:{{ $areaBgColor }}cc; border:2px dashed {{ $areaBgColor }}; color:{{ $areaTxtColor }};">
                    <div class="text-center">
                        <p class="text-sm font-bold uppercase tracking-wider">{{ $areaLabel ?: '—' }}</p>
                        <p class="text-[10px] capitalize opacity-60">{{ $areaType }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 flex gap-2">
            <button wire:click="saveArea" class="btn-primary flex-1">Add to Floor Plan</button>
            <button wire:click="$set('showAreaForm',false)" class="btn-ghost flex-1">Cancel</button>
        </div>
    </div>
</div>
@endif

</div>