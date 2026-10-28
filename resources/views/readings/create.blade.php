<x-app-layout max-width="w-full max-w-5xl">
    <div
        x-data="{
            step: 1,
            activeMobileTab: 'editor', // 'editor' | 'preview'
            title: '{{ old('title', '') }}',
            description: '{{ old('description', '') }}',
            selectedTags: @js(array_values(array_map('intval', (array) old('tags', [])))),
            slides: @js(old('slides', [
                ['type' => 'text', 'bg_color' => 'paper', 'body' => '', 'source' => '']
            ])),
            hasQuiz: {{ old('has_quiz') ? 'true' : 'false' }},
            quizQuestion: '{{ old('quiz_question', '') }}',
            quizOptions: @js(old('quiz_options', ['', ''])),
            quizCorrectIndex: {{ old('quiz_correct_index', 0) }},
            quizExplanation: '{{ old('quiz_explanation', '') }}',
            previewSlideIndex: 0,

            addSlide() {
                if (this.slides.length >= 8) {
                    alert('Maksimal 8 slide per bacaan.');
                    return;
                }
                this.slides.push({
                    type: 'text',
                    bg_color: 'paper',
                    body: '',
                    source: ''
                });
                this.previewSlideIndex = this.slides.length - 1;
            },
            removeSlide(index) {
                if (this.slides.length <= 1) {
                    alert('Bacaan membutuhkan minimal 1 slide.');
                    return;
                }
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
            prevPreviewSlide() {
                if (this.previewSlideIndex > 0) {
                    this.previewSlideIndex--;
                }
            },
            nextPreviewSlide() {
                if (this.previewSlideIndex < this.slides.length - 1) {
                    this.previewSlideIndex++;
                }
            },
            addQuizOption() {
                if (this.quizOptions.length >= 4) return;
                this.quizOptions.push('');
            },
            removeQuizOption(index) {
                if (this.quizOptions.length <= 2) return;
                this.quizOptions.splice(index, 1);
                if (this.quizCorrectIndex >= this.quizOptions.length) {
                    this.quizCorrectIndex = 0;
                }
            },
            toggleTag(id) {
                id = Number(id);
                if (this.isTagSelected(id)) {
                    this.selectedTags = this.selectedTags.filter(i => Number(i) !== id);
                } else {
                    if (this.selectedTags.length >= 3) {
                        alert('Maksimal memilih 3 tag topik.');
                        return;
                    }
                    this.selectedTags.push(id);
                }
            },
            isTagSelected(id) {
                return this.selectedTags.some(t => Number(t) === Number(id));
            },
            handleAudioFile(index, event) {
                const file = event.target.files && event.target.files[0];
                if (file) {
                    if (!this.slides[index]) return;
                    this.slides[index].audioFileName = file.name;
                    this.slides[index].audioFileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                    this.slides[index].audioUrl = URL.createObjectURL(file);
                }
            }
        }"
        class="p-4 sm:p-6 lg:p-8 w-full flex flex-col gap-6"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b-2 border-paper-dark pb-4">
            <div>
                <h1 class="font-extrabold text-2xl sm:text-3xl text-deep-teal tracking-tight">Buat Bacaan Baru</h1>
                <p class="text-xs sm:text-sm text-deep-teal/70 font-semibold mt-0.5">Bagikan ilmu bermanfaat dalam format micro-card yang segar.</p>
            </div>
            <a href="{{ route('readings.my') }}" class="px-3 py-1.5 rounded-xl bg-paper-dark/60 hover:bg-paper-dark text-xs font-bold text-deep-teal transition-colors">
                Batal
            </a>
        </div>

        <!-- Responsive Step Indicator (1 to 4) -->
        <div class="flex items-center justify-between gap-1.5 sm:gap-2 select-none">
            <template x-for="s in [
                { num: 1, short: '1. Info', label: '1. Info Dasar' },
                { num: 2, short: '2. Slide', label: '2. Kelola Slide' },
                { num: 3, short: '3. Kuis', label: '3. Kuis Opsional' },
                { num: 4, short: '4. Terbit', label: '4. Tinjau & Terbit' }
            ]" :key="s.num">
                <button
                    type="button"
                    @click="step = s.num"
                    :class="step === s.num
                        ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark shadow-sm'
                        : (step > s.num ? 'bg-amber text-deep-teal border-b-2 border-amber-dark' : 'bg-paper-dark text-deep-teal/60 hover:bg-paper-dark/80')"
                    class="flex-1 py-2 sm:py-2.5 px-1 sm:px-2 text-center rounded-xl font-extrabold text-xs transition-all flex items-center justify-center"
                >
                    <span class="sm:hidden" x-text="s.short"></span>
                    <span class="hidden sm:inline" x-text="s.label"></span>
                </button>
            </template>
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
            id="readingCreateForm"
            method="POST"
            action="{{ route('readings.store') }}"
            enctype="multipart/form-data"
            class="flex flex-col gap-6"
        >
            @csrf

            <!-- ================= STEP 1: INFORMASI DASAR ================= -->
            <div x-show="step === 1" class="flex flex-col gap-5 max-w-2xl mx-auto w-full">
                <div class="card-paper p-5 sm:p-6 border-2 border-paper-dark rounded-3xl flex flex-col gap-5">
                    <!-- Title -->
                    <div>
                        <x-input-label for="title" value="Judul Bacaan *" />
                        <x-text-input
                            id="title"
                            name="title"
                            x-model="title"
                            placeholder="Contoh: Mengapa Langit Berwarna Biru?"
                            required
                            maxlength="150"
                        />
                        <span class="text-[11px] text-deep-teal/60 font-semibold mt-1.5 block" x-text="`${title.length}/150 karakter`"></span>
                    </div>

                    <!-- Description -->
                    <div>
                        <x-input-label for="description" value="Deskripsi Singkat (Opsional)" />
                        <textarea
                            id="description"
                            name="description"
                            x-model="description"
                            placeholder="Rangkuman 1-2 kalimat tentang inti bacaan ini..."
                            rows="2"
                            maxlength="300"
                            class="w-full px-4 py-3 bg-white text-deep-teal font-medium placeholder:text-deep-teal/40 border-2 border-paper-dark rounded-xl focus:border-kilat focus:ring-2 focus:ring-kilat/20 focus:outline-none resize-none text-xs"
                        ></textarea>
                    </div>

                    <!-- Tags Selection (1-3 tags) -->
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <x-input-label value="Pilih Topik Tag * (Pilih 1 - 3)" class="!mb-0" />
                            <span class="text-xs font-extrabold text-kilat-dark" x-text="`${selectedTags.length}/3 dipilih`"></span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            @foreach ($tags as $tag)
                                <button
                                    type="button"
                                    @click="toggleTag({{ $tag->id }})"
                                    :class="isTagSelected({{ $tag->id }})
                                        ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark'
                                        : 'bg-paper text-deep-teal border-2 border-paper-dark border-b-4 hover:bg-white'"
                                    class="py-2.5 px-3 rounded-xl font-bold text-xs text-left flex items-center justify-between cursor-pointer transition-all active:translate-y-0.5 active:border-b-2"
                                >
                                    <span>{{ $tag->name }}</span>
                                    <template x-if="isTagSelected({{ $tag->id }})">
                                        <x-lucide-check class="w-3.5 h-3.5 stroke-[3]" />
                                    </template>
                                </button>
                            @endforeach
                        </div>
                        <!-- Form hidden inputs for tags -->
                        <template x-for="tagId in selectedTags" :key="tagId">
                            <input type="hidden" name="tags[]" :value="tagId">
                        </template>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <x-button
                        type="button"
                        @click="if (title.trim() && selectedTags.length >= 1) { step = 2; } else { alert('Harap isi judul dan pilih minimal 1 topik tag.'); }"
                        variant="kilat"
                        size="md"
                    >
                        Lanjut ke Kelola Slide &rarr;
                    </x-button>
                </div>
            </div>

            <!-- ================= STEP 2: KELOLA SLIDE & PRATINJAU ================= -->
            <div x-show="step === 2" class="flex flex-col gap-5">
                <!-- Mobile & Tablet Switcher: Editor vs Pratinjau -->
                <div class="flex lg:hidden items-center justify-center p-1 bg-paper-dark rounded-2xl gap-1 select-none">
                    <button
                        type="button"
                        @click="activeMobileTab = 'editor'"
                        :class="activeMobileTab === 'editor' ? 'bg-kilat text-deep-teal border-b-2 border-kilat-dark font-extrabold shadow-sm' : 'text-deep-teal/70 font-bold hover:text-deep-teal'"
                        class="flex-1 py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all"
                    >
                        <x-lucide-edit-3 class="w-4 h-4" />
                        <span>Editor Slide (<span x-text="slides.length"></span>/8)</span>
                    </button>
                    <button
                        type="button"
                        @click="activeMobileTab = 'preview'"
                        :class="activeMobileTab === 'preview' ? 'bg-amber text-deep-teal border-b-2 border-amber-dark font-extrabold shadow-sm' : 'text-deep-teal/70 font-bold hover:text-deep-teal'"
                        class="flex-1 py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all"
                    >
                        <x-lucide-eye class="w-4 h-4" />
                        <span>Pratinjau Live (<span x-text="`${previewSlideIndex + 1}/${slides.length}`"></span>)</span>
                    </button>
                </div>

                <!-- Desktop 2-Column Grid / Mobile Conditional Tab -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                    <!-- Left Column: Slide List and Editor Form -->
                    <div
                        :class="activeMobileTab === 'editor' ? 'flex' : 'hidden lg:flex'"
                        class="lg:col-span-7 flex flex-col gap-4 w-full"
                    >
                        <div class="flex items-center justify-between pb-1">
                            <div>
                                <h3 class="font-extrabold text-base sm:text-lg text-deep-teal">
                                    Daftar Slide (<span x-text="slides.length"></span>/8)
                                </h3>
                                <p class="text-xs text-deep-teal/60 font-semibold">Tiap slide berdurasi baca ~20-40 detik.</p>
                            </div>
                            <x-button
                                type="button"
                                @click="addSlide()"
                                variant="amber"
                                size="sm"
                                x-bind:disabled="slides.length >= 8"
                            >
                                <x-lucide-plus class="w-4 h-4 mr-1" />
                                Tambah Slide
                            </x-button>
                        </div>

                        <!-- Slides Accordion / Cards List -->
                        <div class="flex flex-col gap-4">
                            <template x-for="(slide, index) in slides" :key="index">
                                <div
                                    :class="previewSlideIndex === index ? 'border-kilat ring-2 ring-kilat/20 shadow-sm' : 'border-paper-dark'"
                                    class="card-paper p-4 sm:p-5 border-2 rounded-2xl sm:rounded-3xl flex flex-col gap-4 transition-all"
                                >
                                    <!-- Slide Card Header -->
                                    <div class="flex items-center justify-between gap-2">
                                        <button
                                            type="button"
                                            @click="previewSlideIndex = index"
                                            class="flex items-center gap-2.5 font-extrabold text-sm text-deep-teal hover:underline text-left cursor-pointer min-w-0"
                                        >
                                            <span class="w-7 h-7 rounded-xl bg-deep-teal text-paper text-xs flex items-center justify-center font-extrabold flex-shrink-0" x-text="index + 1"></span>
                                            <span class="whitespace-nowrap font-extrabold text-sm text-deep-teal">Slide <span x-text="index + 1"></span></span>
                                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-lg bg-paper-dark border border-paper-dark text-deep-teal/80 flex-shrink-0" x-text="slide.type"></span>
                                            <span x-show="previewSlideIndex === index" class="hidden sm:inline-block text-[10px] font-extrabold text-kilat-dark bg-kilat/15 px-2 py-0.5 rounded-md flex-shrink-0">
                                                Aktif Ditinjau
                                            </span>
                                        </button>

                                        <!-- Reorder & Delete Action Buttons -->
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <button
                                                type="button"
                                                @click="moveSlide(index, -1)"
                                                :disabled="index === 0"
                                                class="w-8 h-8 rounded-xl bg-paper-dark/70 hover:bg-paper-dark text-deep-teal flex items-center justify-center disabled:opacity-30 cursor-pointer transition-colors"
                                                title="Geser Naik"
                                                aria-label="Geser Naik"
                                            >
                                                <x-lucide-arrow-up class="w-4 h-4 stroke-[2.5]" />
                                            </button>
                                            <button
                                                type="button"
                                                @click="moveSlide(index, 1)"
                                                :disabled="index === slides.length - 1"
                                                class="w-8 h-8 rounded-xl bg-paper-dark/70 hover:bg-paper-dark text-deep-teal flex items-center justify-center disabled:opacity-30 cursor-pointer transition-colors"
                                                title="Geser Turun"
                                                aria-label="Geser Turun"
                                            >
                                                <x-lucide-arrow-down class="w-4 h-4 stroke-[2.5]" />
                                            </button>
                                            <button
                                                type="button"
                                                @click="removeSlide(index)"
                                                :disabled="slides.length <= 1"
                                                class="w-8 h-8 rounded-xl bg-error/10 hover:bg-error text-error hover:text-white flex items-center justify-center disabled:opacity-30 cursor-pointer transition-colors border border-error/20 ml-1"
                                                title="Hapus Slide"
                                                aria-label="Hapus Slide"
                                            >
                                                <x-lucide-trash-2 class="w-4 h-4 stroke-[2.5]" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Slide Configuration (Type & Background Color) -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-paper-dark">
                                        <!-- Slide Type -->
                                        <div>
                                            <label class="text-[11px] font-extrabold uppercase text-deep-teal/70 block mb-1.5">Tipe Slide</label>
                                            <select
                                                :name="`slides[${index}][type]`"
                                                x-model="slide.type"
                                                class="w-full px-3 py-2 bg-white text-deep-teal font-bold text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none"
                                            >
                                                <option value="text">Teks (Micro-article)</option>
                                                <option value="quote">Kutipan (Quote)</option>
                                                <option value="image">Gambar / Komik (Visual)</option>
                                                <option value="audio">Audio Player</option>
                                            </select>
                                        </div>

                                        <!-- Slide Background Color -->
                                        <div>
                                            <label class="text-[11px] font-extrabold uppercase text-deep-teal/70 block mb-1.5">Warna Latar</label>
                                            <select
                                                :name="`slides[${index}][bg_color]`"
                                                x-model="slide.bg_color"
                                                class="w-full px-3 py-2 bg-white text-deep-teal font-bold text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none"
                                            >
                                                <option value="paper">Paper (Krem Terang)</option>
                                                <option value="teal">Deep Teal (Gelap Elegan)</option>
                                                <option value="kilat">Kilat (Hijau Aksi)</option>
                                                <option value="amber">Amber (Kuning Cerah)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Dynamic Content Fields per Slide Type -->
                                    <template x-if="slide.type === 'text'">
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex items-center justify-between">
                                                <label class="text-[11px] font-extrabold uppercase text-deep-teal/70 block">Isi Teks Artikel (Literata Font)</label>
                                                <span class="text-[10px] text-deep-teal/60 font-semibold">Disarankan 40–90 kata</span>
                                            </div>
                                            <textarea
                                                :name="`slides[${index}][body]`"
                                                x-model="slide.body"
                                                rows="4"
                                                placeholder="Tulis artikel pendek yang nyaman dibaca dan to-the-point..."
                                                class="w-full px-3.5 py-2.5 bg-white text-deep-teal font-serif text-sm border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none resize-none leading-relaxed"
                                            ></textarea>
                                        </div>
                                    </template>

                                    <template x-if="slide.type === 'quote'">
                                        <div class="flex flex-col gap-3">
                                            <div>
                                                <label class="text-[11px] font-extrabold uppercase text-deep-teal/70 block mb-1.5">Teks Kutipan</label>
                                                <textarea
                                                    :name="`slides[${index}][body]`"
                                                    x-model="slide.body"
                                                    rows="3"
                                                    placeholder="Tulis kutipan inspiratif atau pesan penting..."
                                                    class="w-full px-3.5 py-2.5 bg-white text-deep-teal font-serif italic text-sm border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none resize-none"
                                                ></textarea>
                                            </div>
                                            <div>
                                                <label class="text-[11px] font-extrabold uppercase text-deep-teal/70 block mb-1.5">Sumber / Tokoh Kutipan</label>
                                                <input
                                                    type="text"
                                                    :name="`slides[${index}][source]`"
                                                    x-model="slide.source"
                                                    placeholder="Contoh: Albert Einstein / Marcus Aurelius"
                                                    class="w-full px-3.5 py-2 bg-white text-deep-teal font-bold text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none"
                                                >
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="slide.type === 'image'">
                                        <div class="flex flex-col gap-3">
                                            <div>
                                                <label class="text-[11px] font-extrabold uppercase text-deep-teal/70 block mb-1.5">Unggah Gambar (JPG/PNG/WEBP, Maks 2MB)</label>
                                                <input
                                                    type="file"
                                                    :name="`slides[${index}][image]`"
                                                    accept="image/png,image/jpeg,image/webp"
                                                    class="w-full text-xs font-semibold text-deep-teal file:mr-2.5 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-paper-dark file:text-deep-teal hover:file:bg-kilat cursor-pointer"
                                                >
                                            </div>
                                            <div>
                                                <label class="text-[11px] font-extrabold uppercase text-deep-teal/70 block mb-1.5">Teks Keterangan Gambar (Opsional)</label>
                                                <input
                                                    type="text"
                                                    :name="`slides[${index}][body]`"
                                                    x-model="slide.body"
                                                    placeholder="Keterangan singkat tentang gambar..."
                                                    class="w-full px-3.5 py-2 bg-white text-deep-teal font-bold text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none"
                                                >
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="slide.type === 'audio'">
                                        <div class="flex flex-col gap-3">
                                            <div>
                                                <div class="flex items-center justify-between mb-1.5">
                                                    <label class="text-[11px] font-extrabold uppercase text-deep-teal/70">Unggah Audio (M4A/MP3/OGG/AAC, Maks 10MB)</label>
                                                    <span class="text-[10px] text-deep-teal/60 font-bold">.m4a, .mp3, .ogg, .wav</span>
                                                </div>
                                                <input
                                                    type="file"
                                                    :name="`slides[${index}][audio]`"
                                                    accept=".m4a,.mp3,.ogg,.wav,.aac,.flac"
                                                    @change="handleAudioFile(index, $event)"
                                                    class="w-full text-xs font-semibold text-deep-teal file:mr-2.5 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-paper-dark file:text-deep-teal hover:file:bg-kilat cursor-pointer"
                                                >
                                                <div x-show="slide.audioFileName" class="mt-2 p-2.5 rounded-xl bg-kilat/20 border-2 border-kilat/40 flex items-center justify-between text-xs">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <x-lucide-music class="w-4 h-4 text-deep-teal flex-shrink-0" />
                                                        <span class="font-extrabold text-deep-teal truncate" x-text="slide.audioFileName"></span>
                                                        <span class="text-deep-teal/70 font-bold text-[11px] flex-shrink-0" x-text="`(${slide.audioFileSize})`"></span>
                                                    </div>
                                                    <span class="text-[11px] font-extrabold text-kilat-dark px-2 py-0.5 rounded-md bg-kilat/30 flex-shrink-0">
                                                        ✓ Terpilih
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-[11px] font-extrabold uppercase text-deep-teal/70 block mb-1.5">Transkrip / Deskripsi Audio</label>
                                                <input
                                                    type="text"
                                                    :name="`slides[${index}][body]`"
                                                    x-model="slide.body"
                                                    placeholder="Transkrip singkat audio..."
                                                    class="w-full px-3.5 py-2 bg-white text-deep-teal font-bold text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none"
                                                >
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Right Column: Live Interactive Smartphone Card Preview -->
                    <div
                        :class="activeMobileTab === 'preview' ? 'flex' : 'hidden lg:flex'"
                        class="lg:col-span-5 flex flex-col gap-3 lg:sticky lg:top-6 w-full"
                    >
                        <div class="flex items-center justify-between px-1">
                            <div>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-deep-teal flex items-center gap-1.5">
                                    <x-lucide-smartphone class="w-4 h-4 text-kilat-dark" />
                                    <span>Pratinjau Langsung</span>
                                </span>
                            </div>
                            <!-- Slide selector / switcher inside preview -->
                            <div class="flex items-center gap-1 text-xs font-extrabold text-deep-teal">
                                <button
                                    type="button"
                                    @click="prevPreviewSlide()"
                                    :disabled="previewSlideIndex === 0"
                                    class="w-6 h-6 rounded-lg bg-paper-dark/70 hover:bg-paper-dark flex items-center justify-center disabled:opacity-25"
                                    title="Slide Sebelumnya"
                                >
                                    <x-lucide-chevron-left class="w-4 h-4" />
                                </button>
                                <span class="px-1.5 text-xs font-extrabold">
                                    <span x-text="previewSlideIndex + 1"></span>/<span x-text="slides.length"></span>
                                </span>
                                <button
                                    type="button"
                                    @click="nextPreviewSlide()"
                                    :disabled="previewSlideIndex === slides.length - 1"
                                    class="w-6 h-6 rounded-lg bg-paper-dark/70 hover:bg-paper-dark flex items-center justify-center disabled:opacity-25"
                                    title="Slide Selanjutnya"
                                >
                                    <x-lucide-chevron-right class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Smartphone Mockup Frame -->
                        <div class="w-full max-w-[340px] mx-auto min-h-[520px] max-h-[580px] rounded-[38px] border-4 border-deep-teal bg-deep-teal overflow-hidden relative flex flex-col justify-between shadow-xl select-none">
                            <!-- Inner Phone Screen -->
                            <div
                                :class="{
                                    'bg-paper text-deep-teal': slides[previewSlideIndex]?.bg_color === 'paper',
                                    'bg-deep-teal text-paper': slides[previewSlideIndex]?.bg_color === 'teal',
                                    'bg-kilat text-deep-teal': slides[previewSlideIndex]?.bg_color === 'kilat',
                                    'bg-amber text-deep-teal': slides[previewSlideIndex]?.bg_color === 'amber'
                                }"
                                class="w-full h-full p-5 sm:p-6 flex flex-col justify-between relative transition-colors overflow-hidden flex-1"
                            >
                                <!-- Top Mock Phone Speaker Notch -->
                                <div class="w-20 h-3.5 bg-black/15 rounded-full mx-auto -mt-2 mb-3"></div>

                                <!-- Slide Header inside mockup -->
                                <div class="flex items-center justify-between text-xs font-extrabold z-10 pt-1">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider"
                                          :class="slides[previewSlideIndex]?.bg_color === 'teal' ? 'bg-teal-soft/60 text-paper' : 'bg-black/10 text-deep-teal'">
                                        <template x-if="slides[previewSlideIndex]?.type === 'quote'">
                                            <span class="flex items-center gap-1">
                                                <x-lucide-quote class="w-3 h-3" />
                                                <span>Kutipan</span>
                                            </span>
                                        </template>
                                        <template x-if="slides[previewSlideIndex]?.type === 'image'">
                                            <span class="flex items-center gap-1">
                                                <x-lucide-image class="w-3 h-3" />
                                                <span>Visual</span>
                                            </span>
                                        </template>
                                        <template x-if="slides[previewSlideIndex]?.type === 'audio'">
                                            <span class="flex items-center gap-1">
                                                <x-lucide-headphones class="w-3 h-3" />
                                                <span>Audio</span>
                                            </span>
                                        </template>
                                        <template x-if="slides[previewSlideIndex]?.type === 'text'">
                                            <span class="flex items-center gap-1">
                                                <x-lucide-book-open class="w-3 h-3" />
                                                <span>Bacaan</span>
                                            </span>
                                        </template>
                                    </span>

                                    <span class="text-xs font-extrabold opacity-75" x-text="`${previewSlideIndex + 1} / ${slides.length}`"></span>
                                </div>

                                <!-- Body Preview in Center -->
                                <div class="my-auto py-4 overflow-y-auto max-h-[280px] scrollbar-none z-10">
                                    <!-- Quote Type -->
                                    <template x-if="slides[previewSlideIndex]?.type === 'quote'">
                                        <div class="border-l-4 border-current pl-4 my-auto flex flex-col gap-2">
                                            <p class="font-serif text-lg font-bold italic leading-relaxed" x-text="`“${slides[previewSlideIndex]?.body || 'Tulis kutipan inspiratif di sini...'}”`"></p>
                                            <p class="text-xs font-bold opacity-80" x-text="`— ${slides[previewSlideIndex]?.source || 'Nama Tokoh / Sumber'}`"></p>
                                        </div>
                                    </template>

                                    <!-- Image Type -->
                                    <template x-if="slides[previewSlideIndex]?.type === 'image'">
                                        <div class="flex flex-col items-center gap-2.5 my-auto">
                                            <div class="w-full h-36 rounded-2xl bg-black/10 flex flex-col items-center justify-center border-2 border-dashed border-black/20 text-center p-3">
                                                <x-lucide-image class="w-10 h-10 opacity-40 mb-1" />
                                                <span class="text-[11px] font-bold opacity-60">Pratinjau Gambar</span>
                                            </div>
                                            <p class="text-xs font-bold text-center leading-snug" x-text="slides[previewSlideIndex]?.body || 'Keterangan gambar akan tampil di sini'"></p>
                                        </div>
                                    </template>

                                    <!-- Audio Type -->
                                    <template x-if="slides[previewSlideIndex]?.type === 'audio'">
                                        <div class="flex flex-col items-center gap-3 p-4 rounded-2xl bg-black/10 my-auto text-center w-full">
                                            <div class="w-14 h-14 rounded-full bg-kilat text-deep-teal flex items-center justify-center border-b-4 border-kilat-dark shadow-sm">
                                                <x-lucide-play class="w-7 h-7 fill-current translate-x-0.5" />
                                            </div>
                                            <div class="w-full">
                                                <p class="text-xs font-bold truncate" x-text="slides[previewSlideIndex]?.audioFileName || slides[previewSlideIndex]?.body || 'Audio Player & Narasi'"></p>
                                                <template x-if="slides[previewSlideIndex]?.audioFileSize">
                                                    <span class="text-[10px] font-semibold opacity-70" x-text="slides[previewSlideIndex]?.audioFileSize"></span>
                                                </template>
                                            </div>
                                            <template x-if="slides[previewSlideIndex]?.audioUrl">
                                                <audio :src="slides[previewSlideIndex]?.audioUrl" controls class="w-full mt-1 h-7"></audio>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Text Type -->
                                    <template x-if="slides[previewSlideIndex]?.type === 'text'">
                                        <p class="font-serif text-sm sm:text-base leading-relaxed" x-text="slides[previewSlideIndex]?.body || 'Tulis teks bacaan kamu di sini. Konten akan otomatis ditata rapi dengan font Literata yang nyaman dibaca...'"></p>
                                    </template>
                                </div>

                                <!-- Bottom Metadata Overlay (Creator, Title, and Slide Indicator Lines!) -->
                                <div class="pt-3 border-t border-current/15 flex flex-col gap-1.5 z-10">
                                    <div class="flex items-center gap-2 text-[11px] font-bold opacity-85">
                                        <span class="w-5 h-5 rounded-md bg-amber text-deep-teal font-extrabold text-[10px] flex items-center justify-center border border-amber-dark">
                                            {{ auth()->user()->initials }}
                                        </span>
                                        <span class="truncate max-w-[120px]">{{ auth()->user()->name }}</span>
                                        <span class="ml-auto text-[10px] px-1.5 py-0.2 rounded bg-kilat text-deep-teal font-extrabold border border-kilat-dark">1 mnt</span>
                                    </div>

                                    <!-- Title in Mockup -->
                                    <p class="font-sans font-extrabold text-xs line-clamp-1 leading-tight" x-text="title || 'Judul Bacaan Kamu'"></p>

                                    <!-- Slide Indicator Segments directly below title (Matches feed!) -->
                                    <div class="flex items-center gap-1 pt-0.5" aria-label="Indikator slide">
                                        <template x-for="(s, sIdx) in slides" :key="sIdx">
                                            <div
                                                @click="previewSlideIndex = sIdx"
                                                class="h-1 flex-1 rounded-full cursor-pointer transition-all duration-200"
                                                :class="previewSlideIndex === sIdx ? 'bg-amber shadow-sm ring-1 ring-amber-dark' : (previewSlideIndex > sIdx ? 'bg-amber/60' : 'bg-current/25')"
                                            ></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-center text-[11px] font-bold text-deep-teal/60">
                            Ketuk slide di atas atau gunakan panah untuk menguji alur membaca.
                        </p>
                    </div>
                </div>

                <!-- Navigation Controls for Step 2 -->
                <div class="flex items-center justify-between pt-4 border-t border-paper-dark">
                    <x-button type="button" @click="step = 1" variant="paper" size="md">
                        &larr; Kembali ke Info Dasar
                    </x-button>
                    <x-button type="button" @click="step = 3" variant="kilat" size="md">
                        Lanjut ke Kuis Opsional &rarr;
                    </x-button>
                </div>
            </div>

            <!-- ================= STEP 3: KUIS OPSIONAL ================= -->
            <div x-show="step === 3" class="flex flex-col gap-5 max-w-2xl mx-auto w-full">
                <div class="card-paper p-5 sm:p-6 border-2 border-paper-dark rounded-3xl flex flex-col gap-5">
                    <!-- Toggle Switch -->
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="font-extrabold text-base sm:text-lg text-deep-teal">Pertanyaan di Akhir Bacaan</h3>
                            <p class="text-xs text-deep-teal/70 font-semibold mt-0.5">Tantang pembaca dengan 1 pertanyaan cepat untuk memberi bonus EXP (+5/10 EXP).</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer select-none flex-shrink-0">
                            <input type="checkbox" name="has_quiz" value="1" x-model="hasQuiz" class="sr-only peer">
                            <div class="w-14 h-8 bg-paper-dark peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-paper-dark after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-kilat"></div>
                        </label>
                    </div>

                    <!-- Quiz Form (Shown only if hasQuiz toggle is active) -->
                    <div x-show="hasQuiz" class="flex flex-col gap-5 pt-4 border-t border-paper-dark">
                        <div>
                            <x-input-label for="quiz_question" value="Pertanyaan Kuis *" />
                            <x-text-input
                                id="quiz_question"
                                name="quiz_question"
                                x-model="quizQuestion"
                                placeholder="Contoh: Berapa durasi fokus ideal teknik Pomodoro?"
                                maxlength="300"
                            />
                        </div>

                        <!-- Options (2 to 4) -->
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <x-input-label value="Pilihan Jawaban * (Pilih radio untuk jawaban yang benar)" class="!mb-0" />
                                <button
                                    type="button"
                                    @click="addQuizOption()"
                                    x-show="quizOptions.length < 4"
                                    class="text-xs font-extrabold text-kilat-dark hover:underline flex items-center gap-1 cursor-pointer"
                                >
                                    <x-lucide-plus class="w-3.5 h-3.5 stroke-[3]" />
                                    <span>Tambah Pilihan</span>
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                <template x-for="(opt, oIdx) in quizOptions" :key="oIdx">
                                    <div class="flex items-center gap-2.5">
                                        <!-- Correct Choice Radio -->
                                        <input
                                            type="radio"
                                            name="quiz_correct_index"
                                            :value="oIdx"
                                            x-model="quizCorrectIndex"
                                            class="w-5 h-5 text-kilat border-2 border-paper-dark focus:ring-kilat cursor-pointer"
                                            title="Tandai sebagai jawaban yang benar"
                                        >
                                        <input
                                            type="text"
                                            name="quiz_options[]"
                                            x-model="quizOptions[oIdx]"
                                            :placeholder="`Pilihan jawaban ${oIdx + 1}...`"
                                            class="flex-1 px-3.5 py-2.5 bg-white text-deep-teal font-semibold text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none"
                                        >
                                        <button
                                            type="button"
                                            @click="removeQuizOption(oIdx)"
                                            x-show="quizOptions.length > 2"
                                            class="p-1.5 text-error hover:bg-error/10 rounded-xl cursor-pointer transition-colors"
                                            title="Hapus opsi ini"
                                        >
                                            <x-lucide-x class="w-4 h-4" />
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Explanation -->
                        <div>
                            <x-input-label for="quiz_explanation" value="Penjelasan Singkat (Tampil setelah pembaca menjawab)" />
                            <textarea
                                id="quiz_explanation"
                                name="quiz_explanation"
                                x-model="quizExplanation"
                                rows="2"
                                placeholder="Jelaskan mengapa jawaban tersebut benar secara edukatif..."
                                class="w-full px-3.5 py-2.5 bg-white text-deep-teal font-medium text-xs border-2 border-paper-dark rounded-xl focus:border-kilat focus:outline-none resize-none"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <x-button type="button" @click="step = 2" variant="paper" size="md">
                        &larr; Kembali ke Slide
                    </x-button>
                    <x-button type="button" @click="step = 4" variant="kilat" size="md">
                        Lanjut ke Tinjau & Terbit &rarr;
                    </x-button>
                </div>
            </div>

            <!-- ================= STEP 4: TINJAU & PUBLIKASIKAN ================= -->
            <div x-show="step === 4" class="flex flex-col gap-5 max-w-2xl mx-auto w-full">
                <div class="card-paper p-6 sm:p-7 border-2 border-paper-dark rounded-3xl flex flex-col gap-5">
                    <div>
                        <h3 class="font-extrabold text-xl sm:text-2xl text-deep-teal">Ringkasan Bacaan Kamu</h3>
                        <p class="text-xs text-deep-teal/60 font-semibold mt-1">Periksa kembali detail sebelum bacaan dibagikan ke komunitas Macha.</p>
                    </div>

                    <div class="space-y-3.5 divide-y divide-paper-dark text-xs sm:text-sm">
                        <div class="pt-2 flex justify-between items-start gap-4">
                            <span class="font-bold text-deep-teal/60 flex-shrink-0">Judul:</span>
                            <span class="font-extrabold text-deep-teal text-right" x-text="title"></span>
                        </div>
                        <div class="pt-3 flex justify-between items-center">
                            <span class="font-bold text-deep-teal/60">Jumlah Slide:</span>
                            <span class="font-extrabold text-deep-teal" x-text="`${slides.length} slide`"></span>
                        </div>
                        <div class="pt-3 flex justify-between items-center">
                            <span class="font-bold text-deep-teal/60">Topik Tag:</span>
                            <span class="font-extrabold text-deep-teal" x-text="`${selectedTags.length} topik dipilih`"></span>
                        </div>
                        <div class="pt-3 flex justify-between items-center">
                            <span class="font-bold text-deep-teal/60">Kuis Tambahan:</span>
                            <span class="font-extrabold" :class="hasQuiz ? 'text-kilat-dark' : 'text-deep-teal/60'" x-text="hasQuiz ? 'Ya (Aktif)' : 'Tidak Ada'"></span>
                        </div>
                    </div>

                    <!-- Action selection: Published vs Draft -->
                    <div class="pt-5 border-t border-paper-dark flex flex-col sm:flex-row items-center gap-3">
                        <input type="hidden" name="status" id="readingStatusInput" value="published">

                        <x-button
                            type="button"
                            @click="document.getElementById('readingStatusInput').value = 'published'; document.getElementById('readingCreateForm').submit();"
                            variant="kilat"
                            size="lg"
                            class="w-full sm:flex-1 !rounded-2xl"
                        >
                            <x-lucide-upload-cloud class="w-5 h-5 mr-1.5" />
                            Publikasikan Sekarang
                        </x-button>

                        <x-button
                            type="button"
                            @click="document.getElementById('readingStatusInput').value = 'draft'; document.getElementById('readingCreateForm').submit();"
                            variant="paper"
                            size="lg"
                            class="w-full sm:flex-1 !rounded-2xl"
                        >
                            Simpan sebagai Draf
                        </x-button>
                    </div>
                </div>

                <div class="flex justify-start pt-2">
                    <x-button type="button" @click="step = 3" variant="paper" size="md">
                        &larr; Kembali Edit Kuis
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
