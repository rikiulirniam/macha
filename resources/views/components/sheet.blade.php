@props([
    'name' => 'sheetOpen',
    'title' => '',
])

<div
    x-cloak
    x-show="{{ $name }}"
    x-data="{
        expanded: false,
        dragY: 0,
        startY: 0,
        isDragging: false,
        startDrag(e) {
            this.isDragging = true;
            this.startY = e.touches[0].clientY;
            this.dragY = 0;
        },
        onDrag(e) {
            if (!this.isDragging) return;
            const currentY = e.touches[0].clientY;
            const delta = currentY - this.startY;
            if (!this.expanded && delta < -40) {
                this.expanded = true;
                this.isDragging = false;
                this.dragY = 0;
                return;
            }
            if (delta > 0) {
                this.dragY = delta;
            } else {
                this.dragY = 0;
            }
        },
        endDrag() {
            if (!this.isDragging) return;
            this.isDragging = false;
            if (this.dragY > 100) {
                if (this.expanded) {
                    this.expanded = false;
                    this.dragY = 0;
                } else {
                    {{ $name }} = false;
                    this.dragY = 0;
                }
            } else {
                this.dragY = 0;
            }
        }
    }"
    x-init="
        $watch('{{ $name }}', val => {
            const feed = document.querySelector('.feed-snap-container');
            if (val) {
                document.body.classList.add('overflow-hidden');
                if (feed) feed.classList.add('feed-locked');
            } else {
                document.body.classList.remove('overflow-hidden');
                if (feed) feed.classList.remove('feed-locked');
            }
        });
        window.addEventListener('popstate', () => {
            document.body.classList.remove('overflow-hidden');
            const feed = document.querySelector('.feed-snap-container');
            if (feed) feed.classList.remove('feed-locked');
        });
    "
    @keydown.escape.window="if ({{ $name }}) { {{ $name }} = false; }"
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
        @touchmove.prevent
        class="fixed inset-0 bg-deep-teal/60 transition-opacity"
        aria-hidden="true"
    ></div>

    <!-- Sheet container (Bottom-sheet on mobile, Centered/Side panel on desktop) -->
    <div class="fixed inset-x-0 bottom-0 md:inset-y-auto md:bottom-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-lg z-50 flex flex-col pointer-events-none">
        <div
            x-show="{{ $name }}"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="translate-y-full md:translate-y-8 md:scale-95 md:opacity-0"
            x-transition:enter-end="translate-y-0 md:translate-y-0 md:scale-100 md:opacity-100"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="translate-y-0 md:translate-y-0 md:scale-100 md:opacity-100"
            x-transition:leave-end="translate-y-full md:translate-y-8 md:scale-95 md:opacity-0"
            :class="[
                expanded ? 'h-[90vh] max-h-[90vh]' : 'h-[65vh] max-h-[65vh]',
                isDragging ? 'select-none pointer-events-auto' : 'transition-all duration-300 ease-out pointer-events-auto'
            ]"
            :style="isDragging && dragY > 0 ? `transform: translateY(${dragY}px); transition: none;` : ''"
            class="bg-paper border-t-2 md:border-2 border-paper-dark rounded-t-3xl md:rounded-3xl shadow-2xl overflow-hidden flex flex-col md:!h-auto md:max-h-[82vh] w-full overscroll-contain"
        >
            <!-- Drag Handle for Mobile -->
            <div
                @touchstart="startDrag($event)"
                @touchmove.prevent="onDrag($event)"
                @touchend="endDrag()"
                @click="expanded = !expanded"
                class="flex flex-col items-center justify-center pt-3 pb-2 md:hidden cursor-grab active:cursor-grabbing select-none touch-none"
                title="Geser atau klik untuk perbesar/kecilkan"
            >
                <div class="w-12 h-1.5 bg-paper-dark rounded-full hover:bg-deep-teal/30 transition-colors"></div>
            </div>

            <!-- Header -->
            <div
                @touchstart="startDrag($event)"
                @touchmove.prevent="onDrag($event)"
                @touchend="endDrag()"
                class="flex items-center justify-between px-5 py-3 border-b border-paper-dark select-none touch-none"
            >
                <h3 class="font-extrabold text-base sm:text-lg text-deep-teal tracking-tight flex items-center gap-1.5">
                    @if (isset($header))
                        {{ $header }}
                    @else
                        {{ $title }}
                    @endif
                </h3>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        @click="expanded = !expanded"
                        class="p-2 rounded-xl text-deep-teal/60 hover:text-deep-teal hover:bg-paper-dark/60 md:hidden transition-colors"
                        :title="expanded ? 'Kecilkan' : 'Perbesar lembar komentar'"
                        aria-label="Perbesar atau kecilkan lembar komentar"
                    >
                        <template x-if="!expanded">
                            <x-lucide-maximize-2 class="w-4 h-4 stroke-[2.5]" />
                        </template>
                        <template x-if="expanded">
                            <x-lucide-minimize-2 class="w-4 h-4 stroke-[2.5]" />
                        </template>
                    </button>
                    <button
                        type="button"
                        @click="{{ $name }} = false"
                        class="p-2 rounded-xl text-deep-teal/60 hover:text-deep-teal hover:bg-paper-dark/60 focus:outline-none transition-colors"
                        aria-label="Tutup panel"
                    >
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Body Content -->
            <div class="flex-1 overflow-hidden flex flex-col p-4 sm:p-5 min-h-0 overscroll-contain">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
