<x-app-layout :maxWidth="'w-full max-w-xl'">
    <div class="p-4 sm:p-6 pb-16 sm:pb-8 w-full flex flex-col gap-6 select-none">
        <!-- Top Profile Card -->
        <div class="card-paper p-6 border-2 border-paper-dark rounded-3xl flex flex-col gap-5">
            <!-- User Info Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <!-- Avatar: Uploaded Photo or Palette Initials -->
                    @if ($user->avatar_path)
                        <img
                            src="{{ asset('storage/' . $user->avatar_path) }}"
                            alt="{{ $user->name }}"
                            class="w-16 h-16 rounded-2xl object-cover border-2 border-paper-dark"
                        >
                    @else
                        <span class="w-16 h-16 rounded-2xl bg-deep-teal text-paper font-black text-2xl flex items-center justify-center border-2 border-teal-soft">
                            {{ $user->initials }}
                        </span>
                    @endif

                    <div class="flex flex-col">
                        <h1 class="font-extrabold text-xl text-deep-teal leading-tight">{{ $user->name }}</h1>
                        <span class="text-xs font-bold text-deep-teal/60">{{ '@' . $user->username }}</span>
                        <!-- Member since -->
                        <span class="text-[10px] text-deep-teal/50 font-semibold mt-1">
                            Bergabung {{ $user->created_at->locale('id')->isoFormat('MMMM Y') }}
                        </span>
                    </div>
                </div>

                @if ($isOwner)
                    <x-button as="a" href="{{ route('profile.edit') }}" variant="paper" size="sm">
                        <x-lucide-settings class="w-3.5 h-3.5 mr-1" />
                        Edit
                    </x-button>
                @endif
            </div>

            <!-- Stats Bar (Streak, Total EXP, Weekly EXP) -->
            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-paper-dark text-center">
                <!-- Streak -->
                <div class="p-3 rounded-2xl bg-paper border border-paper-dark flex flex-col items-center">
                    <div class="flex items-center gap-1 text-deep-teal/60 mb-0.5">
                        <x-lucide-flame class="w-4 h-4 text-amber fill-amber" />
                        <span class="text-[10px] font-bold">Streak</span>
                    </div>
                    <span class="font-black text-lg text-deep-teal">{{ $user->current_streak }} Hari</span>
                    <span class="text-[9px] text-deep-teal/50 font-semibold">Rekor: {{ $user->longest_streak }} hari</span>
                </div>

                <!-- Total EXP -->
                <div class="p-3 rounded-2xl bg-paper border border-paper-dark flex flex-col items-center">
                    <div class="flex items-center gap-1 text-deep-teal/60 mb-0.5">
                        <x-lucide-zap class="w-4 h-4 text-amber fill-amber" />
                        <span class="text-[10px] font-bold">Total EXP</span>
                    </div>
                    <span class="font-black text-lg text-deep-teal">{{ $user->total_exp }}</span>
                    <span class="text-[9px] text-deep-teal/50 font-semibold">Level {{ $user->level }}</span>
                </div>

                <!-- Weekly EXP -->
                <div class="p-3 rounded-2xl bg-paper border border-paper-dark flex flex-col items-center">
                    <div class="flex items-center gap-1 text-deep-teal/60 mb-0.5">
                        <x-lucide-trophy class="w-4 h-4 text-kilat" />
                        <span class="text-[10px] font-bold">Minggu Ini</span>
                    </div>
                    <span class="font-black text-lg text-kilat-dark">+{{ $weeklyExp }}</span>
                    <span class="text-[9px] text-deep-teal/50 font-semibold">EXP</span>
                </div>
            </div>

            <!-- Level Progress Bar -->
            <div class="pt-1">
                <x-exp-bar :currentExp="$user->total_exp" :showLabel="true" size="md" />
            </div>

            <!-- Interest Tags -->
            @if ($tags->isNotEmpty())
                <div class="flex flex-col gap-2 pt-2 border-t border-paper-dark">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-deep-teal/60">Topik Minat</span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($tags as $tag)
                            <x-chip :active="true" variant="default" size="sm">
                                {{ $tag->name }}
                            </x-chip>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- 1. Dedicated Earned Badges Showcase (Lencana yang Sudah Dicapai dengan Tooltip Hover) -->
        <div class="card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                <h2 class="font-extrabold text-base text-deep-teal flex items-center gap-2">
                    <x-lucide-trophy class="w-5 h-5 text-amber" />
                    <span>Lencana yang Diraih ({{ $earnedBadges->count() }})</span>
                </h2>
                <span class="text-[11px] font-bold text-deep-teal/60">
                    Arahkan kursor atau ketuk untuk detail lencana
                </span>
            </div>

            @if ($earnedBadges->isEmpty())
                <div class="py-8 px-4 rounded-2xl border-2 border-dashed border-paper-dark text-center flex flex-col items-center justify-center gap-2 bg-paper-dark/20">
                    <div class="w-12 h-12 rounded-2xl bg-paper-dark flex items-center justify-center text-deep-teal/40 border-b-2 border-paper-dark">
                        <x-lucide-award class="w-6 h-6" />
                    </div>
                    <p class="font-extrabold text-xs text-deep-teal">Belum Ada Lencana yang Diraih</p>
                    <p class="text-[11px] text-deep-teal/70 max-w-sm">
                        Mulai membaca bacaan inspiratif, menjawab kuis, berdiskusi di kolom komentar, atau menerbitkan karya sendiri untuk mengoleksi lencana pertamamu!
                    </p>
                </div>
            @else
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 sm:gap-4">
                    @foreach ($earnedBadges as $earnedBadge)
                        <div
                            x-data="{ open: false }"
                            class="relative group flex justify-center"
                            @mouseenter="open = true"
                            @mouseleave="open = false"
                            @focusin="open = true"
                            @focusout="open = false"
                        >
                            <!-- Badge Medallion Button -->
                            <button
                                type="button"
                                @click="open = !open"
                                class="w-full flex flex-col items-center p-3 rounded-2xl bg-paper border-2 border-amber-dark/40 border-b-4 hover:border-amber hover:-translate-y-1 transition-all duration-200 cursor-pointer shadow-sm focus:outline-none focus:ring-2 focus:ring-amber select-none"
                                aria-label="{{ $earnedBadge->name }}"
                            >
                                <div class="w-12 h-12 rounded-2xl bg-amber text-deep-teal flex items-center justify-center border-b-2 border-amber-dark shadow-sm group-hover:scale-105 transition-transform">
                                    <x-badge-icon :name="$earnedBadge->icon" class="w-6 h-6" />
                                </div>
                                <span class="font-extrabold text-xs text-deep-teal mt-2 text-center truncate w-full">
                                    {{ $earnedBadge->name }}
                                </span>
                                <span class="text-[9px] font-bold text-amber-900 bg-amber/30 px-1.5 py-0.5 rounded-md mt-1">
                                    Tingkat {{ $earnedBadge->tier }}
                                </span>
                            </button>

                            <!-- Interactive Tooltip (Floating Popover) -->
                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-150 transform"
                                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-100 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 z-30 w-56 sm:w-64 p-3.5 bg-deep-teal text-paper rounded-2xl shadow-2xl border-2 border-deep-teal-dark pointer-events-none"
                            >
                                <!-- Tooltip Arrow -->
                                <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3.5 h-3.5 bg-deep-teal border-r-2 border-b-2 border-deep-teal-dark rotate-45"></div>

                                <div class="relative z-10 flex flex-col gap-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-extrabold text-sm text-amber leading-tight">{{ $earnedBadge->name }}</span>
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-paper/20 text-paper whitespace-nowrap">
                                            Tingkat {{ $earnedBadge->tier }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-semibold text-paper/70">
                                        Kategori: {{ $earnedBadge->category_title }}
                                    </span>
                                    <p class="text-xs text-paper/90 font-medium leading-relaxed mt-0.5">
                                        {{ $earnedBadge->description }}
                                    </p>
                                    @if ($earnedBadge->pivot?->earned_at)
                                        <div class="flex items-center gap-1 text-[10px] font-extrabold text-kilat mt-1 pt-1.5 border-t border-paper/10">
                                            <x-lucide-award class="w-3.5 h-3.5" />
                                            <span>Didapat {{ \Carbon\Carbon::parse($earnedBadge->pivot->earned_at)->locale('id')->diffForHumans() }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 2. Progressive Achievement Categories (9 Kategori Pencapaian Bertingkat) -->
        <div class="card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                <div>
                    <h2 class="font-extrabold text-base text-deep-teal flex items-center gap-2">
                        <x-lucide-sparkles class="w-5 h-5 text-kilat-dark" />
                        <span>Progres Pencapaian (9 Kategori)</span>
                    </h2>
                    <p class="text-xs text-deep-teal/70 font-medium">
                        Lencana berkembang otomatis ke tingkat berikutnya begitu target tercapai.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @foreach ($progressiveCategories as $cat)
                    <div class="p-4 rounded-2xl border-2 border-paper-dark bg-paper flex flex-col justify-between gap-3 shadow-sm">
                        <!-- Category Header -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-paper-dark/60 flex items-center justify-center text-deep-teal border border-paper-dark flex-shrink-0">
                                    <x-badge-icon :name="$cat['icon']" class="w-4 h-4" />
                                </div>
                                <span class="font-extrabold text-xs sm:text-sm text-deep-teal truncate">
                                    {{ $cat['title'] }}
                                </span>
                            </div>

                            @if ($cat['is_maxed'])
                                <span class="text-[10px] font-extrabold text-kilat-dark bg-kilat/20 border border-kilat/30 px-2 py-0.5 rounded-full flex items-center gap-1 flex-shrink-0">
                                    <x-lucide-check-circle-2 class="w-3 h-3" />
                                    Maksimal
                                </span>
                            @else
                                <span class="text-[10px] font-extrabold text-deep-teal/80 bg-paper-dark/80 px-2 py-0.5 rounded-full border border-paper-dark flex-shrink-0">
                                    Tingkat {{ $cat['active_tier'] }}
                                </span>
                            @endif
                        </div>

                        <!-- Active Badge Info Card -->
                        <div class="p-3 rounded-xl bg-paper-dark/30 border border-paper-dark/60 flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 border-b-2 {{ $cat['is_maxed'] ? 'bg-amber text-deep-teal border-amber-dark' : 'bg-paper text-deep-teal border-paper-dark' }}">
                                <x-badge-icon :name="$cat['active_badge']?->icon ?? $cat['icon']" class="w-5 h-5" />
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-extrabold text-xs text-deep-teal leading-tight">
                                        {{ $cat['active_badge']?->name ?? 'Tercapai Semua' }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-deep-teal/70 font-semibold leading-snug mt-0.5 line-clamp-2">
                                    @if ($cat['is_maxed'])
                                        Semua lencana di kategori ini berhasil diraih!
                                    @else
                                        {{ $cat['active_badge']?->description }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar & Count -->
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between text-[11px] font-extrabold text-deep-teal">
                                <span>
                                    @if ($cat['is_maxed'])
                                        {{ $cat['current_count'] }} {{ $cat['unit'] }} (Selesai)
                                    @else
                                        {{ $cat['current_count'] }} / {{ $cat['required_count'] }} {{ $cat['unit'] }}
                                    @endif
                                </span>
                                <span class="{{ $cat['is_maxed'] ? 'text-kilat-dark' : 'text-deep-teal/70' }}">
                                    {{ $cat['progress_percent'] }}%
                                </span>
                            </div>
                            <div class="h-2.5 w-full bg-paper-dark rounded-full overflow-hidden border border-paper-dark/50">
                                <div
                                    class="h-full rounded-full transition-all duration-500 {{ $cat['is_maxed'] ? 'bg-amber border-b border-amber-dark' : 'bg-kilat-dark' }}"
                                    style="width: {{ $cat['progress_percent'] }}%"
                                ></div>
                            </div>
                        </div>

                        <!-- Tier Indicators / Stepper -->
                        <div class="flex items-center gap-1.5 pt-1 border-t border-paper-dark/40 flex-wrap">
                            @foreach ($cat['badges'] as $b)
                                <div
                                    class="flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] font-extrabold border transition-all {{ $b->is_earned ? 'bg-amber/25 text-amber-900 border-amber-400' : ($b->id === $cat['active_badge']?->id ? 'bg-kilat/20 text-deep-teal border-kilat-dark' : 'bg-paper-dark/40 text-deep-teal/40 border-paper-dark/50') }}"
                                    title="{{ $b->name }} (Target: {{ $b->required_count }} {{ $cat['unit'] }})"
                                >
                                    <span>T{{ $b->tier }}</span>
                                    @if ($b->is_earned)
                                        <x-lucide-check-circle-2 class="w-2.5 h-2.5 text-amber-900" />
                                    @elseif ($b->id === $cat['active_badge']?->id)
                                        <span class="text-[8px]">🎯</span>
                                    @else
                                        <span class="text-[8px]">🔒</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- User's Published Readings -->
        <div class="card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col gap-4">
            <h2 class="font-extrabold text-base text-deep-teal flex items-center gap-2">
                <x-lucide-book-open class="w-5 h-5 text-deep-teal" />
                <span>Bacaan Karya {{ $user->name }} ({{ $readings->count() }})</span>
            </h2>

            @if ($readings->isEmpty())
                <p class="text-xs text-deep-teal/60 font-semibold py-4 text-center">
                    Belum ada bacaan publik yang diterbitkan.
                </p>
            @else
                <div class="flex flex-col divide-y divide-paper-dark">
                    @foreach ($readings as $r)
                        <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                            <div class="flex flex-col gap-1 min-w-0">
                                <a href="{{ route('readings.public', ['slug' => $r->slug]) }}" class="font-extrabold text-xs sm:text-sm text-deep-teal hover:underline truncate">
                                    {{ $r->title }}
                                </a>
                                <div class="flex items-center gap-2 text-[10px] text-deep-teal/60 font-bold">
                                    <span>{{ $r->slides->count() }} slide</span>
                                    <span>&bull;</span>
                                    <span>{{ $r->likes_count }} suka</span>
                                    <span>&bull;</span>
                                    <span>{{ $r->completions_count }} selesai</span>
                                </div>
                            </div>

                            <x-button as="a" href="{{ route('readings.public', ['slug' => $r->slug]) }}" variant="paper" size="sm">
                                Baca
                            </x-button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
