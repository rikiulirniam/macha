<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight">Lupa Kata Sandi?</h1>
        <p class="text-sm font-medium text-deep-teal/70 mt-1">
            Masukkan email terdaftar kamu, kami akan kirimkan tautan pemulihan kata sandi.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="pt-2">
            <x-button type="submit" variant="kilat" size="lg" class="w-full">
                Kirim Tautan Pemulihan
            </x-button>
        </div>

        <div class="pt-4 text-center border-t border-paper-dark">
            <a href="{{ route('login') }}" class="text-xs font-extrabold text-deep-teal/70 hover:text-deep-teal">
                &larr; Kembali ke halaman Masuk
            </a>
        </div>
    </form>
</x-guest-layout>
