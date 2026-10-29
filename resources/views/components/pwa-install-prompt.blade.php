<div
    x-data="{
        visible: false,
        showIosGuide: false,
        isIos: false,
        isStandalone: false,
        canInstall: false,

        init() {
            this.isStandalone = window.pwaManager ? window.pwaManager.isStandalone : false;
            this.isIos = window.pwaManager ? window.pwaManager.isIos : false;

            if (this.isStandalone) {
                return;
            }

            const dismissedAt = localStorage.getItem('macha_pwa_dismissed_at');
            const now = Date.now();
            // Don't show again if dismissed within 5 days
            if (dismissedAt && (now - parseInt(dismissedAt, 10)) < (5 * 24 * 60 * 60 * 1000)) {
                return;
            }

            if (window.pwaManager && window.pwaManager.deferredPrompt) {
                this.canInstall = true;
                setTimeout(() => { this.visible = true; }, 1500);
            }

            window.addEventListener('pwa-installable', () => {
                this.canInstall = true;
                setTimeout(() => { this.visible = true; }, 1500);
            });

            // iOS Safari does not support beforeinstallprompt; show friendly prompt after 2s
            if (this.isIos && !this.isStandalone) {
                setTimeout(() => { this.visible = true; }, 2000);
            }

            window.addEventListener('pwa-installed', () => {
                this.visible = false;
                this.isStandalone = true;
            });

            window.addEventListener('open-pwa-install', () => {
                if (this.isIos) {
                    this.showIosGuide = true;
                    this.visible = true;
                } else if (window.pwaManager && window.pwaManager.deferredPrompt) {
                    window.pwaManager.promptInstall();
                } else {
                    this.visible = true;
                }
            });
        },

        async install() {
            if (this.isIos) {
                this.showIosGuide = true;
                return;
            }

            if (window.pwaManager && window.pwaManager.deferredPrompt) {
                const outcome = await window.pwaManager.promptInstall();
                if (outcome === 'accepted') {
                    this.visible = false;
                }
            } else {
                alert('Untuk memasang Macha, buka menu browser (titik tiga di kanan atas) lalu pilih \'Tambahkan ke Layar Utama\' atau \'Pasang Aplikasi\'.');
            }
        },

        dismiss() {
            this.visible = false;
            this.showIosGuide = false;
            localStorage.setItem('macha_pwa_dismissed_at', Date.now().toString());
        }
    }"
    x-cloak
    x-show="visible"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-8 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-8 scale-95"
    class="fixed bottom-16 md:bottom-6 left-3 right-3 sm:left-auto sm:right-6 sm:w-96 z-50 pointer-events-auto select-none"
    aria-live="polite"
>
    <div class="bg-paper border-2 border-deep-teal border-b-4 rounded-3xl p-4 sm:p-5 shadow-2xl flex flex-col gap-3">
        <!-- Header -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-deep-teal p-1.5 flex items-center justify-center border-2 border-deep-teal flex-shrink-0 shadow-sm">
                    <img src="{{ asset('icons/icon-192x192.png') }}" alt="Macha Logo" class="w-full h-full object-contain">
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-kilat-dark">Aplikasi Web</span>
                    <h3 class="font-extrabold text-sm sm:text-base text-deep-teal leading-tight">Pasang Macha</h3>
                </div>
            </div>

            <button
                type="button"
                @click="dismiss()"
                class="w-7 h-7 rounded-xl flex items-center justify-center text-deep-teal/50 hover:text-deep-teal hover:bg-paper-dark/60 transition-colors"
                aria-label="Tutup"
            >
                <x-lucide-x class="w-4 h-4" />
            </button>
        </div>

        <!-- Normal State Body -->
        <template x-if="!showIosGuide">
            <div class="flex flex-col gap-3">
                <p class="text-xs text-deep-teal/80 font-medium leading-relaxed">
                    Buka Macha langsung dari layar utama HP seperti aplikasi bawaan, tanpa perlu ketik link di browser setiap kali ingin membaca!
                </p>

                <div class="flex items-center gap-2 pt-1">
                    <x-button
                        type="button"
                        variant="kilat"
                        size="sm"
                        class="flex-1 justify-center py-2.5"
                        @click="install()"
                    >
                        <x-lucide-download class="w-4 h-4 mr-1.5" />
                        <span>Pasang Sekarang</span>
                    </x-button>

                    <button
                        type="button"
                        @click="dismiss()"
                        class="px-3 py-2 text-xs font-extrabold text-deep-teal/60 hover:text-deep-teal transition-colors"
                    >
                        Nanti
                    </button>
                </div>
            </div>
        </template>

        <!-- iOS Guide State Body -->
        <template x-if="showIosGuide">
            <div class="flex flex-col gap-2.5 pt-1">
                <p class="text-xs font-bold text-deep-teal">
                    Cara pasang di iPhone / iPad (Safari):
                </p>

                <div class="flex flex-col gap-2 bg-paper-dark/40 p-3 rounded-2xl text-[11px] font-semibold text-deep-teal/90">
                    <div class="flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-deep-teal text-paper flex items-center justify-center flex-shrink-0 font-extrabold text-[10px]">
                            1
                        </div>
                        <span>Ketuk tombol <strong>Bagikan</strong> (<x-lucide-share-2 class="w-3.5 h-3.5 inline mx-0.5 text-deep-teal" />) di menu bawah Safari.</span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-deep-teal text-paper flex items-center justify-center flex-shrink-0 font-extrabold text-[10px]">
                            2
                        </div>
                        <span>Gulir ke bawah dan pilih <strong>Tambahkan ke Layar Utama</strong> (+).</span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-deep-teal text-paper flex items-center justify-center flex-shrink-0 font-extrabold text-[10px]">
                            3
                        </div>
                        <span>Ketuk tombol <strong>Tambah</strong> di sudut kanan atas layar. Selesai!</span>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <x-button
                        type="button"
                        variant="kilat"
                        size="sm"
                        class="w-full justify-center"
                        @click="dismiss()"
                    >
                        Mengerti
                    </x-button>
                </div>
            </div>
        </template>
    </div>
</div>

