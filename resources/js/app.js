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

// Comments Drawer Alpine Component
Alpine.data('commentDrawer', (readingId) => ({
    comments: [],
    page: 1,
    hasMore: false,
    loading: false,
    loadingMore: false,
    newComment: '',
    submitting: false,
    errorMessage: '',
    loaded: false,

    init() {
        this.$watch('commentsOpen', (val) => {
            if (val && !this.loaded) {
                this.loadComments(true);
            }
        });

        const loadHandler = () => this.loadComments(true);
        const incomingHandler = (e) => this.handleIncoming(e.detail);

        window.addEventListener(`load-comments-${readingId}`, loadHandler);
        window.addEventListener(`incoming-comment-${readingId}`, incomingHandler);

        this._cleanup = () => {
            window.removeEventListener(`load-comments-${readingId}`, loadHandler);
            window.removeEventListener(`incoming-comment-${readingId}`, incomingHandler);
        };
    },

    destroy() {
        if (this._cleanup) {
            this._cleanup();
        }
    },

    handleIncoming(incoming) {
        if (!incoming || !incoming.id) return;
        if (!this.comments.some((c) => c.id === incoming.id)) {
            incoming.is_new = true;
            this.comments.unshift(incoming);
            if (this.$refs.commentsList) {
                this.$refs.commentsList.scrollTo({ top: 0, behavior: 'smooth' });
            }
            setTimeout(() => {
                incoming.is_new = false;
            }, 4000);
        }
    },

    async loadComments(reset = true) {
        if (reset) {
            this.page = 1;
            this.loading = true;
        }
        try {
            const res = await fetch(`/readings/${readingId}/comments?page=${this.page}&per_page=10`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            this.comments = reset ? (data.comments || []) : [...this.comments, ...(data.comments || [])];
            this.hasMore = !!data.has_more;
            this.loaded = true;
            if (data.total !== undefined) {
                window.dispatchEvent(new CustomEvent(`update-comments-count-${readingId}`, {
                    detail: { count: data.total }
                }));
            }
        } catch (e) {
            this.loaded = true;
        } finally {
            this.loading = false;
        }
    },

    async loadMore() {
        if (!this.hasMore || this.loadingMore) return;
        this.loadingMore = true;
        const nextPage = this.page + 1;
        try {
            const res = await fetch(`/readings/${readingId}/comments?page=${nextPage}&per_page=10`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.comments) {
                this.comments = [...this.comments, ...data.comments];
                this.page = nextPage;
                this.hasMore = !!data.has_more;
                if (data.total !== undefined) {
                    window.dispatchEvent(new CustomEvent(`update-comments-count-${readingId}`, {
                        detail: { count: data.total }
                    }));
                }
            }
        } catch (e) {
        } finally {
            this.loadingMore = false;
        }
    },

    async postComment() {
        if (!this.newComment.trim() || this.submitting) return;

        const commentBody = this.newComment.trim();
        const isHaiTrigger = commentBody.toLowerCase() === 'hai';
        const isStopTrigger = commentBody.toLowerCase() === 'stop';

        // Trigger auto comment stream every 2s when user comments 'hai'
        if (isHaiTrigger) {
            window.dispatchEvent(new CustomEvent(`trigger-demo-auto-${readingId}`));
        } else if (isStopTrigger) {
            window.dispatchEvent(new CustomEvent(`pause-demo-auto-${readingId}`));
        }

        this.submitting = true;
        this.errorMessage = '';

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const res = await fetch(`/readings/${readingId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ body: commentBody })
            });
            const data = await res.json();
            if (res.ok) {
                this.comments.unshift(data.comment);
                this.newComment = '';
                window.dispatchEvent(new CustomEvent(`increment-comments-count-${readingId}`));
                if (data.badge_unlocked) {
                    window.dispatchEvent(new CustomEvent('achievement', {
                        detail: {
                            name: data.badge_unlocked.name,
                            description: data.badge_unlocked.description,
                            icon: data.badge_unlocked.icon || 'message-circle'
                        }
                    }));
                }
            } else {
                this.errorMessage = data.message || 'Gagal mengirim komentar.';
            }
        } catch (e) {
            this.errorMessage = 'Terjadi kesalahan jaringan.';
        } finally {
            this.submitting = false;
        }
    },

    async deleteComment(id) {
        if (!confirm('Hapus komentar ini?')) return;
        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const res = await fetch(`/comments/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '',
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                this.comments = this.comments.filter((c) => c.id !== id);
                window.dispatchEvent(new CustomEvent(`decrement-comments-count-${readingId}`));
            }
        } catch (e) {}
    }
}));

Alpine.start();
