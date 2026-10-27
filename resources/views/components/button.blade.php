@props([
    'variant' => 'kilat',
    'size' => 'md',
    'type' => 'button',
    'as' => 'button',
])

@php
$baseClasses = 'inline-flex items-center justify-center font-bold rounded-2xl select-none transition-all duration-100 ease-out focus:outline-none focus:ring-2 focus:ring-kilat focus:ring-offset-2 active:translate-y-[2px] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:active:translate-y-0';

$variantClasses = match ($variant) {
    'kilat' => 'bg-kilat text-deep-teal border-b-4 border-kilat-dark hover:brightness-105 active:border-b-2',
    'amber' => 'bg-amber text-deep-teal border-b-4 border-amber-dark hover:brightness-105 active:border-b-2',
    'paper' => 'bg-paper text-deep-teal border-2 border-paper-dark border-b-4 hover:bg-white active:border-b-2',
    'teal' => 'bg-teal-soft text-paper border-b-4 border-deep-teal hover:brightness-110 active:border-b-2',
    'error' => 'bg-error text-paper border-b-4 border-[#B83E3A] hover:brightness-105 active:border-b-2',
    default => 'bg-kilat text-deep-teal border-b-4 border-kilat-dark active:border-b-2',
};

$sizeClasses = match ($size) {
    'sm' => 'px-3 py-1.5 text-xs gap-1.5',
    'lg' => 'px-6 py-3.5 text-lg gap-3',
    default => 'px-4 py-2.5 text-sm gap-2',
};

$classes = "{$baseClasses} {$variantClasses} {$sizeClasses}";
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
