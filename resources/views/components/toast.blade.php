<div
    x-data="{
        toasts: [],
        achievements: [],
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
        },
        addAchievement(detail) {
            const id = Date.now();
            const ach = {
                id,
                name: detail.name || 'Pencapaian Baru',
                description: detail.description || 'Kamu telah membuka lencana baru!',
                icon: detail.icon || 'award'
            };
            this.achievements.push(ach);
            this.playAchievementSound();
            setTimeout(() => this.removeAchievement(id), 5500);
        },
        removeAchievement(id) {
            this.achievements = this.achievements.filter(a => a.id !== id);
        },
        playAchievementSound() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                // Celebratory fanfare chime: C5 (523Hz), E5 (659Hz), G5 (784Hz), C6 (1046Hz)
                const notes = [
                    { f: 523.25, t: 0, d: 0.12 },
                    { f: 659.25, t: 0.10, d: 0.12 },
                    { f: 783.99, t: 0.20, d: 0.14 },
                    { f: 1046.50, t: 0.32, d: 0.38 }
                ];
                notes.forEach(n => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(n.f, ctx.currentTime + n.t);
                    gain.gain.setValueAtTime(0.001, ctx.currentTime + n.t);
                    gain.gain.exponentialRampToValueAtTime(0.3, ctx.currentTime + n.t + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + n.t + n.d);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + n.t);
                    osc.stop(ctx.currentTime + n.t + n.d);
                });
            } catch (e) {}
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
        @if (session('achievement'))
            addAchievement({
                name: '{{ addslashes(session('achievement.name', 'Pencapaian Baru')) }}',
                description: '{{ addslashes(session('achievement.description', '')) }}'
            });
        @endif
    "
    @toast.window="add($event.detail)"
    @achievement.window="addAchievement($event.detail)"
>
    <!-- Top-Right Achievement Notifications (Kanan Atas dengan Sound Effect) -->
    <div
        class="fixed top-4 right-4 sm:top-5 sm:right-5 z-50 flex flex-col gap-3 w-full max-w-sm px-2 pointer-events-none select-none"
        aria-live="assertive"
    >
        <template x-for="ach in achievements" :key="ach.id">
            <div
                x-show="true"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-x-8 scale-90"
                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-8 scale-90"
                class="pointer-events-auto bg-amber text-deep-teal border-2 border-deep-teal border-b-4 border-amber-dark rounded-2xl p-4 flex items-start gap-3.5 shadow-xl"
            >
                <!-- Badge Trophy Icon -->
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-deep-teal text-amber flex items-center justify-center flex-shrink-0 border border-teal-soft">
                    <x-lucide-trophy class="w-6 h-6 fill-current animate-bounce" />
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-deep-teal/80 bg-black/10 px-2 py-0.5 rounded-md">
                            🏆 Lencana Terbuka!
                        </span>
                        <button
                            type="button"
                            @click="removeAchievement(ach.id)"
                            class="text-deep-teal/60 hover:text-deep-teal p-1 rounded-lg hover:bg-black/10 focus:outline-none"
                            aria-label="Tutup"
                        >
                            <x-lucide-x class="w-3.5 h-3.5" />
                        </button>
                    </div>
                    <h4 class="font-extrabold text-sm text-deep-teal mt-1 leading-snug truncate" x-text="ach.name"></h4>
                    <p class="text-xs font-semibold text-deep-teal/80 mt-0.5 leading-snug" x-text="ach.description"></p>
                </div>
            </div>
        </template>
    </div>

    <!-- Standard System Toasts (Top Center) -->
    <div
        class="fixed top-5 left-1/2 -translate-x-1/2 z-50 flex flex-col gap-2 w-full max-w-sm px-4 pointer-events-none select-none"
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
</div>
