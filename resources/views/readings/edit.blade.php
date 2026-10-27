<x-app-layout max-width="w-full max-w-3xl">
    <div
        x-data="{
            step: 1,
            title: @js(old('title', $reading->title)),
            description: @js(old('description', $reading->description ?? '')),
            selectedTags: @js(old('tags', $reading->tags->pluck('id')->toArray())),
            slides: @js(old('slides', $reading->slides->map(fn($s) => [
                'type' => $s->type,
                'bg_color' => $s->bg_color,
                'body' => $s->body ?? '',
                'source' => $s->source ?? '',
            ])->toArray())),
            hasQuiz: @js($reading->quiz ? true : false),
            quizQuestion: @js(old('quiz_question', $reading->quiz->question ?? '')),
            quizOptions: @js(old('quiz_options', $reading->quiz->options ?? ['', ''])),
            quizCorrectIndex: @js(old('quiz_correct_index', $reading->quiz->correct_index ?? 0)),
            quizExplanation: @js(old('quiz_explanation', $reading->quiz->explanation ?? '')),
            previewSlideIndex: 0,

            addSlide() {
                if (this.slides.length >= 8) return;
                this.slides.push({ type: 'text', bg_color: 'paper', body: '', source: '' });
                this.previewSlideIndex = this.slides.length - 1;
            },
            removeSlide(index) {
                if (this.slides.length <= 1) return;
                this.slides.splice(index, 1);
                if (this.previewSlideIndex >= this.slides.length) {
                    this.previewSlideIndex = this.slides.length - 1;
                }
            },
            moveSlide(index, direction) {
                const target = index + direction;
                if (target < 0 || target >= this.slides.length) return;
                const temp = this.slides[index];
                this.slides[index] = this.slides[target];
                this.slides[target] = temp;
                this.previewSlideIndex = target;
            },
            toggleTag(id) {
                if (this.selectedTags.includes(id)) {
                    this.selectedTags = this.selectedTags.filter(i => i !== id);
                } else {
                    if (this.selectedTags.length >= 3) return;
                    this.selectedTags.push(id);
                }
            }
        }"
        class="p-4 sm:p-6 w-full flex flex-col gap-6"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b-2 border-paper-dark pb-4">
            <div>
                <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight">Edit Bacaan</h1>
                <p class="text-xs text-deep-teal/70 font-semibold mt-0.5">Perbarui konten kartu bacaan dan kuis.</p>
            </div>
            <a href="{{ route('readings.my') }}" class="text-xs font-bold text-deep-teal/70 hover:text-deep-teal">
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-error/10 border-2 border-error text-error text-xs font-bold">
                <p class="font-extrabold mb-1">Mohon periksa kesalahan berikut:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="readingEditForm"
            method="POST"
            action="{{ route('readings.update', $reading) }}"
            enctype="multipart/form-data"
            class="flex flex-col gap-6"
        >
            @csrf
            @method('PUT')

            <div class="card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col gap-4">
                <!-- Title -->
                <div>
                    <x-input-label for="title" value="Judul Bacaan *" />
                    <x-text-input id="title" name="title" x-model="title" required maxlength="150" />
                </div>

                <!-- Description -->
                <div>
                    <x-input-label for="description" value="Deskripsi Singkat" />
                    <textarea
                        id="description"
                        name="description"
                        x-model="description"
                        rows="2"
                        class="w-full px-4 py-3 bg-white text-deep-teal font-medium border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none resize-none text-xs"
                    ></textarea>
                </div>

                <!-- Tags -->
                <div>
                    <x-input-label value="Pilih Topik Tag (Pilih 1 - 3)" />
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

                <!-- Status -->
                <div>
                    <x-input-label for="status" value="Status Publikasi" />
                    <select
                        name="status"
                        id="status"
                        class="w-full px-3 py-2 bg-white text-deep-teal font-bold text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none"
                    >
                        <option value="published" {{ $reading->status === 'published' ? 'selected' : '' }}>Dipublikasikan (Terbit)</option>
                        <option value="draft" {{ $reading->status === 'draft' ? 'selected' : '' }}>Simpan sebagai Draf</option>
                    </select>
                </div>
            </div>

            <!-- Slides Editor -->
            <div class="card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-base text-deep-teal">
                        Kelola Slide (<span x-text="slides.length"></span>/8)
                    </h3>
                    <x-button type="button" @click="addSlide()" variant="amber" size="sm" x-bind:disabled="slides.length >= 8">
                        <x-lucide-plus class="w-4 h-4 mr-1" />
                        Tambah Slide
                    </x-button>
                </div>

                <template x-for="(slide, index) in slides" :key="index">
                    <div class="card-paper p-4 sm:p-5 border-2 border-paper-dark rounded-2xl flex flex-col gap-3.5">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-xs text-deep-teal" x-text="`Slide ${index + 1}`"></span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="moveSlide(index, -1)" :disabled="index === 0" class="w-8 h-8 rounded-xl bg-paper-dark/70 hover:bg-paper-dark text-deep-teal flex items-center justify-center disabled:opacity-30">
                                    <x-lucide-arrow-up class="w-4 h-4 stroke-[2.5]" />
                                </button>
                                <button type="button" @click="moveSlide(index, 1)" :disabled="index === slides.length - 1" class="w-8 h-8 rounded-xl bg-paper-dark/70 hover:bg-paper-dark text-deep-teal flex items-center justify-center disabled:opacity-30">
                                    <x-lucide-arrow-down class="w-4 h-4 stroke-[2.5]" />
                                </button>
                                <button type="button" @click="removeSlide(index)" :disabled="slides.length <= 1" class="w-8 h-8 rounded-xl bg-error/10 hover:bg-error text-error hover:text-white flex items-center justify-center disabled:opacity-30 ml-1 border border-error/20">
                                    <x-lucide-trash-2 class="w-4 h-4 stroke-[2.5]" />
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-paper-dark">
                            <div>
                                <label class="text-[10px] font-extrabold uppercase text-deep-teal/60 block mb-1">Tipe Slide</label>
                                <select :name="`slides[${index}][type]`" x-model="slide.type" class="w-full px-3 py-2 bg-white text-deep-teal font-bold text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none">
                                    <option value="text">Teks (Micro-article)</option>
                                    <option value="quote">Kutipan (Quote)</option>
                                    <option value="image">Gambar / Visual</option>
                                    <option value="audio">Audio Player</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-extrabold uppercase text-deep-teal/60 block mb-1">Warna Latar</label>
                                <select :name="`slides[${index}][bg_color]`" x-model="slide.bg_color" class="w-full px-3 py-2 bg-white text-deep-teal font-bold text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none">
                                    <option value="paper">Paper (Krem Terang)</option>
                                    <option value="teal">Deep Teal (Gelap Elegan)</option>
                                    <option value="kilat">Kilat (Hijau Aksi)</option>
                                    <option value="amber">Amber (Kuning Cerah)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold uppercase text-deep-teal/60 block mb-1">Konten / Isi</label>
                            <textarea
                                :name="`slides[${index}][body]`"
                                x-model="slide.body"
                                rows="2"
                                class="w-full px-3 py-2 bg-white text-deep-teal font-medium text-xs border border-paper-dark rounded-xl focus:border-kilat focus:outline-none resize-none"
                            ></textarea>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Quiz Settings -->
            <div class="card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-base text-deep-teal">Pertanyaan Kuis</h3>
                        <p class="text-xs text-deep-teal/70 font-semibold">Tantang pembaca untuk bonus EXP.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="has_quiz" value="1" x-model="hasQuiz" class="sr-only peer">
                        <div class="w-14 h-8 bg-paper-dark peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-paper-dark after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-kilat"></div>
                    </label>
                </div>

                <div x-show="hasQuiz" class="flex flex-col gap-4 pt-3 border-t border-paper-dark">
                    <div>
                        <x-input-label for="quiz_question" value="Pertanyaan Kuis" />
                        <x-text-input id="quiz_question" name="quiz_question" x-model="quizQuestion" />
                    </div>

                    <div>
                        <x-input-label value="Pilihan Jawaban (Tandai yang benar)" />
                        <div class="space-y-2">
                            <template x-for="(opt, oIdx) in quizOptions" :key="oIdx">
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="quiz_correct_index" :value="oIdx" x-model="quizCorrectIndex" class="w-5 h-5 text-kilat">
                                    <input type="text" name="quiz_options[]" x-model="quizOptions[oIdx]" class="flex-1 px-3 py-1.5 bg-white text-deep-teal font-semibold text-xs border border-paper-dark rounded-xl">
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="quiz_explanation" value="Penjelasan" />
                        <textarea id="quiz_explanation" name="quiz_explanation" x-model="quizExplanation" rows="2" class="w-full px-3 py-2 bg-white text-deep-teal font-medium text-xs border border-paper-dark rounded-xl resize-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3">
                <x-button as="a" href="{{ route('readings.my') }}" variant="paper" size="md">
                    Batal
                </x-button>
                <x-button type="submit" variant="kilat" size="md">
                    Simpan Perubahan
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
