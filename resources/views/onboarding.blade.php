<x-guest-layout>
    <div
        x-data="{
            selected: @js($selectedTagIds ?? []),
            toggle(id) {
                if (this.selected.includes(id)) {
                    this.selected = this.selected.filter(i => i !== id);
                } else {
                    this.selected.push(id);
                }
            },
            isSelected(id) {
                return this.selected.includes(id);
            }
        }"
        class="w-full"
    >
        <div class="mb-6 text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber/20 border border-amber/50 text-xs font-extrabold text-deep-teal mb-3">
                <x-lucide-sparkles class="w-3.5 h-3.5 text-amber-dark" />
                Langkah Terakhir
            </span>
            <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight">Pilih Topik Minat Kamu</h1>
            <p class="text-sm font-medium text-deep-teal/70 mt-1">
                Pilih minimal <strong class="text-deep-teal">3 topik</strong> agar feed bacaanmu langsung relevan dan seru.
            </p>
        </div>

        <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-6">
            @csrf

            <!-- Tag Chips Grid -->
            <div class="grid grid-cols-2 gap-2.5">
                @foreach ($tags as $tag)
                    <button
                        type="button"
                        @click="toggle({{ $tag->id }})"
                        :class="isSelected({{ $tag->id }})
                            ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark -translate-y-0.5'
                            : 'bg-paper text-deep-teal border-2 border-paper-dark border-b-4 hover:border-paper-dark'"
                        class="flex items-center justify-between p-3.5 rounded-2xl font-bold text-sm text-left transition-all duration-100 select-none cursor-pointer active:translate-y-0.5 active:border-b-2"
                    >
                        <span>{{ $tag->name }}</span>
                        <div
                            :class="isSelected({{ $tag->id }}) ? 'bg-deep-teal text-kilat' : 'border-2 border-paper-dark'"
                            class="w-5 h-5 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors"
                        >
                            <template x-if="isSelected({{ $tag->id }})">
                                <x-lucide-check class="w-3.5 h-3.5 stroke-[3]" />
                            </template>
                        </div>
                    </button>

                    <!-- Hidden input when selected -->
                    <template x-if="isSelected({{ $tag->id }})">
                        <input type="hidden" name="tags[]" value="{{ $tag->id }}">
                    </template>
                @endforeach
            </div>

            <x-input-error :messages="$errors->get('tags')" />

            <!-- Counter & Submit Button -->
            <div class="pt-2">
                <x-button
                    type="submit"
                    variant="kilat"
                    size="lg"
                    class="w-full"
                    x-bind:disabled="selected.length < 3"
                >
                    <span x-show="selected.length < 3">
                        Pilih <span x-text="3 - selected.length"></span> Topik Lagi
                    </span>
                    <span x-show="selected.length >= 3">
                        Lanjut ke Feed (<span x-text="selected.length"></span> Dipilih) &rarr;
                    </span>
                </x-button>
            </div>
        </form>
    </div>
</x-guest-layout>
