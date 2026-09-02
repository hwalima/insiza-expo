<?php

use App\Models\Expo;
use App\Models\ExpoGalleryItem;
use App\Models\GuestOfHonor;
use App\Models\Sponsor;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    // ── Expo selector ─────────────────────────────────────────
    public ?int $selectedExpoId = null;
    public bool $isCreating     = false;

    // ── Expo Info tab ─────────────────────────────────────────
    public string $eName        = '';
    public string $eYear        = '';
    public string $eStartDate   = '';
    public string $eEndDate     = '';
    public string $eVenue       = '';
    public string $eTheme       = '';
    public string $eDescription = '';
    public string $eContactEmail = '';
    public string $eContactPhone = '';
    public bool   $eIsActive    = false;

    // ── Guest of Honour tab ───────────────────────────────────
    public string $gName         = '';
    public string $gTitle        = '';
    public string $gOrganisation = '';
    public string $gBio          = '';
    public string $gPhotoUrl     = '';  // external URL
    public $gPhotoFile           = null; // file upload
    public string $gPhotoMode    = 'url'; // 'url' | 'upload'

    // ── Sponsors tab ─────────────────────────────────────────
    public bool   $showSponsorForm    = false;
    public ?int   $editingSponsorId   = null;
    public string $sName     = '';
    public string $sTier     = 'gold';
    public string $sWebsite  = '';
    public string $sLogoUrl  = '';
    public $sLogoFile        = null;
    public string $sLogoMode = 'url';
    public int    $sOrder    = 0;

    // ── Previous Winner tab ───────────────────────────────────
    public string $wName     = '';
    public string $wCategory = '';
    public string $wLogoUrl  = '';
    public $wLogoFile        = null;
    public string $wLogoMode = 'url';
    public string $wImageUrl  = '';
    public $wImageFile        = null;
    public string $wImageMode = 'url';

    // ── Gallery tab ───────────────────────────────────────────
    public bool   $showGalleryForm    = false;
    public ?int   $editingGalleryId   = null;
    public string $gItemType         = 'image';  // 'image' | 'video'
    public string $gItemUrl          = '';
    public string $gItemCaption      = '';
    public int    $gItemOrder        = 0;
    public $gItemFile                = null;
    public string $gItemMode         = 'url';    // 'url' | 'upload'

    public function mount(?int $expoId = null): void
    {
        if ($expoId) {
            $expo = Expo::find($expoId);
            $this->selectedExpoId = $expoId;
            if ($expo) $this->loadExpo($expo);
            return;
        }
        // Default: load the active/latest expo
        $expo = Expo::orderByDesc('is_active')->orderByDesc('year')->first();
        if ($expo) {
            $this->selectedExpoId = $expo->id;
            $this->loadExpo($expo);
        }
    }

    public function startCreating(): void
    {
        $this->isCreating     = true;
        $this->selectedExpoId = null;
        // Clear all expo info fields for a fresh form
        $this->eName = $this->eYear = $this->eStartDate = $this->eEndDate = '';
        $this->eVenue = $this->eTheme = $this->eDescription = '';
        $this->eContactEmail = $this->eContactPhone = '';
        $this->eIsActive = false;
    }

    #[Computed]
    public function allExpos()
    {
        return Expo::orderByDesc('year')->get();
    }

    #[Computed]
    public function currentExpo(): ?Expo
    {
        return Expo::find($this->selectedExpoId);
    }

    #[Computed]
    public function sponsors()
    {
        return Sponsor::where('expo_id', $this->selectedExpoId)
            ->orderBy('sort_order')->orderBy('tier')->get();
    }

    #[Computed]
    public function galleryItems()
    {
        return ExpoGalleryItem::where('expo_id', $this->selectedExpoId)
            ->orderBy('sort_order')->orderBy('id')->get();
    }

    public function updatedSelectedExpoId(): void
    {
        $expo = Expo::find($this->selectedExpoId);
        if ($expo) $this->loadExpo($expo);
        unset($this->sponsors);
        unset($this->galleryItems);
    }

    private function loadExpo(Expo $expo): void
    {
        // Expo info
        $this->eName         = $expo->name;
        $this->eYear         = (string) $expo->year;
        $this->eStartDate    = $expo->start_date->format('Y-m-d');
        $this->eEndDate      = $expo->end_date->format('Y-m-d');
        $this->eVenue        = $expo->venue ?? '';
        $this->eTheme        = $expo->theme ?? '';
        $this->eDescription  = $expo->description ?? '';
        $this->eContactEmail = $expo->contact_email ?? '';
        $this->eContactPhone = $expo->contact_phone ?? '';
        $this->eIsActive     = $expo->is_active;

        // Guest of Honour
        $goh = $expo->guestOfHonor;
        $this->gName         = $goh?->name ?? '';
        $this->gTitle        = $goh?->title ?? '';
        $this->gOrganisation = $goh?->organisation ?? '';
        $this->gBio          = $goh?->bio ?? '';
        $this->gPhotoUrl     = ($goh?->photo && str_starts_with($goh->photo, 'http')) ? $goh->photo : '';
        $this->gPhotoFile    = null;
        $this->gPhotoMode    = 'url';

        // Previous winner
        $this->wName      = $expo->previous_winner ?? '';
        $this->wCategory  = $expo->previous_winner_category ?? '';
        $this->wLogoUrl   = ($expo->previous_winner_logo && str_starts_with($expo->previous_winner_logo, 'http')) ? $expo->previous_winner_logo : '';
        $this->wImageUrl  = ($expo->previous_winner_image && str_starts_with($expo->previous_winner_image, 'http')) ? $expo->previous_winner_image : '';
        $this->wLogoFile  = $this->wImageFile = null;
        $this->wLogoMode  = $this->wImageMode = 'url';
    }

    // ── Save methods ──────────────────────────────────────────

    public function saveExpoInfo(): void
    {
        $this->validate([
            'eName'       => 'required|string|max:255',
            'eYear'       => 'required|integer|min:2000|max:2099',
            'eStartDate'  => 'required|date',
            'eEndDate'    => 'required|date|after_or_equal:eStartDate',
            'eVenue'      => 'required|string|max:255',
        ]);

        $data = [
            'name'          => $this->eName,
            'year'          => $this->eYear,
            'start_date'    => $this->eStartDate,
            'end_date'      => $this->eEndDate,
            'venue'         => $this->eVenue,
            'theme'         => $this->eTheme,
            'description'   => $this->eDescription,
            'contact_email' => $this->eContactEmail,
            'contact_phone' => $this->eContactPhone,
            'is_active'     => $this->eIsActive,
        ];

        if ($this->isCreating || ! $this->selectedExpoId) {
            $expo = Expo::create($data);
            $this->selectedExpoId = $expo->id;
            $this->isCreating     = false;
            unset($this->allExpos);
        } else {
            Expo::findOrFail($this->selectedExpoId)->update($data);
        }

        if ($this->eIsActive) {
            Expo::where('id', '!=', $this->selectedExpoId)->update(['is_active' => false]);
            unset($this->allExpos);
        }

        unset($this->currentExpo);
        session()->flash('success', $this->isCreating ? 'New expo created.' : 'Expo info saved.');
    }

    public function saveGuestOfHonor(): void
    {
        $this->validate([
            'gName'      => 'required|string|max:255',
            'gPhotoFile' => 'nullable|image|max:4096',
        ]);

        $photo = $this->resolveMedia($this->gPhotoFile, $this->gPhotoUrl, $this->gPhotoMode, 'goh-photos');

        GuestOfHonor::updateOrCreate(
            ['expo_id' => $this->selectedExpoId],
            [
                'name'         => $this->gName,
                'title'        => $this->gTitle,
                'organisation' => $this->gOrganisation,
                'bio'          => $this->gBio,
                'photo'        => $photo,
            ]
        );

        $this->gPhotoFile = null;
        session()->flash('success', 'Guest of Honour saved.');
    }

    public function openSponsorForm(?int $id = null): void
    {
        $this->editingSponsorId = $id;
        $this->sLogoFile        = null;
        if ($id) {
            $s = Sponsor::findOrFail($id);
            $this->sName     = $s->name;
            $this->sTier     = $s->tier->value;
            $this->sWebsite  = $s->website ?? '';
            $this->sLogoUrl  = $s->logo && str_starts_with($s->logo, 'http') ? $s->logo : '';
            $this->sOrder    = $s->sort_order;
            $this->sLogoMode = 'url';
        } else {
            $this->sName = $this->sWebsite = $this->sLogoUrl = '';
            $this->sTier = 'gold'; $this->sOrder = 0; $this->sLogoMode = 'url';
        }
        $this->showSponsorForm = true;
    }

    public function saveSponsor(): void
    {
        $this->validate([
            'sName'     => 'required|string|max:255',
            'sLogoFile' => 'nullable|image|max:4096',
        ]);

        $logo = $this->resolveMedia($this->sLogoFile, $this->sLogoUrl, $this->sLogoMode, 'sponsor-logos');

        $data = [
            'expo_id'    => $this->selectedExpoId,
            'name'       => $this->sName,
            'website'    => $this->sWebsite ?: null,
            'logo'       => $logo,
            'sort_order' => $this->sOrder,
        ];

        $this->editingSponsorId
            ? Sponsor::findOrFail($this->editingSponsorId)->update($data)
            : Sponsor::create($data);

        $this->showSponsorForm = false;
        $this->sLogoFile       = null;
        unset($this->sponsors);
        session()->flash('success', 'Sponsor saved.');
    }

    public function deleteSponsor(int $id): void
    {
        Sponsor::findOrFail($id)->delete();
        unset($this->sponsors);
    }

    public function savePreviousWinner(): void
    {
        $this->validate([
            'wLogoFile'  => 'nullable|image|max:4096',
            'wImageFile' => 'nullable|image|max:8192',
        ]);

        $logo  = $this->resolveMedia($this->wLogoFile,  $this->wLogoUrl,  $this->wLogoMode,  'winner-logos');
        $image = $this->resolveMedia($this->wImageFile, $this->wImageUrl, $this->wImageMode, 'winner-images');

        Expo::findOrFail($this->selectedExpoId)->update([
            'previous_winner'          => $this->wName ?: null,
            'previous_winner_category' => $this->wCategory ?: null,
            'previous_winner_logo'     => $logo,
            'previous_winner_image'    => $image,
        ]);

        $this->wLogoFile = $this->wImageFile = null;
        session()->flash('success', 'Previous winner saved.');
    }

    // ── Gallery methods ───────────────────────────────────────

    public function openGalleryForm(?int $id = null): void
    {
        $this->editingGalleryId = $id;
        $this->gItemFile        = null;

        if ($id) {
            $item = ExpoGalleryItem::findOrFail($id);
            $this->gItemType    = $item->type;
            $this->gItemUrl     = $item->url;
            $this->gItemCaption = $item->caption ?? '';
            $this->gItemOrder   = $item->sort_order;
            $this->gItemMode    = 'url';
        } else {
            $this->gItemType    = 'image';
            $this->gItemUrl     = '';
            $this->gItemCaption = '';
            $this->gItemOrder   = $this->galleryItems->count();
            $this->gItemMode    = 'url';
        }

        $this->showGalleryForm = true;
    }

    public function saveGalleryItem(): void
    {
        $this->validate([
            'gItemUrl'  => $this->gItemMode === 'url' ? 'required|string|max:2048' : 'nullable',
            'gItemFile' => $this->gItemMode === 'upload' ? 'required|image|max:8192' : 'nullable|image|max:8192',
        ]);

        if ($this->gItemMode === 'upload' && $this->gItemFile) {
            $url = $this->gItemFile->store('gallery', 'public');
        } else {
            $url = trim($this->gItemUrl);
        }

        $data = [
            'expo_id'    => $this->selectedExpoId,
            'type'       => $this->gItemType,
            'url'        => $url,
            'caption'    => $this->gItemCaption ?: null,
            'sort_order' => $this->gItemOrder,
        ];

        $this->editingGalleryId
            ? ExpoGalleryItem::findOrFail($this->editingGalleryId)->update($data)
            : ExpoGalleryItem::create($data);

        $this->showGalleryForm = false;
        $this->gItemFile       = null;
        unset($this->galleryItems);
        session()->flash('success', 'Gallery item saved.');
    }

    public function deleteGalleryItem(int $id): void
    {
        ExpoGalleryItem::findOrFail($id)->delete();
        unset($this->galleryItems);
    }

    /** Store file or return URL depending on mode */
    private function resolveMedia($file, string $url, string $mode, string $folder): ?string
    {
        if ($mode === 'upload' && $file) {
            return $file->store($folder, 'public');
        }
        // If the user pasted a Google Images URL, extract the real imgurl parameter
        if ($url && str_contains($url, 'google.com/imgres')) {
            parse_str(parse_url($url, PHP_URL_QUERY), $params);
            $url = $params['imgurl'] ?? $url;
        }
        return $url ?: null;
    }
};
?>

