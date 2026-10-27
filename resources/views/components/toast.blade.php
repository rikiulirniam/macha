<div
    x-data="{
        toasts: [],
        add(toast) {
            const id = Date.now();
            const newToast = {
                id,
                message: typeof toast === 'string' ? toast : toast.message,
                type: (typeof toast === 'object' && toast.type) ? toast.type : 'success'
            };
            this.toasts.push(newToast);
            setTimeout(() => this.remove(id), 3500);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-init="
        @if (session('success'))
            add({ message: '{{ addslashes(session('success')) }}', type: 'success' });
        @endif
        @if (session('error'))
            add({ message: '{{ addslashes(session('error')) }}', type: 'error' });
        @endif
        @if (session('status'))
            add({ message: '{{ addslashes(session('status')) }}', type: 'info' });
        @endif
    "
    @toast.window="add($event.detail)"
    class="fixed top-5 left-1/2 -translate-x-1/2 z-50 flex flex-col gap-2 w-full max-w-sm px-4 pointer-events-none"
    aria-live="polite"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
            :class="{
                'bg-kilat text-deep-teal border-b-4 border-kilat-dark': t.type === 'success',
                'bg-amber text-deep-teal border-b-4 border-amber-dark': t.type === 'info',
                'bg-error text-paper border-b-4 border-red-800': t.type === 'error'
            }"
            class="pointer-events-auto flex items-center justify-between px-4 py-3 rounded-2xl border-2 border-deep-teal/10 font-bold text-sm shadow-none"
        >
            <div class="flex items-center gap-2.5">
                <template x-if="t.type === 'success'">
                    <x-lucide-check-circle class="w-5 h-5 flex-shrink-0 text-deep-teal" />
                </template>
                <template x-if="t.type === 'error'">
                    <x-lucide-alert-circle class="w-5 h-5 flex-shrink-0 text-paper" />
                </template>
                <template x-if="t.type === 'info'">
                    <x-lucide-sparkles class="w-5 h-5 flex-shrink-0 text-deep-teal" />
                </template>
                <span x-text="t.message"></span>
            </div>
            <button
                type="button"
                @click="remove(t.id)"
                class="ml-3 p-1 rounded-lg hover:bg-black/10 focus:outline-none"
                aria-label="Tutup notifikasi"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>
    </template>
</div>
