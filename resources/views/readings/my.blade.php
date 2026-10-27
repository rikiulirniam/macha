<x-app-layout :maxWidth="'w-full max-w-3xl'">
    <div class="p-4 sm:p-6 w-full flex flex-col gap-6">
        <!-- Top bar with Title and Create New button -->
        <div class="flex items-center justify-between border-b-2 border-paper-dark pb-4">
            <div>
                <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight">Bacaan Saya</h1>
                <p class="text-xs text-deep-teal/70 font-semibold mt-0.5">Kelola karya dan pantau statistik interaksi pembaca.</p>
            </div>
            <x-button as="a" href="{{ route('readings.create') }}" variant="kilat" size="md">
                <x-lucide-plus class="w-4 h-4 mr-1" />
                Buat Baru
            </x-button>
        </div>

        @if ($readings->isEmpty())
            <div class="card-paper p-10 border-2 border-paper-dark rounded-3xl text-center flex flex-col items-center justify-center gap-3">
                <div class="w-16 h-16 rounded-2xl bg-paper-dark flex items-center justify-center text-deep-teal/60">
                    <x-lucide-pen-tool class="w-8 h-8" />
                </div>
                <h3 class="font-extrabold text-lg text-deep-teal">Belum Ada Bacaan yang Dibuat</h3>
                <p class="text-xs text-deep-teal/70 font-semibold max-w-sm">
                    Bagikan wawasan menarikmu dalam kartu bacaan 1-5 menit dan bantu ribuan pembaca belajar hal baru setiap hari!
                </p>
                <div class="pt-2">
                    <x-button as="a" href="{{ route('readings.create') }}" variant="kilat" size="md">
                        Mulai Tulis Bacaan Pertama &rarr;
                    </x-button>
                </div>
            </div>
        @else
            <!-- Reading Cards List -->
            <div class="flex flex-col gap-4">
                @foreach ($readings as $reading)
                    <div class="card-paper p-5 border-2 border-paper-dark rounded-3xl flex flex-col gap-4 shadow-none">
                        <!-- Header: Title, Status badge, Tags -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex flex-col gap-1.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider {{ $reading->status === 'published' ? 'bg-kilat text-deep-teal border border-kilat-dark' : 'bg-paper-dark text-deep-teal/70' }}">
                                        {{ $reading->status === 'published' ? 'Terbit' : 'Draf' }}
                                    </span>
                                    @foreach ($reading->tags as $t)
                                        <span class="text-[11px] font-bold text-deep-teal/70">
                                            #{{ $t->name }}
                                        </span>
                                    @endforeach
                                    <span class="text-[11px] text-deep-teal/50 font-semibold ml-auto">
                                        {{ $reading->created_at->locale('id')->diffForHumans() }}
                                    </span>
                                </div>

                                <h2 class="font-extrabold text-lg text-deep-teal leading-snug">
                                    <a href="{{ route('readings.public', ['slug' => $reading->slug]) }}" class="hover:underline">
                                        {{ $reading->title }}
                                    </a>
                                </h2>
                            </div>
                        </div>

                        <!-- Statistics Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 pt-3 border-t border-paper-dark text-center select-none">
                            <!-- Views -->
                            <div class="p-2.5 rounded-2xl bg-paper border border-paper-dark flex flex-col items-center justify-center">
                                <div class="flex items-center gap-1 text-deep-teal/60 mb-0.5">
                                    <x-lucide-eye class="w-3.5 h-3.5" />
                                    <span class="text-[10px] font-bold">Dilihat</span>
                                </div>
                                <span class="font-extrabold text-base text-deep-teal">{{ $reading->views_count }}</span>
                            </div>

                            <!-- Completions -->
                            <div class="p-2.5 rounded-2xl bg-paper border border-paper-dark flex flex-col items-center justify-center">
                                <div class="flex items-center gap-1 text-deep-teal/60 mb-0.5">
                                    <x-lucide-check-circle-2 class="w-3.5 h-3.5 text-kilat-dark" />
                                    <span class="text-[10px] font-bold">Selesai</span>
                                </div>
                                <span class="font-extrabold text-base text-deep-teal">{{ $reading->completions_count }}</span>
                            </div>

                            <!-- Likes -->
                            <div class="p-2.5 rounded-2xl bg-paper border border-paper-dark flex flex-col items-center justify-center">
                                <div class="flex items-center gap-1 text-deep-teal/60 mb-0.5">
                                    <x-lucide-heart class="w-3.5 h-3.5 text-error" />
                                    <span class="text-[10px] font-bold">Suka</span>
                                </div>
                                <span class="font-extrabold text-base text-deep-teal">{{ $reading->likes_count }}</span>
                            </div>

                            <!-- Comments -->
                            <div class="p-2.5 rounded-2xl bg-paper border border-paper-dark flex flex-col items-center justify-center">
                                <div class="flex items-center gap-1 text-deep-teal/60 mb-0.5">
                                    <x-lucide-message-circle class="w-3.5 h-3.5" />
                                    <span class="text-[10px] font-bold">Komentar</span>
                                </div>
                                <span class="font-extrabold text-base text-deep-teal">{{ $reading->comments_count }}</span>
                            </div>

                            <!-- Quiz Accuracy Ratio -->
                            <div class="p-2.5 rounded-2xl bg-paper border border-paper-dark flex flex-col items-center justify-center col-span-2 sm:col-span-1">
                                <div class="flex items-center gap-1 text-deep-teal/60 mb-0.5">
                                    <x-lucide-sparkles class="w-3.5 h-3.5 text-amber-dark" />
                                    <span class="text-[10px] font-bold">Akurasi Kuis</span>
                                </div>
                                <span class="font-extrabold text-base text-deep-teal">
                                    {{ $reading->quiz_accuracy_ratio !== null ? $reading->quiz_accuracy_ratio . '%' : '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions bar -->
                        <div class="flex items-center justify-between pt-2 border-t border-paper-dark">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-deep-teal/60">
                                    {{ $reading->slides->count() }} Slide &bull; ~{{ $reading->reading_minutes }} menit baca
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-button as="a" href="{{ route('readings.edit', $reading) }}" variant="paper" size="sm">
                                    <x-lucide-edit-3 class="w-3.5 h-3.5 mr-1" />
                                    Edit
                                </x-button>

                                <form method="POST" action="{{ route('readings.destroy', $reading) }}" onsubmit="return confirm('Apakah kamu yakin ingin menghapus bacaan ini? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="error" size="sm">
                                        <x-lucide-trash-2 class="w-3.5 h-3.5 mr-1" />
                                        Hapus
                                    </x-button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
