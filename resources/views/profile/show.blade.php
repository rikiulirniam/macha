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

        <!-- Badges Section (Tabel Badges: earned in amber/kilat, unearned in grey with requirements) -->
        <div class="card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <h2 class="font-extrabold text-base text-deep-teal flex items-center gap-2">
                    <x-lucide-award class="w-5 h-5 text-amber" />
                    <span>Koleksi Lencana ({{ $badges->where('is_earned', true)->count() }}/{{ $badges->count() }})</span>
                </h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach ($badges as $badge)
                    <div
                        class="p-3 rounded-2xl border-2 flex flex-col items-center text-center gap-2 transition-all {{ $badge->is_earned ? 'bg-paper border-paper-dark' : 'bg-paper-dark/30 border-paper-dark/60 opacity-60' }}"
                        title="{{ $badge->description }}"
                    >
                        <!-- Badge Icon Circle -->
                        <div
                            class="w-12 h-12 rounded-2xl flex items-center justify-center border-b-2 {{ $badge->is_earned ? 'bg-amber text-deep-teal border-amber-dark' : 'bg-paper-dark text-deep-teal/40 border-paper-dark' }}"
                        >
                            @if ($badge->icon === 'flame')
                                <x-lucide-flame class="w-6 h-6 fill-current" />
                            @elseif ($badge->icon === 'award')
                                <x-lucide-award class="w-6 h-6" />
                            @elseif ($badge->icon === 'library')
                                <x-lucide-library class="w-6 h-6" />
                            @elseif ($badge->icon === 'trophy')
                                <x-lucide-trophy class="w-6 h-6" />
                            @elseif ($badge->icon === 'sparkles')
                                <x-lucide-sparkles class="w-6 h-6" />
                            @elseif ($badge->icon === 'message-circle')
                                <x-lucide-message-circle class="w-6 h-6" />
                            @elseif ($badge->icon === 'pen-tool')
                                <x-lucide-pen-tool class="w-6 h-6" />
                            @elseif ($badge->icon === 'check-circle-2')
                                <x-lucide-check-circle-2 class="w-6 h-6" />
                            @else
                                <x-lucide-book-open class="w-6 h-6" />
                            @endif
                        </div>

                        <div class="flex flex-col">
                            <span class="font-extrabold text-xs text-deep-teal leading-tight">{{ $badge->name }}</span>
                            <span class="text-[10px] text-deep-teal/70 font-semibold leading-tight mt-1 line-clamp-2">
                                {{ $badge->description }}
                            </span>
                            @if ($badge->is_earned)
                                <span class="text-[9px] font-extrabold text-kilat-dark mt-1">
                                    Didapat {{ \Carbon\Carbon::parse($badge->earned_at)->locale('id')->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-[9px] font-bold text-deep-teal/50 mt-1">Belum Terbuka</span>
                            @endif
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
