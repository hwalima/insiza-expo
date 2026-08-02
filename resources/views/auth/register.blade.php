<x-guest-layout>
    <h2 class="mb-6 text-2xl font-extrabold text-white">Create Account</h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="label">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="glass-input" required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs text-red-400" />
        </div>

        <div>
            <label for="email" class="label">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="glass-input" required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-red-400" />
        </div>

        <div>
            <label for="phone" class="label">Phone Number</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
                   placeholder="+263..." class="glass-input" autocomplete="tel">
        </div>

        <div>
            <label for="company" class="label">Company / Organisation</label>
            <input id="company" type="text" name="company" value="{{ old('company') }}"
                   class="glass-input" autocomplete="organization">
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" type="password" name="password"
                   class="glass-input" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-400" />
        </div>

        <div>
            <label for="password_confirmation" class="label">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="glass-input" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs text-red-400" />
        </div>

        <button type="submit" class="btn-primary w-full">Create Account</button>

        <p class="text-center text-sm text-white/50">
            Already registered?
            <a href="{{ route('login') }}" class="text-[#D29500] hover:underline">Sign in</a>
        </p>
    </form>
</x-guest-layout>
