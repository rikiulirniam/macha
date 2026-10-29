<x-app-layout :maxWidth="'w-full max-w-3xl'">
    @php
        $tierMeta = [
            1 => [
                'name' => 'Bronze',
                'bg_gradient' => 'bg-gradient-to-b from-[#D48956] via-[#B86D3B] to-[#783D17]',
                'border' => 'border-[#5C2B0C]',
                'icon_color' => 'text-[#FFE8D6]',
                'pill_bg' => 'bg-[#5C2B0C] text-[#FCD7BD] border-[#D48956]/50',
                'stepper_bg' => 'bg-gradient-to-b from-[#D48956] to-[#783D17] text-[#FFE8D6] border-[#5C2B0C]',
                'stepper_active' => 'bg-[#D48956] text-[#FFE8D6] border-[#5C2B0C] ring-2 ring-[#D48956]/40',
                'track_color' => 'bg-[#B86D3B]',
                'text_accent' => 'text-[#8C4A1F]',
            ],
            2 => [
                'name' => 'Silver',
                'bg_gradient' => 'bg-gradient-to-b from-slate-100 via-slate-300 to-slate-500',
                'border' => 'border-slate-600',
                'icon_color' => 'text-slate-900',
                'pill_bg' => 'bg-slate-800 text-slate-100 border-slate-400/50',
                'stepper_bg' => 'bg-gradient-to-b from-slate-200 to-slate-500 text-slate-950 border-slate-600',
                'stepper_active' => 'bg-slate-300 text-slate-950 border-slate-600 ring-2 ring-slate-400/40',
                'track_color' => 'bg-slate-400',
                'text_accent' => 'text-slate-700',
            ],
            3 => [
                'name' => 'Gold',
                'bg_gradient' => 'bg-gradient-to-b from-amber-200 via-yellow-400 to-amber-600',
                'border' => 'border-amber-700',
                'icon_color' => 'text-amber-950',
                'pill_bg' => 'bg-amber-950 text-amber-200 border-amber-400/50',
                'stepper_bg' => 'bg-gradient-to-b from-amber-300 to-amber-600 text-amber-950 border-amber-700',
                'stepper_active' => 'bg-amber-400 text-amber-950 border-amber-700 ring-2 ring-amber-400/40',
                'track_color' => 'bg-amber-500',
                'text_accent' => 'text-amber-800',
            ],
            4 => [
                'name' => 'Platinum',
                'bg_gradient' => 'bg-gradient-to-b from-cyan-100 via-sky-300 to-indigo-500',
                'border' => 'border-indigo-800',
                'icon_color' => 'text-indigo-950',
                'pill_bg' => 'bg-indigo-950 text-cyan-200 border-cyan-300/50',
                'stepper_bg' => 'bg-gradient-to-b from-sky-200 to-indigo-500 text-indigo-950 border-indigo-800',
                'stepper_active' => 'bg-sky-400 text-indigo-950 border-indigo-800 ring-2 ring-sky-400/40',
                'track_color' => 'bg-sky-500',
                'text_accent' => 'text-sky-800',
            ],
        ];
    @endphp
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

        <!-- 1. Dedicated Earned Badges Showcase (Koleksi Lencana yang Sudah Diraih) -->
        <div class="card-paper p-5 sm:p-6 border-2 border-paper-dark rounded-3xl flex flex-col gap-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber/20 border border-amber/40 flex items-center justify-center text-amber-dark flex-shrink-0">
                        <x-lucide-trophy class="w-5 h-5 text-amber-dark" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="font-black text-base text-deep-teal flex items-center gap-2">
                            <span>Lencana yang Diraih</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber/20 text-amber-900 border border-amber/30 font-black">
                                {{ $earnedBadges->count() }}
                            </span>
                        </h2>
                        <span class="text-[11px] font-semibold text-deep-teal/60">
                            Arahkan kursor atau ketuk lencana untuk melihat detail pencapaian
                        </span>
                    </div>
                </div>

                @php
                    $totalBadgesCount = \App\Models\Badge::count() ?: 28;
                    $badgePercentage = min(100, (int) round(($earnedBadges->count() / $totalBadgesCount) * 100));
                @endphp
                <div class="flex items-center gap-2 self-start sm:self-auto bg-paper-dark/50 px-3 py-1.5 rounded-2xl border border-paper-dark">
                    <span class="text-[11px] font-extrabold text-deep-teal/70">{{ $earnedBadges->count() }}/{{ $totalBadgesCount }} Terbuka</span>
                    <span class="text-[11px] font-black text-kilat-dark bg-kilat/15 px-2 py-0.5 rounded-full border border-kilat/30">{{ $badgePercentage }}%</span>
                </div>
            </div>

            @if ($earnedBadges->isEmpty())
                <div class="py-10 px-4 rounded-2xl border-2 border-dashed border-paper-dark text-center flex flex-col items-center justify-center gap-2.5 bg-paper-dark/20">
                    <div class="w-14 h-14 rounded-2xl bg-paper-dark flex items-center justify-center text-deep-teal/40 border-b-2 border-paper-dark">
                        <x-lucide-award class="w-7 h-7" />
                    </div>
                    <p class="font-black text-sm text-deep-teal">Belum Ada Lencana yang Diraih</p>
                    <p class="text-xs text-deep-teal/70 max-w-sm font-medium leading-relaxed">
                        Mulai membaca bacaan inspiratif, menjawab kuis, berdiskusi di kolom komentar, atau menerbitkan karyamu sendiri untuk mengoleksi lencana pertamamu!
                    </p>
                </div>
            @else
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 sm:gap-4">
                    @foreach ($earnedBadges as $earnedBadge)
                        <div
                            x-data="{ open: false }"
                            class="relative flex justify-center"
                            @mouseenter="open = true"
                            @mouseleave="open = false"
                            @focusin="open = true"
                            @focusout="open = false"
                        >
                            <!-- Badge Medallion Button (Modern Coin/Token Card) -->
                            <button
                                type="button"
                                @click="open = !open"
                                class="w-full flex flex-col items-center p-2 sm:p-2.5 pt-3 pb-3 rounded-2xl bg-paper hover:bg-white border-2 border-paper-dark hover:border-amber hover:shadow-md hover:-translate-y-1 transition-all duration-200 cursor-pointer group focus:outline-none focus:ring-2 focus:ring-amber select-none"
                                aria-label="{{ $earnedBadge->name }}"
                            >
                                @php
                                    $meta = $tierMeta[$earnedBadge->tier] ?? $tierMeta[1];
                                @endphp
                                <!-- Medallion Circle with Level Pill -->
                                <div class="relative flex flex-col items-center">
                                    <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl {{ $meta['bg_gradient'] }} {{ $meta['border'] }} {{ $meta['icon_color'] }} flex items-center justify-center border-2 border-b-4 shadow-sm group-hover:scale-105 transition-transform">
                                        <x-badge-icon :name="$earnedBadge->icon" class="w-6 h-6 sm:w-7 sm:h-7" />
                                    </div>
                                    <span class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded-full {{ $meta['pill_bg'] }} text-[8px] sm:text-[9px] font-black uppercase tracking-wider border shadow-xs whitespace-nowrap">
                                        {{ $meta['name'] }}
                                    </span>
                                </div>

                                <!-- Fully readable badge name (Up to 2 lines without truncation) -->
                                <span class="font-black text-[11px] sm:text-xs text-deep-teal mt-3 text-center leading-tight line-clamp-2 w-full break-normal overflow-hidden">
                                    {{ $earnedBadge->name }}
                                </span>

                                <span class="text-[10px] text-deep-teal/50 font-bold text-center mt-0.5 line-clamp-1">
                                    {{ $earnedBadge->category_title }}
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
                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 z-30 w-60 sm:w-64 p-3.5 bg-deep-teal text-paper rounded-2xl shadow-2xl border-2 border-deep-teal-dark pointer-events-none"
                            >
                                <!-- Tooltip Arrow -->
                                <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3.5 h-3.5 bg-deep-teal border-r-2 border-b-2 border-deep-teal-dark rotate-45"></div>

                                <div class="relative z-10 flex flex-col gap-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-black text-sm text-amber leading-tight">{{ $earnedBadge->name }}</span>
                                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full border {{ $meta['pill_bg'] }} whitespace-nowrap">
                                            {{ $meta['name'] }}
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
        <div class="card-paper p-5 sm:p-6 border-2 border-paper-dark rounded-3xl flex flex-col gap-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-kilat/15 border border-kilat/30 flex items-center justify-center text-kilat-dark flex-shrink-0">
                        <x-lucide-sparkles class="w-5 h-5 text-kilat-dark" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="font-black text-base text-deep-teal flex items-center gap-2">
                            <span>Progres Pencapaian (9 Kategori)</span>
                        </h2>
                        <p class="text-xs text-deep-teal/60 font-semibold">
                            Lencana berkembang bertahap ke tingkat berikutnya begitu target tercapai.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2-column wide layout for breathing space -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($progressiveCategories as $cat)
                    <div class="p-4 sm:p-5 rounded-2xl border-2 border-paper-dark bg-paper flex flex-col justify-between gap-4 shadow-xs hover:border-paper-dark/80 transition-all">
                        <!-- Category Header -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-paper-dark/70 flex items-center justify-center text-deep-teal border border-paper-dark flex-shrink-0">
                                    <x-badge-icon :name="$cat['icon']" class="w-5 h-5" />
                                </div>
                                <span class="font-black text-sm sm:text-base text-deep-teal">
                                    {{ $cat['title'] }}
                                </span>
                            </div>

                            @if ($cat['is_maxed'])
                                <span class="text-[11px] font-black text-kilat-dark bg-kilat/15 border border-kilat/30 px-2.5 py-0.5 rounded-full flex items-center gap-1 flex-shrink-0">
                                    <x-lucide-check-circle-2 class="w-3.5 h-3.5" />
                                    Maksimal
                                </span>
                            @else
                                @php
                                    $activeMeta = $tierMeta[$cat['active_tier']] ?? $tierMeta[1];
                                @endphp
                                <span class="text-[10px] font-black px-2.5 py-0.5 rounded-full border flex-shrink-0 {{ $activeMeta['pill_bg'] }}">
                                    {{ $activeMeta['name'] }}
                                </span>
                            @endif
                        </div>

                        @php
                            $targetTier = $cat['active_badge']?->tier ?? ($cat['is_maxed'] ? $cat['badges']->last()->tier : 1);
                            $targetMeta = $tierMeta[$targetTier] ?? $tierMeta[1];
                        @endphp
                        <!-- Active Badge Target Block -->
                        <div class="p-3.5 rounded-2xl bg-paper-dark/35 border border-paper-dark/70 flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 border-2 border-b-4 shadow-xs {{ $targetMeta['bg_gradient'] }} {{ $targetMeta['border'] }} {{ $targetMeta['icon_color'] }}">
                                <x-badge-icon :name="$cat['active_badge']?->icon ?? $cat['icon']" class="w-6 h-6" />
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-black text-sm text-deep-teal leading-tight">
                                        {{ $cat['active_badge']?->name ?? 'Tercapai Semua' }}
                                    </span>
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full border {{ $targetMeta['pill_bg'] }}">
                                        {{ $targetMeta['name'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-deep-teal/70 font-semibold leading-relaxed mt-0.5 line-clamp-2">
                                    @if ($cat['is_maxed'])
                                        Semua tingkatan lencana di kategori ini berhasil diraih!
                                    @else
                                        {{ $cat['active_badge']?->description }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar & Count Row -->
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-deep-teal/80">
                                    @if ($cat['is_maxed'])
                                        <strong class="text-deep-teal font-black">{{ $cat['current_count'] }}</strong> {{ $cat['unit'] }} (Target tercapai)
                                    @else
                                        <strong class="text-deep-teal font-black">{{ $cat['current_count'] }}</strong> dari {{ $cat['required_count'] }} {{ $cat['unit'] }}
                                    @endif
                                </span>
                                <span class="font-black {{ $cat['is_maxed'] ? 'text-amber-900' : 'text-kilat-dark' }}">
                                    {{ $cat['progress_percent'] }}%
                                </span>
                            </div>
                            <div class="h-2.5 w-full bg-paper-dark rounded-full overflow-hidden border border-paper-dark/60 p-0.5">
                                <div
                                    class="h-full rounded-full transition-all duration-500 {{ $cat['is_maxed'] ? 'bg-amber border-b border-amber-dark' : 'bg-kilat-dark' }}"
                                    style="width: {{ $cat['progress_percent'] }}%"
                                ></div>
                            </div>
                        </div>

                        <!-- Horizontal Milestone Stepper Track -->
                        <div class="pt-2 border-t border-paper-dark/50">
                            <div class="flex items-center justify-between gap-1 relative">
                                @foreach ($cat['badges'] as $b)
                                    @php
                                        $bMeta = $tierMeta[$b->tier] ?? $tierMeta[1];
                                    @endphp
                                    <div
                                        class="flex flex-col items-center gap-1 group/tier relative z-10 flex-1"
                                        title="{{ $b->name }} ({{ $bMeta['name'] }} — Target: {{ $b->required_count }} {{ $cat['unit'] }})"
                                    >
                                        @if ($b->is_earned)
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center font-black text-[10px] border-2 border-b-2 shadow-xs {{ $bMeta['stepper_bg'] }}">
                                                <x-lucide-check class="w-3.5 h-3.5 stroke-[3]" />
                                            </div>
                                        @elseif ($b->id === $cat['active_badge']?->id)
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center font-black text-[10px] border-2 {{ $bMeta['stepper_active'] }} animate-pulse">
                                                <x-badge-icon :name="$b->icon" class="w-3 h-3" />
                                            </div>
                                        @else
                                            <div class="w-6 h-6 rounded-full bg-paper-dark text-deep-teal/40 flex items-center justify-center font-bold text-[10px] border border-paper-dark/80">
                                                <x-lucide-lock class="w-2.5 h-2.5" />
                                            </div>
                                        @endif
                                        <span class="text-[9px] sm:text-[10px] font-black {{ $b->is_earned ? 'text-deep-teal' : ($b->id === $cat['active_badge']?->id ? $bMeta['text_accent'] : 'text-deep-teal/40') }}">
                                            {{ $bMeta['name'] }}
                                        </span>
                                    </div>
                                    @if (! $loop->last)
                                        <div class="flex-1 h-0.5 -mt-3.5 {{ $b->is_earned && isset($cat['badges'][$loop->index + 1]) && $cat['badges'][$loop->index + 1]->is_earned ? 'bg-amber' : 'bg-paper-dark' }}"></div>
                                    @endif
                                @endforeach
                            </div>
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

        @if ($isOwner)
            <!-- PWA Application Card (Hanya Tampil di Mobile) -->
            <div
                x-data="{
                    isStandalone: window.pwaManager ? window.pwaManager.isStandalone : false,
                    triggerInstall() {
                        window.dispatchEvent(new CustomEvent('open-pwa-install'));
                    }
                }"
                class="md:hidden card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-deep-teal p-1.5 flex items-center justify-center flex-shrink-0 border-2 border-deep-teal">
                        <img src="{{ asset('icons/icon-192x192.png') }}" alt="Macha Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-extrabold text-sm text-deep-teal">Aplikasi Macha (PWA)</span>
                        <template x-if="isStandalone">
                            <span class="text-xs text-kilat-dark font-extrabold flex items-center gap-1 mt-0.5">
                                <x-lucide-check-circle-2 class="w-3.5 h-3.5" />
                                Terpasang di perangkat ini (Mode PWA)
                            </span>
                        </template>
                        <template x-if="!isStandalone">
                            <span class="text-xs text-deep-teal/70 font-semibold mt-0.5">
                                Pasang di layar utama HP agar cepat dibuka tanpa ketik link di browser.
                            </span>
                        </template>
                    </div>
                </div>

                <template x-if="!isStandalone">
                    <x-button
                        type="button"
                        variant="kilat"
                        size="sm"
                        @click="triggerInstall()"
                        class="sm:flex-shrink-0 justify-center"
                    >
                        <x-lucide-smartphone class="w-4 h-4 mr-1.5" />
                        <span>Pasang di Layar Utama</span>
                    </x-button>
                </template>
            </div>
        @endif
    </div>
</x-app-layout>
