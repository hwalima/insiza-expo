<x-guest-layout>
    <h2 class="mb-6 text-2xl font-extrabold text-white">Sign In</h2>

    <x-auth-session-status class="mb-4 rounded-xl border border-green-500/30 bg-green-900/30 px-4 py-2 text-sm text-green-300" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="label">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="glass-input" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-red-400" />
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" type="password" name="password"
                   class="glass-input" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-400" />
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-white/60">
                <input type="checkbox" name="remember" class="rounded border-white/20 bg-white/10 text-[#185909]">
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-[#D29500] hover:underline">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn-primary w-full">Sign In</button>

        <p class="text-center text-sm text-white/50">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-[#D29500] hover:underline">Register</a>
        </p>
    </form>
</x-guest-layout>