<div x-data="{ tab: 'info' }">

@if(session('success'))
    <div class="mb-4 rounded-xl border border-green-500/30 bg-green-900/30 px-4 py-2 text-sm text-green-300">{{ session('success') }}</div>
@endif

{{-- Expo selector --}}
<div class="mb-6 flex flex-wrap items-center gap-3">
    @if(! $isCreating)
        <label class="label shrink-0">Managing Expo:</label>
        <select wire:model.live="selectedExpoId" class="glass-select flex-1 max-w-sm text-sm">
            @foreach($this->allExpos as $e)
                <option value="{{ $e->id }}">{{ $e->name }}{{ $e->is_active ? ' (Active)' : '' }}</option>
            @endforeach
        </select>
    @else
        <span class="rounded-xl bg-[#D29500]/20 px-3 py-1.5 text-sm font-semibold text-[#D29500]">Creating new expo</span>
    @endif
    @if($isCreating)
        <button wire:click="$set('isCreating',false); $set('selectedExpoId', {{ $this->allExpos->first()?->id ?? 'null' }})" class="btn-ghost text-sm">Cancel</button>
    @else
        <button wire:click="startCreating" class="btn-primary text-sm">+ New Expo</button>
    @endif
</div>

{{-- Tabs --}}
<div class="mb-4 flex gap-1 overflow-x-auto rounded-2xl border border-white/10 bg-white/5 p-1">
    @foreach([
        ['id' => 'info',    'label' => 'Expo Info'],
        ['id' => 'guest',   'label' => 'Guest of Honour'],
        ['id' => 'sponsors','label' => 'Sponsors & Partners'],
        ['id' => 'winner',  'label' => 'Previous Winner'],
        ['id' => 'gallery', 'label' => 'Gallery'],
    ] as $t)
        @if($t['id'] === 'info' || ! $isCreating)
        <button @click="tab = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}' ? 'bg-[#185909] text-white shadow' : 'text-white/50 hover:text-white'"
                class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition"
                {{ ($t['id'] !== 'info' && $isCreating) ? 'disabled' : '' }}>
            {{ $t['label'] }}
        </button>
        @endif
    @endforeach
    @if($isCreating)
        <span class="ml-2 self-center text-xs text-white/30">Save expo info first to unlock other tabs</span>
    @endif
