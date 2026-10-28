@props([
    'slide',
    'index' => 0,
    'total' => 1,
    'reading',
])

@php
// Valid color pairings:
// paper: bg #F7F5EF, text #102A2A
// deep-teal: bg #102A2A, text #F7F5EF
// kilat: bg #19A974, text #102A2A
// amber: bg #F2C14E, text #102A2A
$bgClass = match ($slide->bg_color) {
    'teal' => 'bg-deep-teal text-paper',
    'kilat' => 'bg-kilat text-deep-teal',
    'amber' => 'bg-amber text-deep-teal',
    default => 'bg-paper text-deep-teal',
};

$mutedTextClass = match ($slide->bg_color) {
    'teal' => 'text-paper/70',
    default => 'text-deep-teal/70',
};

$quoteBorderClass = match ($slide->bg_color) {
    'teal' => 'border-paper/40',
    'kilat' => 'border-kilat-dark',
    'amber' => 'border-amber-dark',
    default => 'border-paper-dark',
};

$contentPaddingClass = in_array($slide->type, ['image', 'audio'])
    ? 'pr-0'
    : 'pr-14 sm:pr-16 md:pr-0';
@endphp

<div
    class="slide-snap-item relative flex flex-col justify-between p-6 sm:p-8 {{ $bgClass }} select-none overflow-hidden"
    data-slide-index="{{ $index }}"
    data-slide-id="{{ $slide->id }}"
