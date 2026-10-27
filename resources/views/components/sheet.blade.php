@props([
    'name' => 'sheetOpen',
    'title' => '',
])

<div
    x-cloak
    x-show="{{ $name }}"
    class="fixed inset-0 z-50 overflow-hidden"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        x-show="{{ $name }}"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="{{ $name }} = false"
        class="fixed inset-0 bg-deep-teal/60 transition-opacity"
        aria-hidden="true"
    ></div>

    <!-- Sheet container (Bottom-sheet on mobile, Centered/Side panel on desktop) -->
    <div class="fixed inset-x-0 bottom-0 max-h-[85vh] md:max-h-[80vh] md:inset-y-auto md:bottom-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-lg z-50 flex flex-col">
        <div
            x-show="{{ $name }}"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="translate-y-full md:translate-y-8 md:scale-95 md:opacity-0"
            x-transition:enter-end="translate-y-0 md:translate-y-0 md:scale-100 md:opacity-100"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="translate-y-0 md:translate-y-0 md:scale-100 md:opacity-100"
            x-transition:leave-end="translate-y-full md:translate-y-8 md:scale-95 md:opacity-0"
            class="bg-paper border-t-2 md:border-2 border-paper-dark rounded-t-3xl md:rounded-3xl shadow-none overflow-hidden flex flex-col max-h-[85vh] md:max-h-[80vh]"
        >
            <!-- Drag Handle for Mobile -->
            <div class="flex justify-center pt-3 pb-1 md:hidden">
                <div class="w-12 h-1.5 bg-paper-dark rounded-full"></div>
            </div>

            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-3 border-b border-paper-dark">
                <h3 class="font-extrabold text-lg text-deep-teal tracking-tight">
                    {{ $title }}
                </h3>
                <button
                    type="button"
                    @click="{{ $name }} = false"
                    class="p-2 rounded-xl text-deep-teal/60 hover:text-deep-teal hover:bg-paper-dark/60 focus:outline-none transition-colors"
                    aria-label="Tutup panel"
                >
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            <!-- Body Content -->
            <div class="flex-1 overflow-y-auto p-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
