<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-paper">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $reading->title }} — {{ config('matcha.app_name', 'Macha') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('brand/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('brand/logo.png') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $reading->title }}">
    <meta property="og:description" content="{{ $reading->description ?? Str::limit(strip_tags($reading->slides->first()?->body ?? ''), 120) }}">
    <meta property="og:site_name" content="{{ config('matcha.app_name', 'Macha') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $reading->title }}">
    <meta name="twitter:description" content="{{ $reading->description ?? Str::limit(strip_tags($reading->slides->first()?->body ?? ''), 120) }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-deep-teal bg-paper min-h-full h-[100dvh] flex flex-col justify-center items-center antialiased overflow-hidden">
    <x-toast />

    <!-- Reading Card Container: Full 100dvh viewport with floating header -->
    <main class="w-full max-w-[480px] h-[100dvh] max-h-[100dvh] relative overflow-hidden flex flex-col mx-auto shadow-2xl">
        <!-- Floating Top Header: Positioned over card without cutting bottom height -->
        <header class="absolute top-0 inset-x-0 z-30 flex items-center justify-between p-3 sm:p-4 pointer-events-none select-none">
            <a
                href="{{ route('home') }}"
                class="pointer-events-auto flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-deep-teal/80 hover:bg-deep-teal text-paper backdrop-blur-md border border-white/20 transition-all text-xs font-bold shadow-md hover:scale-105 active:scale-95"
            >
                <img src="{{ asset('brand/logo.png') }}" alt="{{ config('matcha.app_name', 'Macha') }}" class="w-4 h-4 object-contain" />
                <span>{{ config('matcha.app_name', 'Macha') }}</span>
            </a>

            @guest
                <a
                    href="{{ route('register') }}"
                    class="pointer-events-auto flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-kilat text-deep-teal font-extrabold text-xs border border-kilat-dark hover:brightness-105 transition-all shadow-md hover:scale-105 active:scale-95"
                >
                    <x-lucide-zap class="w-3.5 h-3.5 fill-current" />
                    <span>Daftar & EXP (+10)</span>
                </a>
            @else
                <a
                    href="{{ route('profile.user', ['username' => $reading->user->username]) }}"
                    class="pointer-events-auto flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-deep-teal/80 hover:bg-deep-teal text-paper backdrop-blur-md border border-white/20 transition-all text-xs font-bold shadow-md hover:scale-105 active:scale-95"
                    title="Lihat profil kreator"
                >
                    <span class="w-5 h-5 rounded-md bg-amber text-deep-teal font-extrabold text-[10px] flex items-center justify-center border border-amber-dark">
                        {{ $reading->user->initials }}
                    </span>
                    <span class="truncate max-w-[120px]">{{ $reading->user->name }}</span>
                </a>
            @endguest
        </header>

        <!-- Full Height Reading Card -->
        <x-reading-card :reading="$reading" :isFirst="true" :isFeed="false" />
    </main>
</body>
</html>
