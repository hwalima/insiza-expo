@extends('layouts.admin')
@section('title', 'Attendees')

@section('content')
<div class="p-6 space-y-5" x-data="attendeesPage()">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Attendees</h1>
            <p class="text-gray-400 text-sm">
                IDIEXPO {{ $expo?->year }} &nbsp;&middot;&nbsp;
                {{ $attendees->total() }} registered
            </p>
        </div>
        <div class="flex gap-2">
            <button @click="openCreate()"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
                    style="background:linear-gradient(135deg,#185909,#2d7a10)">
                + Add Attendee
            </button>
            <a href="{{ route(`attend`) }}" target="_blank"
               class="px-4 py-2 rounded-lg text-sm font-semibold"
               style="background:rgba(24,89,9,0.3);border:1px solid #185909;color:#4ade80">
                Registration Form ↗
            </a>
        </div>
    </div>

    @if(session(`success`))
    <div class="px-4 py-2 rounded-lg text-sm text-green-300"
         style="background:rgba(24,89,9,0.3);border:1px solid #185909">
        {{ session(`success`) }}
    </div>
    @endif

    {{-- Search --}}
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request(`search`) }}"
               placeholder="Search name, org, phone, reg number…"
               class="flex-1 rounded-lg px-4 py-2 text-white text-sm placeholder-gray-500 focus:outline-none"
               style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12)">
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
                style="background:rgba(24,89,9,0.4);border:1px solid #185909">Search</button>
        @if(request(`search`))
        <a href="{{ route(`admin.attendees`) }}"
           class="px-4 py-2 rounded-lg text-sm text-gray-400"
           style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1)">Clear</a>
        @endif
    </form>

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
                    <th class="text-left px-4 py-3 text-gray-400 font-medium">Date</th>
                    <th class="px-4 py-3 text-gray-400 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendees as $i => $a)
                <tr class="border-b border-white border-opacity-5 hover:bg-white hover:bg-opacity-5 transition-colors">
                    <td class="px-4 py-3 text-gray-500">{{ $attendees->firstItem() + $i }}</td>
                    <td class="px-4 py-3 text-white font-medium">{{ $a->name }}</td>
                    <td class="px-4 py-3 text-gray-400">{{ $a->organisation ?? `–` }}</td>
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
                              style="background:rgba(24,89,9,0.3);color:#4ade80;border:1px solid #185909">Checked In</span>
                        @else
                        <span class="px-2 py-1 rounded-full text-xs font-bold"
                              style="background:rgba(255,255,255,0.05);color:#9ca3af;border:1px solid rgba(255,255,255,0.1)">Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $a->created_at->format(`d M Y`) }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ $a->verifyUrl() }}" target="_blank"
                               class="px-2 py-1 rounded text-xs text-gray-400 hover:text-white"
                               style="background:rgba(255,255,255,0.05)" title="View pass">↗</a>
                            <button @click="openEdit({{ $a->id }}, `{{ addslashes($a->name) }}`, `{{ addslashes($a->organisation ?? ``) }}`, `{{ addslashes($a->email ?? ``) }}`, `{{ $a->phone }}`, {{ $a->checked_in ? `true` : `false` }})"
                                    class="px-2 py-1 rounded text-xs font-medium"
                                    style="background:rgba(210,149,0,0.15);color:#D29500;border:1px solid rgba(210,149,0,0.3)">Edit</button>
                            <button @click="confirmDelete({{ $a->id }}, `{{ addslashes($a->name) }}`)"
                                    class="px-2 py-1 rounded text-xs font-medium"
                                    style="background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.3)">Del</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                        No attendees yet.
                        <a href="{{ route(`attend`) }}" class="text-green-400 underline ml-1">Share the registration link</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attendees->hasPages())
    <div>{{ $attendees->links() }}</div>
    @endif

    {{-- Create/Edit Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
         style="background:rgba(0,0,0,0.75)">
        <div class="w-full max-w-md rounded-2xl p-6 space-y-4"
             style="background:#1a2e0f;border:1px solid rgba(24,89,9,0.5)">
            <div class="flex items-center justify-between">
                <h2 class="text-white font-bold text-lg" x-text="modalTitle"></h2>
                <button @click="showModal=false" class="text-gray-500 hover:text-white text-xl leading-none">✕</button>
            </div>
            <form id="attendee-form" method="POST" @submit.prevent="submitForm">
                @csrf
                <input type="hidden" name="_method" x-bind:value="formMethod">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Full Name *</label>
                        <input type="text" x-model="form.name" name="name" required
                               class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                               style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Organisation</label>
                        <input type="text" x-model="form.organisation" name="organisation"
                               class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                               style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Phone *</label>
                            <input type="tel" x-model="form.phone" name="phone" required
                                   class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                                   style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Email</label>
                            <input type="email" x-model="form.email" name="email"
                                   class="w-full rounded-lg px-3 py-2 text-white text-sm focus:outline-none"
                                   style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15)">
                        </div>
                    </div>
                    <template x-if="formMethod === `PUT`">
                        <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer pt-1">
                            <input type="checkbox" x-model="form.checked_in" name="checked_in" value="1" class="rounded">
                            <span>Mark as Checked In</span>
                        </label>
                    </template>
                </div>
                <div class="flex gap-2 mt-5">
                    <button type="submit"
                            class="flex-1 py-2 rounded-lg font-semibold text-white text-sm"
                            style="background:linear-gradient(135deg,#185909,#2d7a10)"
                            x-text="formMethod === `POST` ? `Add Attendee` : `Save Changes`"></button>
                    <button type="button" @click="showModal=false"
                            class="px-4 py-2 rounded-lg text-sm text-gray-400"
                            style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1)">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
         style="background:rgba(0,0,0,0.75)">
        <div class="w-full max-w-sm rounded-2xl p-6 text-center space-y-4"
             style="background:#1a2e0f;border:1px solid rgba(239,68,68,0.4)">
            <div class="text-4xl">🗑️</div>
            <p class="text-white font-semibold">Delete <span class="text-red-400" x-text="deleteName"></span>?</p>
            <p class="text-gray-400 text-sm">This cannot be undone.</p>
            <div class="flex gap-2 justify-center">
                <form id="delete-form" method="POST" @submit="showDelete=false">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white"
                            style="background:rgba(239,68,68,0.7);border:1px solid #f87171">Yes, Delete</button>
                </form>
                <button @click="showDelete=false" class="px-5 py-2 rounded-lg text-sm text-gray-400"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1)">Cancel</button>
            </div>
        </div>
    </div>

