@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'w-full px-4 py-3 bg-white text-deep-teal font-medium placeholder:text-deep-teal/40 border-2 border-paper-dark rounded-xl focus:border-kilat focus:ring-2 focus:ring-kilat/20 focus:outline-none transition-colors disabled:bg-paper-dark/40 disabled:cursor-not-allowed shadow-none'
    ]) }}
>
