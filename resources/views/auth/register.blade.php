<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight">Mulai Petualangan Macha</h1>
        <p class="text-sm font-medium text-deep-teal/70 mt-1">Daftar sekarang dan baca micro-artikel seru setiap hari.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" placeholder="Budi Santoso" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <!-- Username -->
        <div>
            <x-input-label for="username" value="Username Unik" />
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-deep-teal/50 select-none">@</span>
                <x-text-input id="username" class="!pl-8 lowercase" type="text" name="username" :value="old('username')" placeholder="budisantoso" required autocomplete="username" />
            </div>
            <p class="text-[11px] text-deep-teal/60 font-semibold mt-1">Huruf kecil, angka, atau tanda minus/garis bawah (3-30 karakter).</p>
            <x-input-error :messages="$errors->get('username')" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" placeholder="budi@example.com" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata Sandi" />
            <x-text-input id="password" type="password" name="password" placeholder="Minimal 8 karakter" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi kata sandi" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="pt-2">
            <x-button type="submit" variant="kilat" size="lg" class="w-full">
                Daftar & Pilih Minat
            </x-button>
        </div>

        <div class="pt-4 text-center border-t border-paper-dark">
            <p class="text-xs font-medium text-deep-teal/70">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-extrabold text-kilat-dark hover:underline">
                    Masuk ke sini
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