</div>

<script>
function attendeesPage() {
    return {
        showModal: false, showDelete: false,
        modalTitle: '', formAction: '', formMethod: 'POST',
        deleteAction: '', deleteName: '',
        form: { name: '', organisation: '', phone: '', email: '', checked_in: false },

        openCreate() {
            this.modalTitle = 'Add Attendee';
            this.formMethod = 'POST';
            this.formAction = '{{ route("admin.attendees.store") }}';
            this.form = { name: '', organisation: '', phone: '', email: '', checked_in: false };
            this.showModal = true;
        },
        openEdit(id, name, org, email, phone, checkedIn) {
            this.modalTitle = 'Edit Attendee';
            this.formMethod = 'PUT';
            this.formAction = `/admin/attendees/${id}`;
            this.form = { name, organisation: org, email, phone, checked_in: checkedIn };
            this.showModal = true;
        },
        confirmDelete(id, name) {
            this.deleteName = name;
            this.deleteAction = `/admin/attendees/${id}`;
            this.showDelete = true;
            this.$nextTick(() => {
                document.getElementById('delete-form').action = this.deleteAction;
            });
        },
        submitForm() {
            const f = document.getElementById('attendee-form');
            f.action = this.formAction;
            f.submit();
        },
        get deleteFormReady() {
            document.getElementById('delete-form').action = this.deleteAction;
            return true;
        }
    }
}
document.addEventListener('alpine:init', () => {
    document.addEventListener('change', e => {
        const df = document.getElementById('delete-form');
        if (df) df.action = Alpine.evaluate(df.closest('[x-data]'), 'deleteAction') || df.action;
    });
});
</script>
@endsection