>
    <!-- Slide Header: Type indicator or subtle tag -->
    <div class="flex items-center justify-between pt-16 sm:pt-14 md:pt-10 z-10">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold uppercase tracking-wider {{ $slide->bg_color === 'teal' ? 'bg-teal-soft/60 text-paper' : 'bg-black/10 text-deep-teal' }}">
            @if ($slide->type === 'quote')
                <x-lucide-quote class="w-3.5 h-3.5" />
                <span>Kutipan</span>
            @elseif ($slide->type === 'image')
                <x-lucide-image class="w-3.5 h-3.5" />
                <span>Visual</span>
            @elseif ($slide->type === 'audio')
                <x-lucide-headphones class="w-3.5 h-3.5" />
                <span>Audio</span>
            @else
                <x-lucide-book-open class="w-3.5 h-3.5" />
                <span>Bacaan</span>
            @endif
        </span>

        <span class="text-xs font-extrabold {{ $mutedTextClass }}">
            {{ $index + 1 }} / {{ $total }}
        </span>
    </div>

    <!-- Slide Content based on type -->
    <div class="flex-1 flex flex-col justify-center my-auto py-2 sm:py-4 {{ $contentPaddingClass }} z-10 overflow-y-auto max-h-[58vh] scrollbar-none w-full">
        @if ($slide->type === 'quote')
            <!-- Quote Type -->
            <div class="flex flex-col gap-4 border-l-4 {{ $quoteBorderClass }} pl-5 sm:pl-6 my-auto">
                <blockquote class="font-serif text-xl sm:text-2xl font-bold leading-relaxed italic">
                    &ldquo;{{ $slide->body }}&rdquo;
                </blockquote>
                @if ($slide->source)
                    <cite class="text-sm font-extrabold not-italic {{ $mutedTextClass }} flex items-center gap-2">
                        <span class="w-4 h-0.5 bg-current"></span>
                        <span>{{ $slide->source }}</span>
                    </cite>
                @endif
            </div>

        @elseif ($slide->type === 'image')
            <!-- Image / Infographic / Comic Panel Type -->
            <div class="flex flex-col items-center justify-center gap-4 my-auto w-full max-w-sm sm:max-w-md mx-auto">
                @if ($slide->media_path)
                    <div class="w-full max-h-[42vh] rounded-2xl overflow-hidden border-2 border-black/10 flex items-center justify-center bg-black/5">
                        <img
                            src="{{ asset('storage/' . $slide->media_path) }}"
                            alt="{{ $reading->title }}"
                            class="w-full h-full object-contain max-h-[40vh] select-none pointer-events-none"
                            loading="lazy"
                        >
                    </div>
                @endif
                @if ($slide->body)
                    <p class="font-sans text-sm sm:text-base font-bold text-center leading-snug px-2">
                        {{ $slide->body }}
                    </p>
                @endif
            </div>

        @elseif ($slide->type === 'audio')
            <!-- Custom Audio Player Type -->
            <div
                x-data="{
                    playing: false,
                    progress: 0,
                    duration: 0,
                    currentTime: 0,
                    volume: 1,
                    muted: false,
                    previousVolume: 1,
                    audio: null,
                    init() {
                        this.audio = this.$refs.audioElement;
                        if (this.audio) {
                            this.audio.volume = this.volume;
                            this.audio.addEventListener('timeupdate', () => {
                                this.currentTime = this.audio.currentTime;
                                this.progress = (this.audio.currentTime / (this.audio.duration || 1)) * 100;
                            });
                            this.audio.addEventListener('loadedmetadata', () => {
                                this.duration = this.audio.duration;
                            });
                            this.audio.addEventListener('ended', () => {
                                this.playing = false;
                                this.progress = 0;
                            });
                        }
                    },
                    toggle() {
                        if (!this.audio) return;
                        if (this.playing) {
                            this.audio.pause();
                            this.playing = false;
                        } else {
                            this.audio.play();
                            this.playing = true;
                        }
                    },
                    seek(event) {
                        if (!this.audio || !this.duration) return;
                        const rect = event.currentTarget.getBoundingClientRect();
                        const clickX = event.clientX - rect.left;
                        const ratio = Math.max(0, Math.min(1, clickX / rect.width));
                        this.audio.currentTime = ratio * this.duration;
                        this.progress = ratio * 100;
                    },
                    updateVolume() {
                        if (!this.audio) return;
                        this.audio.volume = this.volume;
                        this.muted = this.volume === 0;
                        this.audio.muted = this.muted;
                    },
                    toggleMute() {
                        if (!this.audio) return;
                        this.muted = !this.muted;
                        this.audio.muted = this.muted;
                        if (this.muted) {
                            this.previousVolume = this.volume > 0 ? this.volume : 1;
                            this.volume = 0;
                        } else {
                            this.volume = this.previousVolume > 0 ? this.previousVolume : 1;
                            this.audio.volume = this.volume;
                        }
                    },
                    formatTime(sec) {
                        if (!sec || isNaN(sec)) return '0:00';
                        const m = Math.floor(sec / 60);
                        const s = Math.floor(sec % 60);
                        return `${m}:${s < 10 ? '0' : ''}${s}`;
                    }
                }"
                class="flex flex-col items-center justify-center gap-5 my-auto p-5 sm:p-6 rounded-3xl bg-black/10 border-2 border-black/10 w-full max-w-sm sm:max-w-md mx-auto"
            >
                @if ($slide->media_path)
                    <audio x-ref="audioElement" src="{{ asset('storage/' . $slide->media_path) }}" preload="metadata"></audio>
                @endif

                <!-- Play/Pause Big Tactile Button -->
                <button
                    type="button"
                    @click="toggle()"
                    class="w-20 h-20 rounded-full flex items-center justify-center bg-kilat text-deep-teal border-b-4 border-kilat-dark active:translate-y-1 active:border-b-0 cursor-pointer shadow-none transition-all hover:scale-105"
                    aria-label="Putar Audio"
                >
                    <template x-if="!playing">
                        <x-lucide-play class="w-8 h-8 fill-current translate-x-0.5" />
                    </template>
                    <template x-if="playing">
                        <x-lucide-pause class="w-8 h-8 fill-current" />
                    </template>
                </button>

                <!-- Track & Duration (Clickable Seek Bar) -->
                <div class="w-full flex flex-col gap-2">
                    <div
                        @click="seek($event)"
                        class="w-full bg-black/20 hover:bg-black/30 h-3 rounded-full overflow-hidden border border-black/10 cursor-pointer transition-colors"
                        title="Klik untuk geser durasi"
                    >
                        <div class="bg-kilat h-full rounded-full transition-all duration-150 pointer-events-none" :style="`width: ${progress}%`"></div>
                    </div>
                    <div class="flex justify-between text-xs font-bold {{ $mutedTextClass }}">
                        <span x-text="formatTime(currentTime)">0:00</span>
                        <span x-text="formatTime(duration)">--:--</span>
                    </div>
                </div>

                <!-- Volume Controller Bar -->
                <div class="w-full flex items-center justify-between gap-3 pt-3 border-t border-black/10">
                    <button
                        type="button"
                        @click="toggleMute()"
                        class="flex items-center gap-1.5 px-2 py-1.5 rounded-xl hover:bg-black/10 active:scale-95 transition-all text-current font-bold"
                        :title="muted || volume === 0 ? 'Bunyikan (Unmute)' : 'Bisukan (Mute)'"
                    >
                        <template x-if="muted || volume === 0">
                            <x-lucide-volume-x class="w-4 h-4 stroke-[2.5] text-error" />
                        </template>
                        <template x-if="!muted && volume > 0 && volume < 0.5">
                            <x-lucide-volume-1 class="w-4 h-4 stroke-[2.5]" />
                        </template>
                        <template x-if="!muted && volume >= 0.5">
                            <x-lucide-volume-2 class="w-4 h-4 stroke-[2.5]" />
                        </template>
                        <span class="text-[11px] font-extrabold uppercase tracking-wide opacity-80" x-text="muted || volume === 0 ? 'Mute' : Math.round(volume * 100) + '%'"></span>
                    </button>

                    <div class="flex items-center gap-2 flex-1 max-w-[160px] sm:max-w-[200px]">
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.01"
                            x-model.number="volume"
                            @input="updateVolume()"
                            :style="`background: linear-gradient(to right, #19A974 0%, #19A974 ${Math.round((muted ? 0 : volume) * 100)}%, rgba(0, 0, 0, 0.2) ${Math.round((muted ? 0 : volume) * 100)}%, rgba(0, 0, 0, 0.2) 100%)`"
                            class="w-full h-2 rounded-lg appearance-none cursor-pointer accent-kilat focus:outline-none transition-all"
                            aria-label="Pengatur Volume Audio"
                            title="Atur Volume"
                        >
                    </div>
                </div>

                @if ($slide->body)
                    <p class="font-serif text-sm italic text-center {{ $mutedTextClass }} mt-1">
                        &ldquo;{{ $slide->body }}&rdquo;
                    </p>
                @endif
            </div>

        @else
            <!-- Standard Text Micro-Article (Literata font, min 18px, max ~90 words) -->
            <div class="my-auto font-serif text-[18px] sm:text-[20px] leading-[1.65] font-normal tracking-normal whitespace-pre-line">
                {{ $slide->body }}
            </div>
        @endif
    </div>

    <!-- Space reservation for bottom overlay (calibrated for mobile metadata + finish button) -->
    <div class="h-44 sm:h-36 md:h-28 flex-shrink-0"></div>
</div>
