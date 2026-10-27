<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight">Selamat Datang Kembali</h1>
        <p class="text-sm font-medium text-deep-teal/70 mt-1">Lanjutkan streak membaca dan kumpulkan EXP kamu!</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <x-input-label for="password" value="Kata Sandi" class="!mb-0" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-bold text-deep-teal/70 hover:text-kilat-dark transition-colors" href="{{ route('password.request') }}">
                        Lupa sandi?
                    </a>
                @endif
            </div>

            <x-text-input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded-md border-2 border-paper-dark text-kilat focus:ring-kilat focus:ring-offset-0" name="remember">
                <span class="ms-2.5 text-xs font-bold text-deep-teal/80">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <div class="pt-2">
            <x-button type="submit" variant="kilat" size="lg" class="w-full">
                Masuk ke Akun
            </x-button>
        </div>

        <div class="pt-4 text-center border-t border-paper-dark">
            <p class="text-xs font-medium text-deep-teal/70">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-extrabold text-kilat-dark hover:underline">
                    Daftar sekarang
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
