<x-app-layout :maxWidth="'w-full max-w-xl'">
    <div class="p-4 sm:p-6 w-full flex flex-col gap-6 select-none">
        <!-- Header & Countdown -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b-2 border-paper-dark pb-4">
            <div>
                <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight flex items-center gap-2">
                    <x-lucide-trophy class="w-7 h-7 text-amber fill-amber" />
                    <span>Peringkat Mingguan</span>
                </h1>
                <p class="text-xs text-deep-teal/70 font-semibold mt-0.5">Bersaing sehat mengumpulkan EXP dari bacaan bermanfaat.</p>
            </div>

            <!-- Countdown Pill -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-amber/20 border-2 border-amber/50 text-xs font-extrabold text-deep-teal self-start sm:self-auto">
                <x-lucide-clock class="w-4 h-4 text-amber-dark" />
                <span>Reset dalam {{ $countdown }}</span>
            </div>
        </div>

        @if ($topUsers->isEmpty())
            <div class="card-paper p-8 text-center flex flex-col items-center justify-center gap-3">
                <x-lucide-sparkles class="w-12 h-12 text-amber" />
                <h3 class="font-extrabold text-base text-deep-teal">Minggu Baru Dimulai!</h3>
                <p class="text-xs text-deep-teal/70 font-semibold max-w-xs">
                    Belum ada EXP terkumpul minggu ini. Jadilah yang pertama membaca untuk memimpin klasemen!
                </p>
                <x-button as="a" href="{{ route('home') }}" variant="kilat" size="md">
                    Mulai Membaca Sekarang
                </x-button>
            </div>
        @else
            <!-- Visual Podium Top 3 (Blok berbeda tinggi, warna solid amber/kilat/paper, tanpa gradient) -->
            @if ($podium->count() >= 3)
                @php
                    $first = $podium[0];
                    $second = $podium[1];
                    $third = $podium[2];
                @endphp
                <div class="card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col items-center pt-8 pb-4">
                    <div class="w-full flex items-end justify-center gap-2 sm:gap-4 max-w-md">
                        <!-- Rank 2 (Left, Medium height, Kilat) -->
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <a href="{{ route('profile.user', ['username' => $second->username]) }}" class="flex flex-col items-center group">
                                <span class="w-12 h-12 rounded-2xl bg-kilat text-deep-teal font-extrabold text-base flex items-center justify-center border-2 border-kilat-dark group-hover:scale-105 transition-transform">
                                    {{ $second->initials }}
                                </span>
                                <span class="font-extrabold text-xs text-deep-teal mt-1.5 text-center truncate max-w-[85px] leading-tight">
                                    {{ $second->name }}
                                </span>
                                <span class="text-[11px] font-extrabold text-kilat-dark">
                                    {{ $second->weekly_exp }} EXP
                                </span>
                            </a>
                            <!-- Podium Pillar -->
                            <div class="w-full h-28 rounded-t-2xl bg-kilat text-deep-teal border-2 border-kilat-dark border-b-0 flex flex-col items-center justify-start pt-3">
                                <span class="font-extrabold text-2xl">2</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Perak</span>
                            </div>
                        </div>

                        <!-- Rank 1 (Center, Tallest, Amber) -->
                        <div class="flex-1 flex flex-col items-center gap-2 -translate-y-2">
                            <div class="relative">
                                <x-lucide-crown class="w-6 h-6 text-amber fill-amber absolute -top-5 left-1/2 -translate-x-1/2" />
                                <a href="{{ route('profile.user', ['username' => $first->username]) }}" class="flex flex-col items-center group">
                                    <span class="w-14 h-14 rounded-2xl bg-amber text-deep-teal font-extrabold text-lg flex items-center justify-center border-2 border-amber-dark group-hover:scale-105 transition-transform">
                                        {{ $first->initials }}
                                    </span>
                                    <span class="font-extrabold text-xs text-deep-teal mt-1.5 text-center truncate max-w-[95px] leading-tight">
                                        {{ $first->name }}
                                    </span>
                                    <span class="text-[11px] font-extrabold text-amber-dark">
                                        {{ $first->weekly_exp }} EXP
                                    </span>
                                </a>
                            </div>
                            <!-- Podium Pillar -->
                            <div class="w-full h-36 rounded-t-2xl bg-amber text-deep-teal border-2 border-amber-dark border-b-0 flex flex-col items-center justify-start pt-3">
                                <span class="font-extrabold text-3xl">1</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Emas</span>
                            </div>
                        </div>

                        <!-- Rank 3 (Right, Shortest, Paper) -->
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <a href="{{ route('profile.user', ['username' => $third->username]) }}" class="flex flex-col items-center group">
                                <span class="w-12 h-12 rounded-2xl bg-paper-dark text-deep-teal font-extrabold text-base flex items-center justify-center border-2 border-paper-dark group-hover:scale-105 transition-transform">
                                    {{ $third->initials }}
                                </span>
                                <span class="font-extrabold text-xs text-deep-teal mt-1.5 text-center truncate max-w-[85px] leading-tight">
                                    {{ $third->name }}
                                </span>
                                <span class="text-[11px] font-extrabold text-deep-teal/70">
                                    {{ $third->weekly_exp }} EXP
                                </span>
                            </a>
                            <!-- Podium Pillar -->
                            <div class="w-full h-20 rounded-t-2xl bg-paper text-deep-teal border-2 border-paper-dark border-b-0 flex flex-col items-center justify-start pt-2">
                                <span class="font-extrabold text-xl">3</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Perunggu</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Ranks 4 to 20 List -->
            <div class="flex flex-col gap-2.5 pb-24">
                <span class="text-xs font-extrabold uppercase tracking-wider text-deep-teal/70 px-1">Klasemen Top 20</span>
                @foreach ($topUsers as $u)
                    <div
                        class="p-3.5 rounded-2xl border-2 flex items-center justify-between transition-all {{ auth()->check() && auth()->id() === $u->id ? 'bg-amber/20 border-amber ring-2 ring-amber/30' : 'bg-paper border-paper-dark hover:border-paper-dark' }}"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Rank Number -->
                            <span class="w-6 font-extrabold text-sm text-center {{ $u->rank <= 3 ? 'text-amber-dark font-black' : 'text-deep-teal/60' }}">
                                #{{ $u->rank }}
                            </span>

                            <!-- Avatar -->
                            <span class="w-10 h-10 rounded-xl bg-deep-teal text-paper font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                                {{ $u->initials }}
                            </span>

                            <div class="flex flex-col min-w-0">
                                <a href="{{ route('profile.user', ['username' => $u->username]) }}" class="font-extrabold text-sm text-deep-teal hover:underline truncate">
                                    {{ $u->name }}
                                </a>
                                <div class="flex items-center gap-2 text-[11px] text-deep-teal/60 font-semibold">
                                    <span>{{ '@' . $u->username }}</span>
                                    @if ($u->current_streak > 0)
                                        <span class="flex items-center gap-0.5 text-amber-dark font-extrabold">
                                            <x-lucide-flame class="w-3 h-3 fill-amber text-amber" />
                                            <span>{{ $u->current_streak }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Weekly EXP Pill -->
                        <div class="flex flex-col items-end flex-shrink-0 ml-3">
                            <span class="px-2.5 py-1 rounded-xl bg-kilat text-deep-teal font-extrabold text-xs border-b-2 border-kilat-dark">
                                +{{ $u->weekly_exp }} EXP
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Sticky Bottom Bar for Current User's Own Rank (Always visible even if outside top 20) -->
    @auth
        <aside class="fixed bottom-14 md:bottom-3 left-1/2 -translate-x-1/2 z-40 w-full max-w-xl px-4 select-none">
            <div class="p-3.5 bg-deep-teal text-paper rounded-2xl border-2 border-teal-soft shadow-none flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-8 h-8 rounded-xl bg-amber text-deep-teal font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                        {{ auth()->user()->initials }}
                    </span>
                    <div class="flex flex-col min-w-0">
                        <span class="font-extrabold text-xs text-paper truncate">
                            Posisi Kamu:
                            @if ($currentUserRank)
                                <strong class="text-amber">Peringkat #{{ $currentUserRank }}</strong>
                            @else
                                <span class="text-paper/70 font-semibold">Belum Masuk Peringkat</span>
                            @endif
                        </span>
                        <span class="text-[10px] text-paper/70 font-semibold">
                            Total EXP Minggu Ini: <strong class="text-amber">{{ $currentUserWeeklyExp }} EXP</strong>
                        </span>
                    </div>
                </div>

                <x-button as="a" href="{{ route('home') }}" variant="kilat" size="sm" class="flex-shrink-0 ml-3">
                    Baca Lagi
                </x-button>
            </div>
        </aside>
    @endauth
</x-app-layout>
