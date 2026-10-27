<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight">Atur Kata Sandi Baru</h1>
        <p class="text-sm font-medium text-deep-teal/70 mt-1">Buat kata sandi baru yang aman untuk akun Macha kamu.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata Sandi Baru" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Ulangi Kata Sandi Baru" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="pt-2">
            <x-button type="submit" variant="kilat" size="lg" class="w-full">
                Simpan Kata Sandi Baru
            </x-button>
        </div>
    </form>
</x-guest-layout>
