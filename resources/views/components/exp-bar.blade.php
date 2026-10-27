@props([
    'currentExp' => 0,
    'showLabel' => true,
    'size' => 'md',
])

@php
$level = (int) floor($currentExp / 100) + 1;
$progress = $currentExp % 100;
$nextLevelExp = 100;

$heightClass = match ($size) {
    'sm' => 'h-2',
    'lg' => 'h-4',
    default => 'h-3',
};
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($showLabel)
        <div class="flex items-center justify-between text-xs font-bold mb-1.5 text-deep-teal">
            <span class="flex items-center gap-1">
                <span class="bg-amber px-1.5 py-0.5 rounded-lg text-deep-teal text-[11px] font-extrabold border border-amber-dark">
                    Lv. {{ $level }}
                </span>
                <span>Level {{ $level }}</span>
            </span>
            <span class="text-deep-teal/70 font-semibold">
                {{ $progress }} / {{ $nextLevelExp }} EXP
            </span>
        </div>
    @endif

    <div class="w-full bg-paper-dark rounded-full {{ $heightClass }} overflow-hidden border border-paper-dark">
        <div
            class="bg-amber h-full rounded-full transition-all duration-500 ease-out border-b-2 border-amber-dark"
            style="width: {{ max(4, min(100, $progress)) }}%;"
            role="progressbar"
            aria-valuenow="{{ $progress }}"
            aria-valuemin="0"
            aria-valuemax="100"
        ></div>
    </div>
</div>
