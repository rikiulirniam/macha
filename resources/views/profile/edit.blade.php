<x-app-layout :maxWidth="'w-full max-w-xl'">
    <div
        x-data="{
            selectedTags: @js($userTagIds ?? []),
            toggleTag(id) {
                if (this.selectedTags.includes(id)) {
                    if (this.selectedTags.length <= 1) {
                        alert('Pilih minimal 1 topik minat.');
                        return;
                    }
                    this.selectedTags = this.selectedTags.filter(i => i !== id);
                } else {
                    this.selectedTags.push(id);
                }
            }
        }"
        class="p-4 sm:p-6 w-full flex flex-col gap-6"
    >
        <div class="flex items-center justify-between border-b-2 border-paper-dark pb-4">
            <div>
                <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight">Pengaturan Profil</h1>
                <p class="text-xs text-deep-teal/70 font-semibold mt-0.5">Sesuaikan informasi akun dan topik minat membaca kamu.</p>
            </div>
            <a href="{{ route('profile.show') }}" class="text-xs font-bold text-deep-teal/70 hover:text-deep-teal">
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-error/10 border-2 border-error text-error text-xs font-bold">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Profile Information Form -->
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex flex-col gap-5">
            @csrf
            @method('PATCH')

            <div class="card-paper p-6 border-2 border-paper-dark rounded-3xl flex flex-col gap-4">
                <h2 class="font-extrabold text-base text-deep-teal">Data Diri & Avatar</h2>

                <!-- Avatar Upload -->
                <div class="flex items-center gap-4">
                    @if ($user->avatar_path)
                        <img src="{{ asset('storage/' . $user->avatar_path) }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-paper-dark">
                    @else
                        <span class="w-16 h-16 rounded-2xl bg-deep-teal text-paper font-black text-2xl flex items-center justify-center">
                            {{ $user->initials }}
                        </span>
                    @endif

                    <div class="flex-1">
                        <label class="text-xs font-extrabold uppercase text-deep-teal/80 block mb-1">Ganti Foto Profil (Opsional)</label>
                        <input
                            type="file"
                            name="avatar"
                            accept="image/png,image/jpeg,image/webp"
                            class="w-full text-xs font-semibold text-deep-teal file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-paper-dark file:text-deep-teal hover:file:bg-kilat cursor-pointer"
                        >
                        <span class="text-[10px] text-deep-teal/50 font-semibold block mt-1">Maksimal 2 MB (JPG, PNG, atau WEBP).</span>
                    </div>
                </div>

                <!-- Name -->
                <div>
                    <x-input-label for="name" value="Nama Lengkap" />
                    <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required />
                </div>

                <!-- Username -->
                <div>
                    <x-input-label for="username" value="Username Unik" />
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-deep-teal/50 select-none">@</span>
                        <x-text-input id="username" class="!pl-8 lowercase" name="username" type="text" :value="old('username', $user->username)" required />
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <x-input-label for="email" value="Alamat Email" />
                    <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required />
                </div>

                <!-- Interests Selection -->
                <div class="pt-3 border-t border-paper-dark">
                    <div class="flex items-center justify-between mb-2">
                        <x-input-label value="Ubah Topik Minat Membaca" class="!mb-0" />
                        <span class="text-xs font-extrabold text-kilat-dark" x-text="`${selectedTags.length} topik aktif`"></span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($tags as $tag)
                            <button
                                type="button"
                                @click="toggleTag({{ $tag->id }})"
                                :class="selectedTags.includes({{ $tag->id }})
                                    ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark'
                                    : 'bg-paper text-deep-teal border-2 border-paper-dark border-b-4 hover:bg-white'"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs text-left flex items-center justify-between cursor-pointer transition-all active:translate-y-0.5 active:border-b-2"
                            >
                                <span>{{ $tag->name }}</span>
                                <template x-if="selectedTags.includes({{ $tag->id }})">
                                    <x-lucide-check class="w-3.5 h-3.5 stroke-[3]" />
                                </template>
                            </button>
                            <template x-if="selectedTags.includes({{ $tag->id }})">
                                <input type="hidden" name="tags[]" value="{{ $tag->id }}">
                            </template>
                        @endforeach
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <x-button type="submit" variant="kilat" size="md">
                        Simpan Perubahan Profil
                    </x-button>
                </div>
            </div>
        </form>

        <!-- Delete Account Card -->
        <div class="card-paper p-6 border-2 border-paper-dark rounded-3xl flex flex-col gap-3">
            <h2 class="font-extrabold text-base text-error">Hapus Akun</h2>
            <p class="text-xs text-deep-teal/70 font-semibold leading-relaxed">
                Setelah akun dihapus, seluruh data profil, EXP, streak, dan histori membaca kamu akan terhapus secara permanen.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Apakah kamu benar-benar yakin ingin menghapus akun ini? Masukkan kata sandi pada jendela berikutnya.');">
                @csrf
                @method('DELETE')

                <div class="flex items-center gap-2">
                    <x-text-input name="password" type="password" placeholder="Masukkan kata sandi untuk konfirmasi..." required class="!text-xs !py-2" />
                    <x-button type="submit" variant="error" size="sm" class="flex-shrink-0">
                        Hapus Akun
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
