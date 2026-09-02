@php
    $isAdmin = auth()->user()?->hasAnyRole(['admin', 'super_admin']);
@endphp

@if($isAdmin)
    @extends('layouts.admin')
@else
    @extends('layouts.public')
@endif

@section('title', 'My Profile')

@section('content')

<div class="mx-auto max-w-2xl">

    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-white">My Profile</h1>
        <p class="mt-1 text-sm text-white/50">Update your account information and password.</p>
    </div>

    {{-- Flash --}}
    @if(session('status') === 'profile-updated')
        <div class="mb-4 rounded-xl border border-green-500/30 bg-green-900/30 px-4 py-3 text-sm text-green-300">
            Profile updated successfully.
        </div>
    @endif
    @if(session('status') === 'password-updated')
        <div class="mb-4 rounded-xl border border-green-500/30 bg-green-900/30 px-4 py-3 text-sm text-green-300">
            Password changed successfully.
        </div>
    @endif

    {{-- ── Profile Info ── --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8">
        <h2 class="mb-5 flex items-center gap-3 text-base font-bold text-white">
            <span class="inline-block h-5 w-1 rounded-full bg-[#D29500]"></span>
            Account Information
        </h2>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label">Full Name *</label>
                    <input name="name" type="text" class="glass-input"
                           value="{{ old('name', $user->name) }}" required autofocus>
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Email Address *</label>
                    <input name="email" type="email" class="glass-input"
                           value="{{ old('email', $user->email) }}" required>
                    @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Phone Number</label>
                    <input name="phone" type="text" placeholder="+263..." class="glass-input"
                           value="{{ old('phone', $user->phone) }}">
                    @error('phone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="label">Company / Organisation</label>
                    <input name="company" type="text" placeholder="Your company name" class="glass-input"
                           value="{{ old('company', $user->company) }}">
                    @error('company') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Role badge (read-only) --}}
                @if($user->getRoleNames()->isNotEmpty())
                <div class="sm:col-span-2">
                    <label class="label">Role</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($user->getRoleNames() as $role)
                            <span class="rounded-full px-3 py-1 text-xs font-bold uppercase
                                {{ $role === 'super_admin' ? 'bg-[#D29500]/20 text-[#D29500]' : 'bg-[#185909]/40 text-green-400' }}">
                                {{ str_replace('_', ' ', $role) }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <button type="submit" class="btn-primary">Save Profile</button>
        </form>
    </div>

    {{-- ── Change Password ── --}}
    <div class="glass-card mt-5 rounded-3xl p-6 sm:p-8">
        <h2 class="mb-5 flex items-center gap-3 text-base font-bold text-white">
            <span class="inline-block h-5 w-1 rounded-full bg-[#D29500]"></span>
            Change Password
        </h2>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4"
              x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
            @csrf @method('PUT')

            <div>
                <label class="label">Current Password</label>
                <div class="relative">
                    <input name="current_password"
                           :type="showCurrent ? 'text' : 'password'"
                           class="glass-input pr-10"
                           autocomplete="current-password">
                    <button type="button" @click="showCurrent = !showCurrent"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label">New Password</label>
                    <div class="relative">
                        <input name="password"
                               :type="showNew ? 'text' : 'password'"
                               class="glass-input pr-10"
                               autocomplete="new-password">
                        <button type="button" @click="showNew = !showNew"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="label">Confirm New Password</label>
                    <div class="relative">
                        <input name="password_confirmation"
                               :type="showConfirm ? 'text' : 'password'"
                               class="glass-input pr-10"
                               autocomplete="new-password">
                        <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">Update Password</button>
        </form>
    </div>

    {{-- ── Danger zone (non-admins only) ── --}}
    @if(! $isAdmin)
    <div class="glass-card mt-5 rounded-3xl border border-red-500/20 p-6 sm:p-8">
        <h2 class="mb-3 text-base font-bold text-red-400">Danger Zone</h2>
        <p class="mb-4 text-sm text-white/50">Permanently delete your account and all associated data. This cannot be undone.</p>
        <form method="POST" action="{{ route('profile.destroy') }}"
              onsubmit="return confirm('Are you sure? This will permanently delete your account.')">
            @csrf @method('DELETE')
            <input name="password" type="password" class="glass-input mb-3 max-w-xs" placeholder="Confirm your password" required>
            @error('password', 'userDeletion') <p class="mb-2 text-xs text-red-400">{{ $message }}</p> @enderror
            <br>
            <button type="submit" class="rounded-xl border border-red-500/40 bg-red-900/30 px-4 py-2 text-sm font-semibold text-red-400 hover:bg-red-900/50 transition">
                Delete My Account
            </button>
        </form>
    </div>
    @endif

</div>

@endsection
