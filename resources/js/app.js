import Alpine from 'alpinejs';

window.Alpine = Alpine;

// PWA Service Worker & Install Prompt Controller
window.pwaManager = {
    deferredPrompt: null,
    isInstalled: false,
    isIos: false,
    isStandalone: false,

    init() {
        // Detect standalone display mode
        this.isStandalone = window.matchMedia('(display-mode: standalone)').matches 
            || window.navigator.standalone === true;

        // Detect iOS
        const userAgent = window.navigator.userAgent.toLowerCase();
        this.isIos = /iphone|ipad|ipod/.test(userAgent) && !window.MSStream;

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then((registration) => {
                        // Check for SW updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            if (newWorker) {
                                newWorker.addEventListener('statechange', () => {
                                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                        window.dispatchEvent(new CustomEvent('pwa-updated'));
                                    }
                                });
                            }
                        });
                    })
                    .catch((err) => {
                        console.warn('PWA: Service Worker registration failed', err);
                    });
            });
        }

        // Listen for beforeinstallprompt (Android / Chromium)
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            window.dispatchEvent(new CustomEvent('pwa-installable'));
        });

        // Listen for appinstalled
        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.isInstalled = true;
            window.dispatchEvent(new CustomEvent('pwa-installed'));
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    message: 'Macha berhasil dipasang di layar utama!',
                    type: 'success'
                }
            }));
        });
    },

    async promptInstall() {
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                this.deferredPrompt = null;
            }
            return outcome;
        }
        return null;
    }
};

window.pwaManager.init();

Alpine.start();
