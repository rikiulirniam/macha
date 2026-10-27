@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-extrabold text-xs uppercase tracking-wider text-deep-teal/80 mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
