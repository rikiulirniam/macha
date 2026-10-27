<x-app-layout>
    <div
        x-data="{
            tab: '{{ $activeTab }}',
            tagId: {{ $activeTagId ?? 'null' }},
            offset: {{ $readings->count() }},
            hasMore: {{ $hasMore ? 'true' : 'false' }},
            loading: false,

            init() {
                const container = this.$refs.feedContainer;
                container.addEventListener('scroll', () => {
                    if (this.loading || !this.hasMore) return;
                    const threshold = 500;
                    if (container.scrollHeight - container.scrollTop - container.clientHeight < threshold) {
                        this.loadMore();
                    }
                });
            },

            async loadMore() {
                if (this.loading || !this.hasMore) return;
                this.loading = true;

                try {
                    let url = `/?tab=${this.tab}&offset=${this.offset}&ajax=1`;
                    if (this.tagId) {
                        url += `&tag=${this.tagId}`;
                    }

                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();

                    if (data.html) {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;
                        while (tempDiv.firstChild) {
                            this.$refs.feedContainer.appendChild(tempDiv.firstChild);
                        }
                    }

                    this.offset = data.next_offset;
                    this.hasMore = data.has_more;
                } catch (e) {
                    console.error('Failed to load more readings', e);
                } finally {
                    this.loading = false;
                }
            }
        }"
        class="relative w-full h-[100dvh] max-h-[100dvh] overflow-hidden bg-paper"
    >
        <!-- Minimal Floating Top Header: Clean "Untukmu" & "Terbaru" Tabs -->
        <header class="absolute top-0 inset-x-0 z-30 flex items-center justify-center pt-3 pb-2 px-4 pointer-events-none select-none">
            <div class="flex items-center gap-6 px-5 py-1.5 rounded-full bg-deep-teal/75 backdrop-blur-md border border-white/15 pointer-events-auto shadow-sm">
                <a
                    href="{{ route('home', ['tab' => 'for-you']) }}"
                    class="font-extrabold text-sm transition-all pb-0.5 border-b-2 {{ $activeTab === 'for-you' ? 'text-amber border-amber' : 'text-paper/70 border-transparent hover:text-paper' }}"
                >
                    Untukmu
                </a>
                <a
                    href="{{ route('home', ['tab' => 'latest']) }}"
                    class="font-extrabold text-sm transition-all pb-0.5 border-b-2 {{ $activeTab === 'latest' ? 'text-amber border-amber' : 'text-paper/70 border-transparent hover:text-paper' }}"
                >
                    Terbaru
                </a>
            </div>
        </header>

        <!-- Vertical Swipe Scroll-Snap Feed Container -->
        <div
            x-ref="feedContainer"
            class="feed-snap-container w-full h-[100dvh]"
        >
            @forelse ($readings as $index => $reading)
                <x-reading-card
                    :reading="$reading"
                    :isFirst="$index === 0 && !request()->has('page')"
                    :isFeed="true"
                />
            @empty
                <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center bg-paper">
                    <div class="w-16 h-16 rounded-2xl bg-paper-dark text-deep-teal flex items-center justify-center mb-4">
                        <x-lucide-book-open class="w-8 h-8 opacity-60" />
                    </div>
                    <h3 class="font-extrabold text-lg text-deep-teal mb-1">Belum Ada Bacaan</h3>
                    <p class="text-xs text-deep-teal/70 font-semibold mb-6 max-w-xs">
                        Tidak ada bacaan untuk topik ini saat ini. Coba pilih topik lain atau buat bacaan barumu sendiri!
                    </p>
                    <x-button as="a" href="{{ route('home') }}" variant="kilat" size="md">
                        Lihat Semua Bacaan
                    </x-button>
                </div>
            @endforelse

            <!-- Skeleton Loading Block for Infinite Scroll -->
            <div
                x-show="loading"
                class="feed-snap-card w-full h-[100dvh] bg-paper p-6 flex flex-col justify-between"
            >
                <div class="h-2 w-full bg-paper-dark rounded-full"></div>
                <div class="space-y-4 my-auto">
                    <div class="h-6 w-3/4 bg-paper-dark rounded-xl"></div>
                    <div class="h-4 w-full bg-paper-dark rounded-lg"></div>
                    <div class="h-4 w-5/6 bg-paper-dark rounded-lg"></div>
                    <div class="h-4 w-2/3 bg-paper-dark rounded-lg"></div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-paper-dark"></div>
                    <div class="h-4 w-32 bg-paper-dark rounded-lg"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
