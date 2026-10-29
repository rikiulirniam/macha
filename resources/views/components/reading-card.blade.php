@props([
    'reading',
    'isFirst' => false,
    'isFeed' => false,
])

@php
$user = auth()->user();
$isLiked = $reading->isLikedBy($user);
$isCompleted = $reading->isCompletedBy($user);
$totalSlides = $reading->slides->count();
$creator = $reading->user;
$publicUrl = route('readings.public', ['slug' => $reading->slug]);

// Check existing user progress for quiz
$userProgress = $user ? ($reading->relationLoaded('progresses') ? $reading->progresses->firstWhere('user_id', $user->id) : $reading->progresses()->where('user_id', $user->id)->first()) : null;
$quizAnswered = $userProgress && $userProgress->quiz_answered_at !== null;
$quizCorrect = $userProgress ? (bool) $userProgress->quiz_correct : false;
@endphp

<article
    x-data="{
        currentSlide: 0,
        totalSlides: {{ $totalSlides }},
        liked: {{ $isLiked ? 'true' : 'false' }},
        likesCount: {{ $reading->likes_count }},
        completed: {{ $isCompleted ? 'true' : 'false' }},
        alreadyCompleted: {{ $isCompleted ? 'true' : 'false' }},
        showCompletedScreen: false,
        showQuiz: {{ $quizAnswered ? 'true' : 'false' }},
        quizAnswered: {{ $quizAnswered ? 'true' : 'false' }},
        quizSelected: null,
        quizCorrect: {{ $quizCorrect ? 'true' : ($quizAnswered ? 'false' : 'null') }},
        quizExplanation: @js($reading->quiz->explanation ?? ''),
        commentsOpen: false,
        commentsCount: {{ (int) $reading->comments_count }},
        floatingExp: false,
        floatingExpText: '+10 EXP',
        hasQuiz: {{ $reading->quiz ? 'true' : 'false' }},

        init() {
            const container = this.$refs.slidesContainer;
            container.addEventListener('scroll', () => {
                const width = container.offsetWidth;
                const index = Math.round(container.scrollLeft / (width || 1));
                if (index !== this.currentSlide) {
                    this.currentSlide = index;
                    this.recordProgress(index + 1);
                }
            });
        },
        goToSlide(index) {
            const container = this.$refs.slidesContainer;
            container.scrollTo({
                left: index * container.offsetWidth,
                behavior: 'smooth'
            });
            clearTimeout(this._slideTimer);
            this._slideTimer = setTimeout(() => {
                if (this.currentSlide !== index) {
                    this.currentSlide = index;
                    this.recordProgress(index + 1);
                }
            }, 320);
        },
        openComments() {
            this.commentsOpen = true;
            this.$dispatch('load-comments-{{ $reading->id }}');
        },
        async toggleLike() {
            @guest
                window.location.href = '{{ route('login') }}';
                return;
            @endguest

            this.liked = !this.liked;
            this.likesCount += this.liked ? 1 : -1;

            try {
                const res = await fetch('/readings/{{ $reading->id }}/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                this.likesCount = data.likes_count;
                this.liked = data.liked;
            } catch (e) {
                // rollback on failure
                this.liked = !this.liked;
                this.likesCount += this.liked ? 1 : -1;
            }
        },
        async recordProgress(slideNumber) {
            @guest return; @endguest
            try {
                await fetch('/readings/{{ $reading->id }}/progress', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ slide: slideNumber })
                });
            } catch (e) {}
        },
        async completeReading() {
            @guest
                window.location.href = '{{ route('login') }}';
                return;
            @endguest

            if (this.completed) {
                this.showCompletedScreen = true;
                return;
            }

            try {
                const res = await fetch('/readings/{{ $reading->id }}/complete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                this.completed = true;
                this.showCompletedScreen = true;

                if (data.exp_awarded > 0) {
                    this.alreadyCompleted = false;
                    this.floatingExpText = `+${data.exp_awarded} EXP`;
                    this.floatingExp = true;
                    setTimeout(() => { this.floatingExp = false; }, 1400);

                    // Real-time update desktop status card & any listeners
                    window.dispatchEvent(new CustomEvent('exp-gained', {
                        detail: {
                            exp_awarded: data.exp_awarded,
                            total_exp: data.total_exp,
                            streak: data.current_streak,
                            level: data.level,
                            progress: data.progress_to_next_level
                        }
                    }));

                    // Trigger Achievement Toast & Sound if badge unlocked
                    if (data.badge_unlocked) {
                        window.dispatchEvent(new CustomEvent('achievement', {
                            detail: {
                                name: data.badge_unlocked.name,
                                description: data.badge_unlocked.description,
                                icon: data.badge_unlocked.icon || 'sparkles'
                            }
                        }));
                    }
                } else {
                    this.alreadyCompleted = true;
                    if (data.already_completed) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: 'Bacaan ini sudah pernah kamu selesaikan. EXP hanya dapat diklaim 1 kali per bacaan.',
                                type: 'info'
                            }
                        }));
                    }
                }
            } catch (e) {
                this.showCompletedScreen = true;
            }
        },
        async answerQuiz(index) {
            @guest
                window.location.href = '{{ route('login') }}';
                return;
            @endguest

            if (this.quizAnswered) return;
            this.quizSelected = index;

            try {
                const res = await fetch('/readings/{{ $reading->id }}/quiz', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ option_index: index })
                });
                const data = await res.json();
                this.quizAnswered = true;
                this.quizCorrect = data.is_correct;
                this.quizExplanation = data.explanation || '';

                if (data.exp_awarded > 0) {
                    this.floatingExpText = `+${data.exp_awarded} EXP`;
                    this.floatingExp = true;
                    setTimeout(() => { this.floatingExp = false; }, 1400);

                    // Real-time update desktop status card & any listeners
                    window.dispatchEvent(new CustomEvent('exp-gained', {
                        detail: {
                            exp_awarded: data.exp_awarded,
                            total_exp: data.total_exp,
                            streak: data.current_streak,
                            level: data.level,
                            progress: data.progress_to_next_level
                        }
                    }));
                }

                if (data.badge_unlocked) {
                    window.dispatchEvent(new CustomEvent('achievement', {
                        detail: {
                            name: data.badge_unlocked.name,
                            description: data.badge_unlocked.description,
                            icon: data.badge_unlocked.icon || 'sparkles'
                        }
                    }));
                }
            } catch (e) {
                console.error(e);
            }
        },
        copyShareLink() {
            const url = '{{ $publicUrl }}';
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(() => {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Link berhasil disalin ke clipboard!', type: 'success' } }));
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Link berhasil disalin ke clipboard!', type: 'success' } }));
            }
        }
    }"
    class="feed-snap-card relative w-full h-[100dvh] max-h-[100dvh] bg-paper overflow-hidden flex flex-col justify-between border-b-2 border-paper-dark"
    data-reading-id="{{ $reading->id }}"
