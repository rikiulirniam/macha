@props([
    'title' => null,
    'maxWidth' => null,
    'isFeed' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-paper {{ $isFeed ? 'overflow-hidden' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('matcha.app_name', 'Macha') . ' — Micro-reading Sosial' }}</title>
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/icon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/icon-16x16.png') }}">
        <link rel="icon" type="image/png" href="{{ asset('brand/logo.png') }}">

        <!-- PWA Web App Manifest & Mobile Meta -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#102A2A">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="application-name" content="{{ config('matcha.app_name', 'Macha') }}">

        <!-- iOS Safari PWA Meta & Icons -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ config('matcha.app_name', 'Macha') }}">
        <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/icon-180x180.png') }}">
        <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">
        <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('icons/icon-512x512.png') }}">

        <!-- Windows Tile -->
        <meta name="msapplication-TileColor" content="#102A2A">
        <meta name="msapplication-TileImage" content="{{ asset('icons/icon-192x192.png') }}">

        @stack('meta')

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans text-deep-teal bg-paper min-h-full flex flex-col md:flex-row antialiased selection:bg-kilat selection:text-deep-teal {{ $isFeed ? 'h-[100dvh] max-h-[100dvh] overflow-hidden' : '' }}">
        <x-toast />
        <x-pwa-install-prompt />

        <!-- Desktop Sidebar (md and up) -->
        <aside class="hidden md:flex md:w-64 lg:w-72 md:flex-col md:fixed md:inset-y-0 z-30 bg-paper border-r-2 border-paper-dark p-6 justify-between select-none">
            <div class="flex flex-col gap-8">
                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="focus:outline-none focus:ring-2 focus:ring-kilat rounded-2xl p-1 inline-block">
                    <x-application-logo />
                </a>

                <!-- Navigation Links -->
                <nav class="flex flex-col gap-2" aria-label="Navigasi Utama">
                    <a
                        href="{{ route('home') }}"
                        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl font-extrabold text-base transition-all {{ request()->routeIs('home') ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark' : 'text-deep-teal/80 hover:bg-paper-dark/60' }}"
                    >
                        <x-lucide-home class="w-6 h-6 flex-shrink-0" />
                        <span>Beranda</span>
                    </a>

                    <a
                        href="{{ route('leaderboard') }}"
                        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl font-extrabold text-base transition-all {{ request()->routeIs('leaderboard') ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark' : 'text-deep-teal/80 hover:bg-paper-dark/60' }}"
                    >
                        <x-lucide-trophy class="w-6 h-6 flex-shrink-0" />
                        <span>Peringkat</span>
                    </a>

                    <a
                        href="{{ route('readings.create') }}"
                        class="flex items-center gap-3.5 px-4 py-3 rounded-2xl font-extrabold text-base transition-all {{ request()->routeIs('readings.create') ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark' : 'text-deep-teal/80 hover:bg-paper-dark/60' }}"
                    >
                        <x-lucide-plus-circle class="w-6 h-6 flex-shrink-0" />
                        <span>Buat</span>
                    </a>

                    @auth
                        <a
                            href="{{ route('readings.my') }}"
                            class="flex items-center gap-3.5 px-4 py-3 rounded-2xl font-extrabold text-base transition-all {{ request()->routeIs('readings.my') ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark' : 'text-deep-teal/80 hover:bg-paper-dark/60' }}"
                        >
                            <x-lucide-book-open class="w-6 h-6 flex-shrink-0" />
                            <span>Bacaan Saya</span>
                        </a>

                        <a
                            href="{{ route('profile.show') }}"
                            class="flex items-center gap-3.5 px-4 py-3 rounded-2xl font-extrabold text-base transition-all {{ request()->routeIs('profile.show') ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark' : 'text-deep-teal/80 hover:bg-paper-dark/60' }}"
                        >
                            <x-lucide-user class="w-6 h-6 flex-shrink-0" />
                            <span>Profil</span>
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="flex items-center gap-3.5 px-4 py-3 rounded-2xl font-extrabold text-base transition-all {{ request()->routeIs('login') ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark' : 'text-deep-teal/80 hover:bg-paper-dark/60' }}"
                        >
                            <x-lucide-log-in class="w-6 h-6 flex-shrink-0" />
                            <span>Masuk / Daftar</span>
                        </a>
                    @endauth
                </nav>
            </div>

            <!-- User Status / Quick EXP & Streak on Desktop -->
            @auth
                <div
                    x-data="{
                        currentExp: {{ (int) auth()->user()->total_exp }},
                        streak: {{ (int) auth()->user()->current_streak }},
                        animating: false,
                        get progress() {
                            return Math.max(4, Math.min(100, this.currentExp % 100));
                        },
                        handleExpGained(detail) {
                            if (!detail) return;
                            if (detail.total_exp !== undefined && detail.total_exp !== null) {
                                this.currentExp = detail.total_exp;
                            } else if (detail.exp_awarded) {
                                this.currentExp += detail.exp_awarded;
                            }
                            if (detail.streak !== undefined && detail.streak !== null) {
                                this.streak = detail.streak;
                            } else if (detail.current_streak !== undefined && detail.current_streak !== null) {
                                this.streak = detail.current_streak;
                            }
                            this.animating = true;
                            setTimeout(() => { this.animating = false; }, 1200);
                        }
                    }"
                    @exp-gained.window="handleExpGained($event.detail)"
                    class="card-paper p-4 border-2 border-paper-dark rounded-2xl flex flex-col gap-3 transition-all duration-300"
                    :class="{ 'scale-[1.02] ring-2 ring-amber shadow-md': animating }"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-9 h-9 rounded-xl bg-deep-teal text-paper font-extrabold text-sm flex items-center justify-center border border-teal-soft">
                                {{ auth()->user()->initials }}
                            </span>
                            <div class="flex flex-col min-w-0">
                                <span class="font-bold text-sm text-deep-teal truncate leading-tight">{{ auth()->user()->name }}</span>
                                <span class="text-xs text-deep-teal/60 truncate leading-tight">{{ '@' . auth()->user()->username }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-paper-dark text-xs font-bold">
                        <!-- Streak -->
                        <div class="flex items-center gap-1.5 text-deep-teal" title="Streak harian">
                            <x-lucide-flame class="w-4 h-4 text-amber fill-amber" />
                            <span x-text="streak + ' hari'">{{ auth()->user()->current_streak }} hari</span>
                        </div>
                        <!-- EXP -->
                        <div class="flex items-center gap-1.5 text-deep-teal" title="Total EXP">
                            <span :class="{ 'animate-bounce': animating }" class="inline-flex">
                                <x-lucide-zap class="w-4 h-4 text-amber fill-amber" />
                            </span>
                            <span x-text="currentExp + ' EXP'" :class="{ 'text-amber-dark font-extrabold': animating }">{{ auth()->user()->total_exp }} EXP</span>
                        </div>
                    </div>

                    <div class="w-full">
                        <div class="w-full bg-paper-dark rounded-full h-2 overflow-hidden border border-paper-dark">
                            <div
                                class="bg-amber h-full rounded-full transition-all duration-700 ease-out border-b-2 border-amber-dark"
                                :style="'width: ' + progress + '%'"
                                role="progressbar"
                                :aria-valuenow="progress"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                    </div>

                    <!-- Logout button -->
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="w-full text-xs font-bold text-deep-teal/60 hover:text-error py-1 text-center transition-colors">
                            Keluar dari akun
                        </button>
                    </form>
                </div>
            @else
                <div class="card-paper p-4 border-2 border-paper-dark rounded-2xl flex flex-col gap-3">
                    <p class="text-xs font-semibold text-deep-teal/70 leading-relaxed">
                        Masuk untuk menyimpan kemajuan baca, mengumpulkan EXP, dan menjaga streak!
                    </p>
                    <x-button as="a" href="{{ route('register') }}" variant="kilat" size="sm" class="w-full">
                        Daftar Gratis
                    </x-button>
                </div>
            @endauth
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 md:pl-64 lg:pl-72 flex justify-center {{ $isFeed ? 'h-[100dvh] max-h-[100dvh] overflow-hidden pb-0' : 'pb-20 md:pb-0 min-h-screen' }} w-full">
            <div class="{{ $maxWidth ?? 'w-full max-w-[480px]' }} {{ $isFeed ? 'h-[100dvh] max-h-[100dvh] overflow-hidden' : 'min-h-screen' }} flex flex-col">
                {{ $slot }}
            </div>
        </main>

        <!-- Mobile Bottom Navigation (4 tabs, min 44px touch target) -->
        <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-paper/95 backdrop-blur-none border-t-2 border-paper-dark px-3 py-1 flex items-center justify-around select-none" aria-label="Navigasi Bawah">
            <a
                href="{{ route('home') }}"
                class="flex flex-col items-center justify-center min-w-[56px] min-h-[48px] py-1 px-2 rounded-xl transition-colors {{ request()->routeIs('home') ? 'text-kilat font-extrabold' : 'text-deep-teal/70 font-semibold' }}"
                aria-label="Beranda"
            >
                <x-lucide-home class="w-6 h-6 {{ request()->routeIs('home') ? 'stroke-[2.5]' : 'stroke-2' }}" />
                <span class="text-[11px] leading-tight mt-0.5">Beranda</span>
            </a>

            <a
                href="{{ route('leaderboard') }}"
                class="flex flex-col items-center justify-center min-w-[56px] min-h-[48px] py-1 px-2 rounded-xl transition-colors {{ request()->routeIs('leaderboard') ? 'text-kilat font-extrabold' : 'text-deep-teal/70 font-semibold' }}"
                aria-label="Peringkat"
            >
                <x-lucide-trophy class="w-6 h-6 {{ request()->routeIs('leaderboard') ? 'stroke-[2.5]' : 'stroke-2' }}" />
                <span class="text-[11px] leading-tight mt-0.5">Peringkat</span>
            </a>

            <a
                href="{{ route('readings.create') }}"
                class="flex flex-col items-center justify-center min-w-[56px] min-h-[48px] py-1 px-2 rounded-xl transition-colors {{ request()->routeIs('readings.create') ? 'text-kilat font-extrabold' : 'text-deep-teal/70 font-semibold' }}"
                aria-label="Buat Bacaan"
            >
                <x-lucide-plus-circle class="w-6 h-6 {{ request()->routeIs('readings.create') ? 'stroke-[2.5]' : 'stroke-2' }}" />
                <span class="text-[11px] leading-tight mt-0.5">Buat</span>
            </a>

            @auth
                <a
                    href="{{ route('profile.show') }}"
                    class="flex flex-col items-center justify-center min-w-[56px] min-h-[48px] py-1 px-2 rounded-xl transition-colors {{ request()->routeIs('profile.show') ? 'text-kilat font-extrabold' : 'text-deep-teal/70 font-semibold' }}"
                    aria-label="Profil Saya"
                >
                    <x-lucide-user class="w-6 h-6 {{ request()->routeIs('profile.show') ? 'stroke-[2.5]' : 'stroke-2' }}" />
                    <span class="text-[11px] leading-tight mt-0.5">Profil</span>
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="flex flex-col items-center justify-center min-w-[56px] min-h-[48px] py-1 px-2 rounded-xl transition-colors {{ request()->routeIs('login') ? 'text-kilat font-extrabold' : 'text-deep-teal/70 font-semibold' }}"
                    aria-label="Masuk ke Akun"
                >
                    <x-lucide-log-in class="w-6 h-6 {{ request()->routeIs('login') ? 'stroke-[2.5]' : 'stroke-2' }}" />
                    <span class="text-[11px] leading-tight mt-0.5">Masuk</span>
                </a>
            @endauth
        </nav>

        @stack('scripts')
    </body>
</html>
