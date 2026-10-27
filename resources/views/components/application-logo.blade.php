@props([
    'showText' => true,
    'size' => 'md',
    'imageClass' => '',
    'textClass' => '',
])

@php
    $imageSizes = [
        'xs' => 'h-7 w-7',
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-12 w-12',
        'xl' => 'h-16 w-16',
    ];
    $textSizes = [
        'xs' => 'text-lg',
        'sm' => 'text-xl',
        'md' => 'text-2xl',
        'lg' => 'text-3xl',
        'xl' => 'text-4xl',
    ];
    $gaps = [
        'xs' => 'gap-2',
        'sm' => 'gap-2',
        'md' => 'gap-2.5',
        'lg' => 'gap-3',
        'xl' => 'gap-3.5',
    ];

    $imgSizeClass = $imageSizes[$size] ?? $imageSizes['md'];
    $textSizeClass = $textSizes[$size] ?? $textSizes['md'];
    $gapClass = $gaps[$size] ?? $gaps['md'];
@endphp

<div {{ $attributes->merge(['class' => "inline-flex items-center {$gapClass} select-none group"]) }}>
    <img
        src="{{ asset('brand/logo.png') }}"
        alt="{{ config('matcha.app_name', 'Macha') }}"
        class="{{ $imgSizeClass }} {{ $imageClass }} object-contain flex-shrink-0 transition-transform duration-200 group-hover:scale-105"
    />
    @if ($showText)
        <span class="font-sans font-extrabold {{ $textSizeClass }} tracking-tight text-deep-teal leading-none {{ $textClass }}">
            {{ config('matcha.app_name', 'Macha') }}
        </span>
    @endif
</div>

