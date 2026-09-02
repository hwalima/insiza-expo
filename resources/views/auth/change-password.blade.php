<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set New Password — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-expo antialiased flex min-h-screen items-center justify-center p-4">

<div class="w-full max-w-md">
    {{-- Logo --}}
    <div class="mb-8 text-center">
        <p class="text-2xl font-extrabold">
            <span class="text-[#D29500]">INSIZA</span>
            <span class="text-white"> EXPO</span>
        </p>
    </div>

    <div class="glass-card rounded-3xl p-8 shadow-2xl">
        {{-- Icon + heading --}}
        <div class="mb-6 flex flex-col items-center text-center">
            <div class="mb-4 flex size-14 items-center justify-center rounded-full bg-[#D29500]/15 ring-2 ring-[#D29500]/30">
                <svg class="size-7 text-[#D29500]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-xl font-extrabold text-white">Set Your New Password</h1>
            <p class="mt-2 text-sm text-white/50">Your account requires a password change before you can continue.</p>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-500/30 bg-red-900/30 px-4 py-3 text-sm text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.change.update') }}" class="space-y-4"
              x-data="{ show1: false, show2: false }">
            @csrf

            <div>
                <label class="label">New Password</label>
                <div class="relative">
                    <input name="password"
                           :type="show1 ? 'text' : 'password'"
                           class="glass-input pr-10"
                           autocomplete="new-password"
                           required>
                    <button type="button" @click="show1 = !show1"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70">
                        <svg x-show="!show1" class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show1" class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <p class="mt-1 text-[10px] text-white/30">At least 8 characters, with uppercase, lowercase and a number.</p>
            </div>

            <div>
                <label class="label">Confirm New Password</label>
                <div class="relative">
                    <input name="password_confirmation"
                           :type="show2 ? 'text' : 'password'"
                           class="glass-input pr-10"
                           autocomplete="new-password"
                           required>
                    <button type="button" @click="show2 = !show2"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70">
                        <svg x-show="!show2" class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show2" class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full py-3 text-base font-bold">
                Set Password &amp; Continue
            </button>
        </form>

        <div class="mt-5 border-t border-white/10 pt-4 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-white/30 hover:text-white/60">
                    Sign out instead
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
