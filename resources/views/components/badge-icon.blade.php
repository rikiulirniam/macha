@props(['name' => 'award', 'class' => 'w-6 h-6'])

@switch($name)
    @case('book-open')
        <x-lucide-book-open :class="$class" />
        @break
    @case('library')
        <x-lucide-library :class="$class" />
        @break
    @case('trophy')
        <x-lucide-trophy :class="$class" />
        @break
    @case('book-marked')
        <x-lucide-book-marked :class="$class" />
        @break
    @case('flame')
        <x-lucide-flame :class="$class" />
        @break
    @case('award')
        <x-lucide-award :class="$class" />
        @break
    @case('zap')
        <x-lucide-zap :class="$class" />
        @break
    @case('sparkles')
        <x-lucide-sparkles :class="$class" />
        @break
    @case('help-circle')
        <x-lucide-help-circle :class="$class" />
        @break
    @case('brain')
        <x-lucide-brain :class="$class" />
        @break
    @case('message-circle')
        <x-lucide-message-circle :class="$class" />
        @break
    @case('messages-square')
        <x-lucide-messages-square :class="$class" />
        @break
    @case('message-square-quote')
        <x-lucide-message-square-quote :class="$class" />
        @break
    @case('pen-tool')
        <x-lucide-pen-tool :class="$class" />
        @break
    @case('feather')
        <x-lucide-feather :class="$class" />
        @break
    @case('scroll')
        <x-lucide-scroll :class="$class" />
        @break
    @case('heart')
        <x-lucide-heart :class="$class" />
        @break
    @case('thumbs-up')
        <x-lucide-thumbs-up :class="$class" />
        @break
    @case('smile')
        <x-lucide-smile :class="$class" />
        @break
    @case('star')
        <x-lucide-star :class="$class" />
        @break
    @case('crown')
        <x-lucide-crown :class="$class" />
        @break
    @case('compass')
        <x-lucide-compass :class="$class" />
        @break
    @case('shield-check')
        <x-lucide-shield-check :class="$class" />
        @break
    @case('gem')
        <x-lucide-gem :class="$class" />
        @break
    @case('check-circle-2')
        <x-lucide-check-circle-2 :class="$class" />
        @break
    @case('calendar')
        <x-lucide-calendar :class="$class" />
        @break
    @case('sun')
        <x-lucide-sun :class="$class" />
        @break
    @default
        <x-lucide-award :class="$class" />
@endswitch