</div>

{{-- ── TAB: Expo Info ──────────────────────────────────────── --}}
<div x-show="tab === 'info'" x-cloak>
    <form wire:submit="saveExpoInfo" class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="label">Expo Name</label>
                <input wire:model="eName" type="text" class="glass-input" required>
                @error('eName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Year</label>
                <input wire:model="eYear" type="number" min="2000" max="2099" class="glass-input" required>
            </div>
            <div>
                <label class="label">Venue</label>
                <input wire:model="eVenue" type="text" class="glass-input" required>
            </div>
            <div>
                <label class="label">Start Date</label>
                <input wire:model="eStartDate" type="date" class="glass-input" required>
            </div>
            <div>
                <label class="label">End Date</label>
                <input wire:model="eEndDate" type="date" class="glass-input" required>
            </div>
            <div class="sm:col-span-2">
                <label class="label">Theme</label>
                <input wire:model="eTheme" type="text" placeholder="e.g. Industrialisation for Economic Development" class="glass-input">
            </div>
            <div class="sm:col-span-2">
                <label class="label">Description</label>
                <textarea wire:model="eDescription" rows="3" class="glass-input resize-none" placeholder="Public-facing description of this expo..."></textarea>
            </div>
            <div>
                <label class="label">Contact Email</label>
                <input wire:model="eContactEmail" type="email" class="glass-input">
            </div>
            <div>
                <label class="label">Contact Phone</label>
                <input wire:model="eContactPhone" type="text" class="glass-input">
            </div>
            <div class="sm:col-span-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input wire:model="eIsActive" type="checkbox" class="size-4 rounded border-white/20 bg-white/10 text-[#185909]">
                    <span class="text-sm text-white/80">Set as Active Expo (will deactivate all others)</span>
                </label>
            </div>
        </div>
        <button type="submit" class="btn-primary">Save Expo Info</button>
    </form>
</div>

{{-- ── TAB: Guest of Honour ────────────────────────────────── --}}
<div x-show="tab === 'guest'" x-cloak>
    <form wire:submit="saveGuestOfHonor" class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="label">Full Name *</label>
                <input wire:model="gName" type="text" class="glass-input" required>
                @error('gName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Title / Position</label>
                <input wire:model="gTitle" type="text" placeholder="e.g. Minister of State..." class="glass-input">
            </div>
            <div class="sm:col-span-2">
                <label class="label">Organisation</label>
                <input wire:model="gOrganisation" type="text" class="glass-input">
            </div>
            <div class="sm:col-span-2">
                <label class="label">Biography</label>
                <textarea wire:model="gBio" rows="4" class="glass-input resize-none"></textarea>
            </div>

            {{-- Photo --}}
            <div x-data="{ mode: $wire.entangle('gPhotoMode') }" class="sm:col-span-2">
                <label class="label">Photo</label>
                <div class="mb-2 flex gap-2">
                    <button type="button" @click="mode = 'url'"
                            :class="mode === 'url' ? 'btn-primary text-xs !py-1.5' : 'btn-ghost text-xs !py-1.5'">URL</button>
                    <button type="button" @click="mode = 'upload'"
                            :class="mode === 'upload' ? 'btn-primary text-xs !py-1.5' : 'btn-ghost text-xs !py-1.5'">Upload</button>
                </div>
                <div x-show="mode === 'url'">
                    <input wire:model="gPhotoUrl" type="text" placeholder="https://example.com/photo.jpg"
                               class="glass-input" @change="extractUrl($event.target.value)">
                    <p class="mt-1 text-[10px] text-white/30">Direct image URL. Paste a Google Images link to auto-extract the real URL.</p>
                </div>
                <div x-show="mode === 'upload'">
                    <input wire:model="gPhotoFile" type="file" accept="image/*" class="glass-input">
                    @error('gPhotoFile') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                {{-- Current photo preview --}}
                @if($gPhotoUrl)
                    <div class="mt-2 flex items-center gap-3">
                        <img src="{{ $gPhotoUrl }}" class="size-16 rounded-xl object-cover" alt="preview">
                        <button type="button" wire:click="$set('gPhotoUrl','')" class="text-xs text-red-400 hover:text-red-300">Remove URL</button>
                    </div>
                @endif
            </div>
        </div>
        <button type="submit" class="btn-primary">Save Guest of Honour</button>
    </form>
</div>

{{-- ── TAB: Sponsors & Partners ────────────────────────────── --}}
<div x-show="tab === 'sponsors'" x-cloak>

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-white/50">{{ $this->sponsors->count() }} sponsor(s) for this expo</p>
        <button wire:click="openSponsorForm()" class="btn-primary text-sm">+ Add Sponsor</button>
    </div>

    <div class="space-y-2">
        @forelse($this->sponsors as $sp)
            <div class="glass-card flex items-center gap-3 rounded-2xl px-4 py-3">
                @if($sp->logo)
                    <img src="{{ str_starts_with($sp->logo,'http') ? $sp->logo : Storage::url($sp->logo) }}"
                         class="h-10 w-16 rounded-lg object-contain bg-white/10" alt="{{ $sp->name }}">
                @else
                    <div class="flex h-10 w-16 items-center justify-center rounded-lg bg-white/10 text-xs font-bold text-white/50">{{ substr($sp->name,0,2) }}</div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-white truncate">{{ $sp->name }}</p>
                </div>
                @if($sp->website)
                    <a href="{{ $sp->website }}" target="_blank" rel="noopener" class="text-xs text-[#D29500]/70 hover:text-[#D29500] hidden sm:block truncate max-w-32">{{ $sp->website }}</a>
                @endif
                <button wire:click="openSponsorForm({{ $sp->id }})" class="text-xs text-blue-400 hover:text-blue-300">Edit</button>
                <button wire:click="deleteSponsor({{ $sp->id }})" wire:confirm="Delete this sponsor?" class="text-xs text-red-400 hover:text-red-300">Delete</button>
            </div>
        @empty
            <p class="rounded-2xl border border-white/10 py-8 text-center text-sm text-white/30">No sponsors yet. Add your first sponsor.</p>
        @endforelse
    </div>

    {{-- Sponsor Form Modal --}}
    @if($showSponsorForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showSponsorForm',false)"></div>
        <div class="glass-card relative w-full max-w-md rounded-3xl p-6 shadow-2xl">
            <h3 class="mb-5 text-lg font-bold text-[#D29500]">{{ $editingSponsorId ? 'Edit Sponsor' : 'New Sponsor' }}</h3>
            <form wire:submit="saveSponsor" class="space-y-3">
                <div>
                    <label class="label">Name *</label>
                    <input wire:model="sName" type="text" class="glass-input" required>
                    @error('sName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Website</label>
                    <input wire:model="sWebsite" type="url" placeholder="https://..." class="glass-input">
                </div>
                <div>
                    <label class="label">Sort Order</label>
                    <input wire:model="sOrder" type="number" min="0" class="glass-input">
                </div>
                {{-- Logo --}}
                <div x-data="{
                    mode: $wire.entangle('sLogoMode'),
                    extractDirectUrl(val) {
                        // Pull imgurl= param out of a Google Images link
                        try {
                            const u = new URL(val);
                            const imgurl = u.searchParams.get('imgurl');
                            if (imgurl) { this.$wire.set('sLogoUrl', imgurl); return; }
                        } catch(e) {}
                    }
                }">
                    <label class="label">Logo</label>
                    <div class="mb-2 flex gap-2">
                        <button type="button" @click="mode='url'"    :class="mode==='url'    ? 'btn-primary text-xs !py-1' : 'btn-ghost text-xs !py-1'">URL</button>
                        <button type="button" @click="mode='upload'" :class="mode==='upload' ? 'btn-primary text-xs !py-1' : 'btn-ghost text-xs !py-1'">Upload</button>
                    </div>
                    <div x-show="mode==='url'">
                        <input wire:model="sLogoUrl" type="text"
                               placeholder="https://example.com/logo.png"
                               class="glass-input"
                               @change="extractDirectUrl($event.target.value)">
                        <p class="mt-1 text-[10px] text-white/30">
                            Use a <strong class="text-white/50">direct image URL</strong> (ending in .jpg/.png/.webp).
                            If you copied a Google Images link, paste it above &mdash; the direct URL will be extracted automatically.
                        </p>
                    </div>
                    <div x-show="mode==='upload'">
                        <input wire:model="sLogoFile" type="file" accept="image/*" class="glass-input">
                    </div>
                    @if($sLogoUrl)
                        <img src="{{ $sLogoUrl }}" class="mt-2 h-10 rounded-lg object-contain bg-white/10 px-2" alt="logo preview">
                    @endif
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn-primary flex-1">Save</button>
                    <button type="button" wire:click="$set('showSponsorForm',false)" class="btn-ghost flex-1">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

{{-- ── TAB: Previous Winner ─────────────────────────────────── --}}
<div x-show="tab === 'winner'" x-cloak>
    <form wire:submit="savePreviousWinner" class="space-y-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="label">Winning Company / Organisation</label>
                <input wire:model="wName" type="text" placeholder="Company name" class="glass-input">
            </div>
            <div>
                <label class="label">Category Won</label>
                <select wire:model="wCategory" class="glass-select w-full">
                    <option value="">Select category</option>
                    @foreach(\App\Enums\StandCategory::cases() as $c)
                        <option value="{{ $c->value }}" {{ $wCategory === $c->value ? 'selected' : '' }}>{{ $c->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Company Logo --}}
        <div x-data="{
                mode: $wire.entangle('wLogoMode'),
                extractUrl(val) { const m=val.match(/[?&]imgurl=([^&]+)/); if(m) $wire.set('wLogoUrl',decodeURIComponent(m[1])); }
            }">
            <label class="label">Company Logo</label>
            <div class="mb-2 flex gap-2">
                <button type="button" @click="mode='url'"    :class="mode==='url'    ? 'btn-primary text-xs !py-1.5' : 'btn-ghost text-xs !py-1.5'">URL</button>
                <button type="button" @click="mode='upload'" :class="mode==='upload' ? 'btn-primary text-xs !py-1.5' : 'btn-ghost text-xs !py-1.5'">Upload</button>
            </div>
            <div x-show="mode==='url'">
                <input wire:model="wLogoUrl" type="text" placeholder="https://example.com/logo.png"
                       class="glass-input" @change="extractUrl($event.target.value)">
                    <p class="mt-1 text-[10px] text-white/30">Direct image URL. Paste a Google Images link to auto-extract the real URL.</p>
            </div>
            <div x-show="mode==='upload'">
                <input wire:model="wLogoFile" type="file" accept="image/*" class="glass-input">
                @error('wLogoFile') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            @if($wLogoUrl)
                <img src="{{ $wLogoUrl }}" class="mt-2 h-12 rounded-xl object-contain bg-white/10 px-3 py-1" alt="logo">
            @endif
        </div>

        {{-- Winner Photo/Image --}}
        <div x-data="{
                mode: $wire.entangle('wImageMode'),
                extractUrl(val) { const m=val.match(/[?&]imgurl=([^&]+)/); if(m) $wire.set('wImageUrl',decodeURIComponent(m[1])); }
            }">
            <label class="label">Winner Photo / Image</label>
            <div class="mb-2 flex gap-2">
                <button type="button" @click="mode='url'"    :class="mode==='url'    ? 'btn-primary text-xs !py-1.5' : 'btn-ghost text-xs !py-1.5'">URL</button>
                <button type="button" @click="mode='upload'" :class="mode==='upload' ? 'btn-primary text-xs !py-1.5' : 'btn-ghost text-xs !py-1.5'">Upload</button>
            </div>
            <div x-show="mode==='url'">
                <input wire:model="wImageUrl" type="text" placeholder="https://example.com/photo.jpg"
                       class="glass-input" @change="extractUrl($event.target.value)">
                <p class="mt-1 text-[10px] text-white/30">Direct image URL. Paste a Google Images link to auto-extract the real URL.</p>
            </div>
            <div x-show="mode==='upload'">
                <input wire:model="wImageFile" type="file" accept="image/*" class="glass-input">
                @error('wImageFile') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            @if($wImageUrl)
                <img src="{{ $wImageUrl }}" class="mt-2 h-24 rounded-xl object-cover" alt="winner image">
            @endif
        </div>

        <button type="submit" class="btn-primary">Save Previous Winner</button>
    </form>
</div>

{{-- ── TAB: Gallery ──────────────────────────────────────────── --}}
<div x-show="tab === 'gallery'" x-cloak>

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-white/50">{{ $this->galleryItems->count() }} item(s) in gallery</p>
        <button wire:click="openGalleryForm()" class="btn-primary text-sm">+ Add Item</button>
    </div>

    {{-- Gallery grid --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @forelse($this->galleryItems as $item)
            @php
                $isVideo = $item->isVideo();
                $thumb   = $isVideo ? null : $item->resolvedUrl();
            @endphp
            <div class="glass-card group relative overflow-hidden rounded-2xl">

                {{-- Thumbnail / video badge --}}
                @if($isVideo)
                    <div class="flex h-28 w-full items-center justify-center bg-black/40">
                        <svg class="size-10 text-[#D29500]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                @else
                    <img src="{{ $thumb }}" alt="{{ $item->caption ?? 'Gallery image' }}"
                         class="h-28 w-full object-cover">
                @endif

                {{-- Type badge --}}
                <span class="absolute left-2 top-2 rounded-lg px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider
                    {{ $isVideo ? 'bg-[#D29500] text-[#111D02]' : 'bg-[#185909]/80 text-white' }}">
                    {{ $item->type }}
                </span>

                {{-- Caption + actions --}}
                <div class="px-3 py-2">
                    @if($item->caption)
                        <p class="truncate text-xs text-white/70">{{ $item->caption }}</p>
                    @endif
                    <p class="mt-0.5 truncate text-[10px] text-white/30">#{{ $item->sort_order }} &bull; {{ Str::limit($item->url, 30) }}</p>
                    <div class="mt-2 flex gap-2">
                        <button wire:click="openGalleryForm({{ $item->id }})"
                                class="text-xs text-blue-400 hover:text-blue-300">Edit</button>
                        <button wire:click="deleteGalleryItem({{ $item->id }})"
                                wire:confirm="Remove this gallery item?"
                                class="text-xs text-red-400 hover:text-red-300">Delete</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-white/10 py-10 text-center text-sm text-white/30">
                No gallery items yet. Add images or video links for this expo.
            </div>
        @endforelse
    </div>

    {{-- Add / Edit Modal --}}
    @if($showGalleryForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
             wire:click="$set('showGalleryForm', false)"></div>

        <div class="glass-card relative w-full max-w-md rounded-3xl p-6 shadow-2xl">
            <h3 class="mb-5 text-lg font-bold text-[#D29500]">
                {{ $editingGalleryId ? 'Edit Gallery Item' : 'Add Gallery Item' }}
            </h3>

            <form wire:submit="saveGalleryItem" class="space-y-4">

                {{-- Type toggle --}}
                <div>
                    <label class="label">Type</label>
                    <div class="flex gap-2">
                        <button type="button" wire:click="$set('gItemType','image')"
                                class="{{ $gItemType === 'image' ? 'btn-primary' : 'btn-ghost' }} text-sm flex-1">
                            Image
                        </button>
                        <button type="button" wire:click="$set('gItemType','video')"
                                class="{{ $gItemType === 'video' ? 'btn-primary' : 'btn-ghost' }} text-sm flex-1">
                            Video
                        </button>
                    </div>
                </div>

                {{-- URL / Upload (images only) --}}
                @if($gItemType === 'image')
                    <div x-data="{ mode: $wire.entangle('gItemMode') }">
                        <label class="label">Image</label>
                        <div class="mb-2 flex gap-2">
                            <button type="button" @click="mode = 'url'"
                                    :class="mode === 'url' ? 'btn-primary text-xs !py-1' : 'btn-ghost text-xs !py-1'">URL</button>
                            <button type="button" @click="mode = 'upload'"
                                    :class="mode === 'upload' ? 'btn-primary text-xs !py-1' : 'btn-ghost text-xs !py-1'">Upload</button>
                        </div>
                        <div x-show="mode === 'url'">
                            <input wire:model="gItemUrl" type="text"
                                   placeholder="https://example.com/photo.jpg"
                                   class="glass-input">
                            @error('gItemUrl') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="mode === 'upload'">
                            <input wire:model="gItemFile" type="file" accept="image/*" class="glass-input">
                            @error('gItemFile') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        @if($gItemUrl && $gItemMode === 'url')
                            <img src="{{ $gItemUrl }}" alt="preview"
                                 class="mt-2 h-20 w-full rounded-xl object-cover">
                        @endif
                    </div>
                @else
                    {{-- Video --}}
                    <div>
                        <label class="label">Video URL</label>
                        <input wire:model="gItemUrl" type="text"
                               placeholder="https://youtube.com/watch?v=... or https://vimeo.com/..."
                               class="glass-input">
                        <p class="mt-1 text-[10px] text-white/30">
                            Supports YouTube, Vimeo, or any direct video link.
                        </p>
                        @error('gItemUrl') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Caption --}}
                <div>
                    <label class="label">Caption <span class="text-white/30">(optional)</span></label>
                    <input wire:model="gItemCaption" type="text"
                           placeholder="Short description of this item"
                           class="glass-input">
                </div>

                {{-- Sort order --}}
                <div>
                    <label class="label">Sort Order</label>
                    <input wire:model="gItemOrder" type="number" min="0" class="glass-input">
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="submit" class="btn-primary flex-1">Save</button>
                    <button type="button" wire:click="$set('showGalleryForm', false)"
                            class="btn-ghost flex-1">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

</div>