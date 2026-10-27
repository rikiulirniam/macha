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
@endphp

<div
    class="slide-snap-item relative flex flex-col justify-between p-6 sm:p-8 {{ $bgClass }} select-none overflow-hidden"
    data-slide-index="{{ $index }}"
    data-slide-id="{{ $slide->id }}"
>
    <!-- Slide Header: Type indicator or subtle tag -->
    <div class="flex items-center justify-between pt-14 sm:pt-10 z-10">
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
    <div class="flex-1 flex flex-col justify-center my-auto py-4 z-10 overflow-y-auto max-h-[60vh] scrollbar-none">
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
            <div class="flex flex-col items-center justify-center gap-4 my-auto w-full">
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
                    audio: null,
                    init() {
                        this.audio = this.$refs.audioElement;
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
                    formatTime(sec) {
                        const m = Math.floor(sec / 60);
                        const s = Math.floor(sec % 60);
                        return `${m}:${s < 10 ? '0' : ''}${s}`;
                    }
                }"
                class="flex flex-col items-center justify-center gap-6 my-auto p-6 rounded-3xl bg-black/10 border-2 border-black/10"
            >
                @if ($slide->media_path)
                    <audio x-ref="audioElement" src="{{ asset('storage/' . $slide->media_path) }}" preload="metadata"></audio>
                @endif

                <!-- Play/Pause Big Tactile Button -->
                <button
                    type="button"
                    @click="toggle()"
                    class="w-20 h-20 rounded-full flex items-center justify-center bg-kilat text-deep-teal border-b-4 border-kilat-dark active:translate-y-1 active:border-b-0 cursor-pointer shadow-none transition-all"
                    aria-label="Putar Audio"
                >
                    <template x-if="!playing">
                        <x-lucide-play class="w-8 h-8 fill-current translate-x-0.5" />
                    </template>
                    <template x-if="playing">
                        <x-lucide-pause class="w-8 h-8 fill-current" />
                    </template>
                </button>

                <!-- Track & Duration -->
                <div class="w-full flex flex-col gap-2">
                    <div class="w-full bg-black/20 h-3 rounded-full overflow-hidden border border-black/10">
                        <div class="bg-kilat h-full rounded-full transition-all duration-150" :style="`width: ${progress}%`"></div>
                    </div>
                    <div class="flex justify-between text-xs font-bold {{ $mutedTextClass }}">
                        <span x-text="formatTime(currentTime)">0:00</span>
                        <span x-text="formatTime(duration)">--:--</span>
                    </div>
                </div>

                @if ($slide->body)
                    <p class="font-serif text-sm italic text-center {{ $mutedTextClass }}">
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

    <!-- Space reservation for bottom overlay -->
    <div class="h-28 sm:h-32 flex-shrink-0"></div>
</div>
