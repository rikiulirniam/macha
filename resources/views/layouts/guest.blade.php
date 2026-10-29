<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-paper">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('matcha.app_name', 'Macha') }} — Micro-reading Sosial</title>
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

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-deep-teal bg-paper min-h-full flex flex-col justify-center items-center p-4 sm:p-6 antialiased">
        <x-toast />
        <x-pwa-install-prompt />

        <div class="w-full max-w-md flex flex-col items-center">
            <div class="mb-6">
                <a href="{{ route('home') }}" class="focus:outline-none focus:ring-2 focus:ring-kilat rounded-2xl p-1 inline-block">
                    <x-application-logo size="lg" />
                </a>
            </div>

            <div class="w-full bg-paper border-2 border-paper-dark rounded-3xl p-6 sm:p-8 shadow-none">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-deep-teal/60 text-center font-medium">
                &copy; {{ date('Y') }} {{ config('matcha.app_name', 'Macha') }}. Platform micro-reading sosial santai & bergizi.
            </p>
        </div>
    </body>
</html>
