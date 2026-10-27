@props([
    'active' => false,
    'variant' => 'default',
    'size' => 'md',
    'interactive' => false,
])

@php
$baseClasses = 'inline-flex items-center font-bold rounded-xl transition-all duration-150 select-none';

$interactiveClasses = $interactive ? 'cursor-pointer active:scale-95' : '';

$sizeClasses = match ($size) {
    'sm' => 'px-2.5 py-1 text-xs gap-1',
    'lg' => 'px-5 py-3 text-base gap-2.5 rounded-2xl',
    default => 'px-3.5 py-1.5 text-sm gap-1.5',
};

if ($active) {
    $variantClasses = match ($variant) {
        'amber' => 'bg-amber text-deep-teal border-2 border-amber-dark',
        'teal' => 'bg-deep-teal text-paper border-2 border-teal-soft',
        default => 'bg-kilat text-deep-teal border-2 border-kilat-dark shadow-sm',
    };
} else {
    $variantClasses = match ($variant) {
        'amber' => 'bg-amber/20 text-deep-teal border border-amber/40 hover:bg-amber/30',
        'teal' => 'bg-teal-soft/20 text-paper border border-teal-soft/40',
        default => 'bg-paper text-deep-teal border-2 border-paper-dark hover:border-kilat/60',
    };
}

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses} {$interactiveClasses}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
