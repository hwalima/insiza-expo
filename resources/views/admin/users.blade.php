@extends('layouts.admin')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-extrabold text-white">Admin Users</h1>
        <p class="mt-1 text-sm text-white/50">Manage who has access to the admin panel.</p>
    </div>
    <button onclick="document.getElementById('create-modal').classList.remove('hidden')"
            class="btn-primary">+ Add Admin</button>
</div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="mb-4 rounded-xl border border-green-500/30 bg-green-900/30 px-4 py-3 text-sm text-green-300">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-xl border border-red-500/30 bg-red-900/30 px-4 py-3 text-sm text-red-300">{{ session('error') }}</div>
@endif

{{-- Users table --}}
<div class="glass-card overflow-hidden rounded-2xl">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/10 text-left text-xs font-semibold uppercase tracking-wider text-white/40">
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3 hidden sm:table-cell">Email</th>
                <th class="px-5 py-3 hidden md:table-cell">Role</th>
                <th class="px-5 py-3 hidden md:table-cell">Status</th>
                <th class="px-5 py-3 hidden lg:table-cell">Created</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($users as $u)
                <tr class="hover:bg-white/5 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-[#185909] text-xs font-bold text-[#D29500]">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-white">{{ $u->name }}</p>
                                <p class="text-xs text-white/40 sm:hidden">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-white/70 hidden sm:table-cell">{{ $u->email }}</td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        @foreach($u->getRoleNames() as $role)
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase
                                {{ $role === 'super_admin' ? 'bg-[#D29500]/20 text-[#D29500]' : 'bg-[#185909]/40 text-green-400' }}">
                                {{ str_replace('_', ' ', $role) }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        @if($u->must_change_password)
                            <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-[10px] font-semibold text-amber-400">Must change pw</span>
                        @else
                            <span class="rounded-full bg-green-900/40 px-2 py-0.5 text-[10px] font-semibold text-green-400">Active</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-white/40 hidden lg:table-cell">{{ $u->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-3">
                            <button onclick="openEdit({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ $u->getRoleNames()->first() }}')"
                                    class="text-xs text-blue-400 hover:text-blue-300">Edit</button>

                            @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.reset-password', $u) }}"
                                      onsubmit="return confirm('Reset password for {{ addslashes($u->name) }} and send new credentials by email?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-amber-400 hover:text-amber-300">Reset pw</button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                      onsubmit="return confirm('Delete {{ addslashes($u->name) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-300">Delete</button>
                                </form>
                            @else
                                <span class="text-xs text-white/20">You</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-sm text-white/30">No admin users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ── Create Modal ── --}}
<div id="create-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
         onclick="document.getElementById('create-modal').classList.add('hidden')"></div>
    <div class="glass-card relative w-full max-w-md rounded-3xl p-6 shadow-2xl">
        <h3 class="mb-5 text-lg font-bold text-[#D29500]">Add Admin User</h3>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label">Full Name *</label>
                <input name="name" type="text" class="glass-input" required value="{{ old('name') }}">
                @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Email Address *</label>
                <input name="email" type="email" class="glass-input" required value="{{ old('email') }}">
                @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Role *</label>
                <select name="role" class="glass-select w-full">
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
                @error('role') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>
            <p class="text-xs text-white/40">A temporary password will be generated and emailed to the user. They will be prompted to change it on first login.</p>
            <div class="flex gap-2 pt-1">
                <button type="submit" class="btn-primary flex-1">Create &amp; Send Email</button>
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')"
                        class="btn-ghost flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Edit Modal ── --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
         onclick="document.getElementById('edit-modal').classList.add('hidden')"></div>
    <div class="glass-card relative w-full max-w-md rounded-3xl p-6 shadow-2xl">
        <h3 class="mb-5 text-lg font-bold text-[#D29500]">Edit Admin User</h3>
        <form id="edit-form" method="POST" action="" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="label">Full Name *</label>
                <input id="edit-name" name="name" type="text" class="glass-input" required>
            </div>
            <div>
                <label class="label">Email Address *</label>
                <input id="edit-email" name="email" type="email" class="glass-input" required>
            </div>
            <div>
                <label class="label">Role *</label>
                <select id="edit-role" name="role" class="glass-select w-full">
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="submit" class="btn-primary flex-1">Save Changes</button>
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')"
                        class="btn-ghost flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name, email, role) {
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-role').value  = role;
    document.getElementById('edit-form').action = '/admin/users/' + id;
    document.getElementById('edit-modal').classList.remove('hidden');
}

// Re-open create modal if there were validation errors
@if($errors->any() && old('_token'))
    document.getElementById('create-modal').classList.remove('hidden');
@endif
</script>

@endsection