>
    <!-- Floating EXP animation badge -->
    <div
        x-show="floatingExp"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-75"
        x-transition:enter-end="opacity-100 -translate-y-6 scale-110"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 -translate-y-6 scale-110"
        x-transition:leave-end="opacity-0 -translate-y-12 scale-90"
        class="absolute top-1/3 left-1/2 -translate-x-1/2 z-50 pointer-events-none"
    >
        <span class="px-4 py-2 rounded-2xl bg-amber text-deep-teal font-extrabold text-xl border-b-4 border-amber-dark flex items-center gap-1.5 shadow-none" x-text="floatingExpText">
        </span>
    </div>

    <!-- First Time Swipe Hint -->
    @if ($isFirst)
        <div
            x-show="currentSlide === 0"
            x-transition:leave="transition ease-out duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute top-28 right-5 z-30 pointer-events-none flex items-center gap-1.5 px-3 py-1 rounded-xl bg-deep-teal/85 text-paper text-xs font-bold animate-pulse border border-white/10 shadow-sm"
        >
            <span>Geser</span>
            <x-lucide-arrow-right class="w-3.5 h-3.5" />
        </div>
    @endif

    <!-- Main Horizontal Slides Snap Container -->
    <div
        x-ref="slidesContainer"
        class="slides-snap-container relative flex-1 w-full h-full"
    >
        @foreach ($reading->slides as $index => $slide)
            <x-slide
                :slide="$slide"
                :index="$index"
                :total="$totalSlides"
                :reading="$reading"
            />
        @endforeach
    </div>

    <!-- Desktop Left & Right Arrow Buttons to flip slides easily -->
    <div class="hidden md:flex items-center justify-between absolute inset-y-0 inset-x-2 z-20 pointer-events-none">
        <button
            type="button"
            @click="goToSlide(currentSlide - 1)"
            x-show="currentSlide > 0"
            class="w-10 h-10 rounded-full bg-deep-teal/70 hover:bg-deep-teal text-paper flex items-center justify-center pointer-events-auto transition-transform hover:scale-110 active:scale-95 shadow-lg border border-white/20 cursor-pointer"
            title="Slide Sebelumnya"
            aria-label="Slide Sebelumnya"
        >
            <x-lucide-chevron-left class="w-6 h-6 stroke-[2.5]" />
        </button>
        <div x-show="currentSlide === 0" class="w-10"></div>

        <button
            type="button"
            @click="goToSlide(currentSlide + 1)"
            x-show="currentSlide < totalSlides - 1"
            class="w-10 h-10 rounded-full bg-deep-teal/70 hover:bg-deep-teal text-paper flex items-center justify-center pointer-events-auto transition-transform hover:scale-110 active:scale-95 shadow-lg border border-white/20 cursor-pointer"
            title="Slide Selanjutnya"
            aria-label="Slide Selanjutnya"
        >
            <x-lucide-chevron-right class="w-6 h-6 stroke-[2.5]" />
        </button>
    </div>

    <!-- Desktop Nav Arrows End -->

    <!-- Bottom Metadata Overlay (Creator, Title, Tag, Reading Time, and Finish Button on last slide) -->
    <div class="absolute bottom-0 inset-x-0 z-20 px-4 sm:px-5 pt-8 {{ $isFeed ? 'pb-20 md:pb-5' : 'pb-5' }} bg-gradient-to-t from-deep-teal via-deep-teal/90 to-transparent flex flex-col pointer-events-none">
        <!-- Right Vertical Action Column (Like, Comment, Share) positioned above the overlay -->
        <div class="absolute bottom-full right-3 sm:right-4 mb-3 z-30 flex flex-col items-center gap-3 sm:gap-4 pointer-events-auto">
            <!-- Like Action Button -->
            <button
                type="button"
                @click="toggleLike()"
                class="flex flex-col items-center gap-1 focus:outline-none group select-none cursor-pointer"
                aria-label="Sukai bacaan"
            >
                <div
                    :class="liked ? 'bg-error text-paper border-b-4 border-red-800' : 'bg-paper/90 text-deep-teal border-2 border-paper-dark border-b-4 hover:bg-white'"
                    class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center transition-transform active:scale-90"
                >
                    <x-lucide-heart
                        class="w-5 h-5 sm:w-6 sm:h-6 transition-transform"
                        ::class="liked ? 'fill-current animate-like' : ''"
                    />
                </div>
                <span
                    class="text-[10px] sm:text-[11px] font-extrabold px-1.5 py-0.5 rounded-lg bg-deep-teal/70 text-paper backdrop-blur-none"
                    x-text="likesCount"
                >
                    {{ $reading->likes_count }}
                </span>
            </button>

            <!-- Comment Action Button -->
            <button
                type="button"
                @click="openComments()"
                class="flex flex-col items-center gap-1 focus:outline-none group select-none cursor-pointer"
                aria-label="Lihat komentar"
            >
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center bg-paper/90 text-deep-teal border-2 border-paper-dark border-b-4 hover:bg-white transition-transform active:scale-90">
                    <x-lucide-message-circle class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
                <span
                    class="text-[10px] sm:text-[11px] font-extrabold px-1.5 py-0.5 rounded-lg bg-deep-teal/70 text-paper backdrop-blur-none"
                    x-text="commentsCount"
                >
                    {{ $reading->comments_count }}
                </span>
            </button>

            <!-- Share Action Button -->
            <button
                type="button"
                @click="copyShareLink()"
                class="flex flex-col items-center gap-1 focus:outline-none group select-none cursor-pointer"
                aria-label="Salin link bacaan"
            >
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center bg-paper/90 text-deep-teal border-2 border-paper-dark border-b-4 hover:bg-white transition-transform active:scale-90">
                    <x-lucide-share-2 class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
                <span class="text-[10px] sm:text-[11px] font-extrabold px-1.5 py-0.5 rounded-lg bg-deep-teal/70 text-paper backdrop-blur-none">
                    Bagikan
                </span>
            </button>
        </div>

        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2 pointer-events-auto flex-wrap">
                <!-- Creator Avatar -->
                <a href="{{ route('profile.user', ['username' => $creator->username]) }}" class="flex items-center gap-1.5 group flex-shrink-0">
                    <span class="w-7 h-7 rounded-lg bg-amber text-deep-teal font-extrabold text-[11px] flex items-center justify-center border border-amber-dark">
                        {{ $creator->initials }}
                    </span>
                    <span class="font-extrabold text-xs text-paper group-hover:underline truncate max-w-[120px] sm:max-w-[160px]">
                        {{ $creator->name }}
                    </span>
                </a>

                <!-- Tag Chips -->
                @foreach ($reading->tags->take(2) as $tagIdx => $tag)
                    <span class="px-2 py-0.5 rounded-md bg-black/40 border border-white/20 text-paper text-[10px] font-bold truncate max-w-[110px] {{ $tagIdx > 0 ? 'hidden xs:inline-flex sm:inline-flex' : '' }}">
                        {{ $tag->name }}
                    </span>
                @endforeach

                <!-- Estimated Reading Minutes -->
                <span class="px-1.5 py-0.5 rounded-md bg-kilat text-deep-teal text-[10px] font-extrabold border border-kilat-dark ml-auto flex-shrink-0">
                    {{ $reading->reading_minutes }} mnt
                </span>
            </div>

            <!-- Reading Title -->
            <h2 class="font-sans font-extrabold text-sm sm:text-base text-paper line-clamp-2 leading-snug pointer-events-auto">
                {{ $reading->title }}
            </h2>

            <!-- Slide Progress Segment Lines (Dibawah Judul) -->
            <div class="flex items-center gap-1 sm:gap-1.5 py-0.5 pointer-events-auto" aria-label="Indikator slide bacaan">
                @foreach ($reading->slides as $i => $s)
                    <button
                        type="button"
                        @click="goToSlide({{ $i }})"
                        class="h-1 sm:h-1.5 flex-1 rounded-full cursor-pointer transition-all duration-200 focus:outline-none"
                        :class="currentSlide === {{ $i }} ? 'bg-amber shadow-sm ring-1 ring-amber-dark' : (currentSlide > {{ $i }} ? 'bg-amber/60' : 'bg-white/30 hover:bg-white/50')"
                        title="Buka slide {{ $i + 1 }}"
                        aria-label="Slide {{ $i + 1 }}"
                    ></button>
                @endforeach
            </div>
        </div>

        <!-- Last Slide "Selesai" Action Button (Smooth CSS Grid Collapse/Expand) -->
        <div
            class="finish-button-collapse"
            :class="{ 'is-expanded': currentSlide === totalSlides - 1 }"
            :aria-hidden="currentSlide !== totalSlides - 1 ? 'true' : 'false'"
        >
            <div class="pointer-events-auto">
                <x-button
                    type="button"
                    @click="completeReading()"
                    variant="kilat"
                    size="sm"
                    class="w-full !rounded-xl !py-2.5 text-xs sm:text-sm font-extrabold transition-all"
                    x-bind:class="completed ? '!bg-paper !text-deep-teal border-2 !border-paper-dark hover:!bg-paper-dark/40 shadow-none' : '!bg-kilat !text-deep-teal'"
                >
                    <span :class="completed ? 'text-deep-teal/60' : 'text-deep-teal'" class="inline-flex mr-1.5">
                        <x-lucide-check-circle-2 class="w-4 h-4" />
                    </span>
                    <span x-text="completed ? '✓ Sudah Selesai Dibaca (Lihat Ringkasan)' : 'Selesai Membaca (+10 EXP)'"></span>
                </x-button>
            </div>
        </div>
    </div>

    <!-- Completion / Wrap-up Screen Modal (Layar Penutup) -->
    <div
        x-cloak
        x-show="showCompletedScreen"
        class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center p-4 bg-deep-teal/80"
        role="dialog"
    >
        <div
            x-show="showCompletedScreen"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-paper border-2 border-paper-dark rounded-3xl p-6 w-full max-w-sm flex flex-col gap-4 text-center select-none shadow-none"
        >
            <!-- Badge Icon Celebration -->
            <div class="mx-auto w-16 h-16 rounded-2xl bg-amber text-deep-teal border-b-4 border-amber-dark flex items-center justify-center">
                <x-lucide-sparkles class="w-8 h-8 fill-deep-teal" />
            </div>

            <div>
                <template x-if="!alreadyCompleted">
                    <span class="inline-block px-3 py-1 rounded-xl bg-kilat text-deep-teal text-xs font-extrabold border-b-2 border-kilat-dark mb-1">
                        +10 EXP DIDAPATKAN
                    </span>
                </template>
                <template x-if="alreadyCompleted">
                    <span class="inline-block px-3 py-1 rounded-xl bg-paper-dark text-deep-teal/80 text-xs font-extrabold border border-paper-dark mb-1">
                        EXP SUDAH DIKLAIM SEBELUMNYA
                    </span>
                </template>
                <h3 class="font-extrabold text-xl text-deep-teal tracking-tight">Bacaan Selesai!</h3>
                <p class="text-xs text-deep-teal/70 font-semibold mt-1" x-text="alreadyCompleted ? 'Kamu sudah pernah menyelesaikan bacaan ini. EXP hanya didapatkan satu kali.' : 'Kamu semakin pintar satu langkah hari ini.'"></p>
            </div>

            <!-- Streak & Daily Goal Progress -->
            <div class="card-paper p-3.5 border-2 border-paper-dark rounded-2xl flex items-center justify-around text-xs font-bold">
                <div class="flex items-center gap-1.5 text-deep-teal">
                    <x-lucide-flame class="w-5 h-5 text-amber fill-amber" />
                    <span x-text="alreadyCompleted ? 'Streak Terjaga' : 'Streak Hidup Hari Ini'">Streak Hidup Hari Ini</span>
                </div>
                <div class="w-px h-6 bg-paper-dark"></div>
                <div class="flex items-center gap-1.5 text-deep-teal">
                    <x-lucide-check-circle-2 class="w-5 h-5 text-kilat" />
                    <span>1 Bacaan Selesai</span>
                </div>
            </div>

            <!-- Optional Quiz Trigger Card (Pertanyaan Cepat) -->
            @if ($reading->quiz)
                <div
                    x-show="!showQuiz && !quizAnswered"
                    class="p-4 rounded-2xl bg-amber/20 border-2 border-amber/40 flex flex-col gap-2 text-left"
                >
                    <div class="flex items-center gap-2">
                        <x-lucide-help-circle class="w-5 h-5 text-deep-teal" />
                        <span class="font-extrabold text-xs text-deep-teal">Kuis Cepat (+Bonus EXP)</span>
                    </div>
                    <p class="text-xs text-deep-teal/80 font-medium">
                        Mau jawab 1 pertanyaan cepat tentang bacaan ini?
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <!-- Jawab Button -->
                        <x-button
                            type="button"
                            @click="showQuiz = true"
                            variant="amber"
                            size="sm"
                            class="flex-1 !rounded-xl"
                        >
                            Jawab (+10 EXP)
                        </x-button>
                        <!-- Lewati Button with EQUAL visual balance -->
                        <x-button
                            type="button"
                            @click="showCompletedScreen = false"
                            variant="paper"
                            size="sm"
                            class="flex-1 !rounded-xl"
                        >
                            Lewati
                        </x-button>
                    </div>
                </div>

                <!-- Active Quiz Card -->
                <div
                    x-show="showQuiz || quizAnswered"
                    class="p-4 rounded-2xl bg-paper border-2 border-paper-dark flex flex-col gap-3 text-left"
                >
                    <p class="font-extrabold text-xs text-deep-teal">
                        {{ $reading->quiz->question }}
                    </p>

                    <div class="flex flex-col gap-1.5">
                        @foreach ($reading->quiz->options as $optIdx => $opt)
                            <button
                                type="button"
                                @click="answerQuiz({{ $optIdx }})"
                                :disabled="quizAnswered"
                                :class="{
                                    'bg-kilat text-deep-teal border-b-4 border-kilat-dark': quizAnswered && quizCorrect && quizSelected === {{ $optIdx }},
                                    'bg-error text-paper border-b-4 border-red-800': quizAnswered && !quizCorrect && quizSelected === {{ $optIdx }},
                                    'bg-kilat/30 border-2 border-kilat': quizAnswered && {{ $reading->quiz->correct_index }} === {{ $optIdx }},
                                    'bg-paper text-deep-teal border-2 border-paper-dark border-b-4 hover:bg-white': !quizAnswered
                                }"
                                class="w-full text-left p-3 rounded-xl font-bold text-xs transition-all active:translate-y-0.5 active:border-b-2 cursor-pointer disabled:cursor-default"
                            >
                                <span>{{ $opt }}</span>
                            </button>
                        @endforeach
                    </div>

                    <!-- Explanation -->
                    <div x-show="quizAnswered" class="pt-2 text-xs font-semibold text-deep-teal/80 leading-relaxed">
                        <template x-if="quizCorrect">
                            <span class="text-kilat-dark font-extrabold block mb-1">✓ Jawaban Benar!</span>
                        </template>
                        <template x-if="!quizCorrect">
                            <span class="text-error font-extrabold block mb-1">Kurang Tepat</span>
                        </template>
                        <p x-text="quizExplanation"></p>
                    </div>
                </div>
            @endif

            <!-- Close / Next Reading Button -->
            <x-button
                type="button"
                @click="showCompletedScreen = false"
                variant="kilat"
                size="md"
                class="w-full"
            >
                Lanjut Membaca &rarr;
            </x-button>
        </div>
    </div>

    <!-- Comments Sheet Component -->
    <x-sheet name="commentsOpen">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <span>Komentar</span>
                <span class="text-xs sm:text-sm font-extrabold px-2.5 py-0.5 rounded-full bg-paper-dark text-deep-teal" x-text="commentsCount">{{ $reading->comments_count }}</span>
            </div>
        </x-slot:header>

        <div
            x-data="{
                comments: [],
                page: 1,
                hasMore: false,
                loading: false,
                loadingMore: false,
                newComment: '',
                submitting: false,
                errorMessage: '',
                loaded: false,
                async loadComments(reset = true) {
                    if (reset) {
                        this.page = 1;
                        this.loading = true;
                    }
                    try {
                        const res = await fetch(`/readings/{{ $reading->id }}/comments?page=${this.page}&per_page=10`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        this.comments = reset ? (data.comments || []) : [...this.comments, ...(data.comments || [])];
                        this.hasMore = !!data.has_more;
                        this.loaded = true;
                        if (data.total !== undefined) {
                            commentsCount = data.total;
                        }
                    } catch (e) {
                        this.loaded = true;
                    } finally {
                        this.loading = false;
                    }
                },
                async loadMore() {
                    if (!this.hasMore || this.loadingMore) return;
                    this.loadingMore = true;
                    const nextPage = this.page + 1;
                    try {
                        const res = await fetch(`/readings/{{ $reading->id }}/comments?page=${nextPage}&per_page=10`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.comments) {
                            this.comments = [...this.comments, ...data.comments];
                            this.page = nextPage;
                            this.hasMore = !!data.has_more;
                            if (data.total !== undefined) {
                                commentsCount = data.total;
                            }
                        }
                    } catch (e) {
                    } finally {
                        this.loadingMore = false;
                    }
                },
                async postComment() {
                    if (!this.newComment.trim() || this.submitting) return;
                    this.submitting = true;
                    this.errorMessage = '';

                    try {
                        const res = await fetch('/readings/{{ $reading->id }}/comments', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ body: this.newComment })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.comments.unshift(data.comment);
                            this.newComment = '';
                            commentsCount++;
                        } else {
                            this.errorMessage = data.message || 'Gagal mengirim komentar.';
                        }
                    } catch (e) {
                        this.errorMessage = 'Terjadi kesalahan jaringan.';
                    } finally {
                        this.submitting = false;
                    }
                },
                async deleteComment(id) {
                    if (!confirm('Hapus komentar ini?')) return;
                    try {
                        const res = await fetch(`/comments/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            }
                        });
                        if (res.ok) {
                            this.comments = this.comments.filter(c => c.id !== id);
                            commentsCount = Math.max(0, commentsCount - 1);
                        }
                    } catch (e) {}
                }
            }"
            x-init="$watch('commentsOpen', val => { if (val) loadComments(true); })"
            @load-comments-{{ $reading->id }}.window="loadComments(true)"
            class="flex-1 flex flex-col gap-3 overflow-hidden min-h-0"
        >
            <!-- Comment Input Form -->
            @auth
                <form @submit.prevent="postComment()" class="flex flex-col gap-2 flex-shrink-0">
                    <div class="relative">
                        <textarea
                            x-model="newComment"
                            maxlength="300"
                            placeholder="Tulis tanggapan atau opinimu (maks 300 karakter)..."
                            rows="2"
                            class="w-full px-4 py-3 bg-white text-deep-teal font-medium placeholder:text-deep-teal/40 border-2 border-paper-dark rounded-xl focus:border-kilat focus:ring-2 focus:ring-kilat/20 focus:outline-none resize-none text-xs"
                        ></textarea>
                        <span class="absolute right-3 bottom-2 text-[10px] text-deep-teal/50 font-bold" x-text="`${newComment.length}/300`"></span>
                    </div>

                    <div x-show="errorMessage" class="text-xs text-error font-bold flex items-center gap-1.5" x-text="errorMessage"></div>

                    <div class="flex justify-end">
                        <x-button type="submit" variant="kilat" size="sm" x-bind:disabled="!newComment.trim() || submitting">
                            Kirim Komentar
                        </x-button>
                    </div>
                </form>
            @else
                <div class="p-3 bg-paper-dark/50 rounded-xl text-center text-xs font-bold text-deep-teal flex-shrink-0">
                    <a href="{{ route('login') }}" class="text-kilat-dark underline">Masuk</a> untuk bergabung dalam diskusi.
                </div>
            @endauth

            <!-- Comments List -->
            <div class="flex-1 flex flex-col gap-3 divide-y divide-paper-dark/60 overflow-y-auto min-h-0 pr-1 overscroll-contain">
                <!-- Loading State -->
                <template x-if="loading">
                    <div class="py-8 flex flex-col items-center justify-center gap-2 text-deep-teal/60">
                        <svg class="animate-spin w-5 h-5 text-deep-teal" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span class="text-xs font-semibold">Memuat komentar...</span>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="comments.length === 0 && loaded && !loading">
                    <p class="text-xs text-deep-teal/60 text-center py-6 font-semibold">
                        Belum ada komentar. Jadilah yang pertama berkomentar!
                    </p>
                </template>

                <!-- Comments Items -->
                <template x-for="c in comments" :key="c.id">
                    <div class="pt-3 first:pt-0 flex flex-col gap-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-extrabold text-deep-teal" x-text="c.user.name"></span>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-deep-teal/60 font-semibold" x-text="c.created_at_human"></span>
                                <template x-if="c.can_delete">
                                    <button
                                        type="button"
                                        @click="deleteComment(c.id)"
                                        class="text-deep-teal/40 hover:text-error text-xs font-bold"
                                        title="Hapus komentar"
                                    >
                                        &times;
                                    </button>
                                </template>
                            </div>
                        </div>
                        <p class="text-xs text-deep-teal font-normal leading-relaxed" x-text="c.body"></p>
                    </div>
                </template>

                <!-- Load More Button -->
                <template x-if="hasMore && !loading">
                    <div class="pt-3 pb-2 flex justify-center">
                        <button
                            type="button"
                            @click="loadMore()"
                            :disabled="loadingMore"
                            class="px-4 py-2 rounded-xl bg-paper-dark/70 hover:bg-paper-dark text-deep-teal font-extrabold text-xs flex items-center gap-2 transition-colors cursor-pointer disabled:opacity-50"
                        >
                            <template x-if="loadingMore">
                                <svg class="animate-spin w-3.5 h-3.5 text-deep-teal" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <span x-text="loadingMore ? 'Memuat komentar...' : 'Lihat lebih banyak komentar'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </x-sheet>
</article>
