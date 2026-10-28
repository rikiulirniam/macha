<x-app-layout :maxWidth="'w-full max-w-xl'">
    <div
        x-data="{
            selectedTags: @js($userTagIds ?? []),
            toggleTag(id) {
                if (this.selectedTags.includes(id)) {
                    if (this.selectedTags.length <= 1) {
                        alert('Pilih minimal 1 topik minat.');
                        return;
                    }
                    this.selectedTags = this.selectedTags.filter(i => i !== id);
                } else {
                    this.selectedTags.push(id);
                }
            },

            // Avatar Cropper State
            showCropModal: false,
            imageSrc: '',
            imageObj: null,
            croppedPreviewUrl: null,
            avatarBase64: '',
            zoom: 1,
            minZoom: 0.5,
            maxZoom: 3,
            offsetX: 0,
            offsetY: 0,
            rotation: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            initialOffsetX: 0,
            initialOffsetY: 0,
            canvasSize: 280,

            onFileSelected(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file foto profil maksimal 2 MB.');
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imageSrc = e.target.result;
                    const img = new Image();
                    img.onload = () => {
                        this.imageObj = img;
                        this.resetCrop();
                        this.showCropModal = true;
                        this.$nextTick(() => {
                            this.drawCropCanvas();
                        });
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            resetCrop() {
                this.zoom = 1;
                this.rotation = 0;
                this.offsetX = 0;
                this.offsetY = 0;
                if (this.imageObj) {
                    this.drawCropCanvas();
                }
            },

            rotateImage() {
                this.rotation = (this.rotation + 90) % 360;
                this.drawCropCanvas();
            },

            adjustZoom(delta) {
                this.zoom = Math.min(this.maxZoom, Math.max(this.minZoom, parseFloat((this.zoom + delta).toFixed(2))));
                this.drawCropCanvas();
            },

            onZoomChange(val) {
                this.zoom = parseFloat(val);
                this.drawCropCanvas();
            },

            startDrag(x, y) {
                this.isDragging = true;
                this.dragStartX = x;
                this.dragStartY = y;
                this.initialOffsetX = this.offsetX;
                this.initialOffsetY = this.offsetY;
            },

            onDrag(x, y) {
                if (!this.isDragging) return;
                const dx = x - this.dragStartX;
                const dy = y - this.dragStartY;
                this.offsetX = this.initialOffsetX + dx;
                this.offsetY = this.initialOffsetY + dy;
                this.drawCropCanvas();
            },

            endDrag() {
                this.isDragging = false;
            },

            drawCropCanvas() {
                const canvas = this.$refs.cropCanvas;
                if (!canvas || !this.imageObj) return;
                const ctx = canvas.getContext('2d');
                const size = this.canvasSize;
                canvas.width = size;
                canvas.height = size;

                ctx.clearRect(0, 0, size, size);

                ctx.save();
                ctx.translate(size / 2 + this.offsetX, size / 2 + this.offsetY);
                ctx.rotate((this.rotation * Math.PI) / 180);

                const naturalW = this.imageObj.naturalWidth;
                const naturalH = this.imageObj.naturalHeight;
                const baseScale = Math.max(size / naturalW, size / naturalH);
                const drawW = naturalW * baseScale * this.zoom;
                const drawH = naturalH * baseScale * this.zoom;

                ctx.drawImage(this.imageObj, -drawW / 2, -drawH / 2, drawW, drawH);
                ctx.restore();

                this.updateMiniPreview();
            },

            updateMiniPreview() {
                const miniCanvas = this.$refs.miniPreviewCanvas;
                if (!miniCanvas || !this.$refs.cropCanvas) return;
                const mCtx = miniCanvas.getContext('2d');
                miniCanvas.width = 64;
                miniCanvas.height = 64;
                mCtx.clearRect(0, 0, 64, 64);
                mCtx.drawImage(this.$refs.cropCanvas, 0, 0, 64, 64);
            },

            cancelCrop() {
                this.showCropModal = false;
                const input = this.$refs.avatarFileInput;
                if (input && !this.croppedPreviewUrl) {
                    input.value = '';
                }
            },

            applyCrop() {
                if (!this.imageObj) return;

                const exportCanvas = document.createElement('canvas');
                const exportSize = 400;
                exportCanvas.width = exportSize;
                exportCanvas.height = exportSize;
                const ctx = exportCanvas.getContext('2d');

                const ratio = exportSize / this.canvasSize;
                ctx.save();
                ctx.translate(exportSize / 2 + this.offsetX * ratio, exportSize / 2 + this.offsetY * ratio);
                ctx.rotate((this.rotation * Math.PI) / 180);

                const naturalW = this.imageObj.naturalWidth;
                const naturalH = this.imageObj.naturalHeight;
                const baseScale = Math.max(this.canvasSize / naturalW, this.canvasSize / naturalH);
                const drawW = naturalW * baseScale * this.zoom * ratio;
                const drawH = naturalH * baseScale * this.zoom * ratio;

                ctx.drawImage(this.imageObj, -drawW / 2, -drawH / 2, drawW, drawH);
                ctx.restore();

                const croppedBase64 = exportCanvas.toDataURL('image/jpeg', 0.92);
                this.croppedPreviewUrl = croppedBase64;
                this.avatarBase64 = croppedBase64;

                try {
                    exportCanvas.toBlob((blob) => {
                        if (blob && window.DataTransfer) {
                            const file = new File([blob], 'cropped-avatar.jpg', { type: 'image/jpeg' });
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            if (this.$refs.avatarFileInput) {
                                this.$refs.avatarFileInput.files = dt.files;
                            }
                        }
                    }, 'image/jpeg', 0.92);
                } catch (e) {}

                this.showCropModal = false;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Foto berhasil dipotong! Tekan Simpan untuk memperbarui profil.', type: 'info' }
                }));
            }
        }"
        class="p-4 sm:p-6 pb-20 w-full flex flex-col gap-6"
    >
        <div class="flex items-center justify-between border-b-2 border-paper-dark pb-4">
            <div>
                <h1 class="font-extrabold text-2xl text-deep-teal tracking-tight">Pengaturan Profil</h1>
                <p class="text-xs text-deep-teal/70 font-semibold mt-0.5">Sesuaikan informasi akun dan topik minat membaca kamu.</p>
            </div>
            <a href="{{ route('profile.show') }}" class="text-xs font-bold text-deep-teal/70 hover:text-deep-teal">
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-error/10 border-2 border-error text-error text-xs font-bold">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Profile Information Form -->
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex flex-col gap-5">
            @csrf
            @method('PATCH')

            <div class="card-paper p-6 border-2 border-paper-dark rounded-3xl flex flex-col gap-4">
                <h2 class="font-extrabold text-base text-deep-teal">Data Diri & Avatar</h2>

                <!-- Avatar Upload with Interactive Cropper -->
                <div class="flex items-center gap-4">
                    <template x-if="croppedPreviewUrl">
                        <img :src="croppedPreviewUrl" class="w-16 h-16 rounded-2xl object-cover border-2 border-kilat shadow-sm">
                    </template>
                    <template x-if="!croppedPreviewUrl">
                        @if ($user->avatar_path)
                            <img src="{{ asset('storage/' . $user->avatar_path) }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-paper-dark">
                        @else
                            <span class="w-16 h-16 rounded-2xl bg-deep-teal text-paper font-black text-2xl flex items-center justify-center border-2 border-teal-soft">
                                {{ $user->initials }}
                            </span>
                        @endif
                    </template>

                    <div class="flex-1">
                        <label class="text-xs font-extrabold uppercase text-deep-teal/80 block mb-1">Ganti Foto Profil (Opsional)</label>
                        <input
                            type="file"
                            x-ref="avatarFileInput"
                            name="avatar"
                            accept="image/png,image/jpeg,image/webp"
                            @change="onFileSelected($event)"
                            class="w-full text-xs font-semibold text-deep-teal file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-paper-dark file:text-deep-teal hover:file:bg-kilat cursor-pointer"
                        >
                        <input type="hidden" name="avatar_base64" :value="avatarBase64">
                        <span class="text-[10px] text-deep-teal/50 font-semibold block mt-1">Maksimal 2 MB. Foto dapat digeser & dizoom sebelum disimpan.</span>

                        <template x-if="croppedPreviewUrl">
                            <button
                                type="button"
                                @click="showCropModal = true; $nextTick(() => drawCropCanvas())"
                                class="mt-2 text-xs font-bold text-kilat-dark hover:underline flex items-center gap-1 cursor-pointer"
                            >
                                <x-lucide-crop class="w-3.5 h-3.5" />
                                <span>Potong Ulang Foto</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Name -->
                <div>
                    <x-input-label for="name" value="Nama Lengkap" />
                    <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required />
                </div>

                <!-- Username -->
                <div>
                    <x-input-label for="username" value="Username Unik" />
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-deep-teal/50 select-none">@</span>
                        <x-text-input id="username" class="!pl-8 lowercase" name="username" type="text" :value="old('username', $user->username)" required />
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <x-input-label for="email" value="Alamat Email" />
                    <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required />
                </div>

                <!-- Interests Selection -->
                <div class="pt-3 border-t border-paper-dark">
                    <div class="flex items-center justify-between mb-2">
                        <x-input-label value="Ubah Topik Minat Membaca" class="!mb-0" />
                        <span class="text-xs font-extrabold text-kilat-dark" x-text="`${selectedTags.length} topik aktif`"></span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach ($tags as $tag)
                            <button
                                type="button"
                                @click="toggleTag({{ $tag->id }})"
                                :class="selectedTags.includes({{ $tag->id }})
                                    ? 'bg-kilat text-deep-teal border-b-4 border-kilat-dark'
                                    : 'bg-paper text-deep-teal border-2 border-paper-dark border-b-4 hover:bg-white'"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs text-left flex items-center justify-between cursor-pointer transition-all active:translate-y-0.5 active:border-b-2"
                            >
                                <span>{{ $tag->name }}</span>
                                <template x-if="selectedTags.includes({{ $tag->id }})">
                                    <x-lucide-check class="w-3.5 h-3.5 stroke-[3]" />
                                </template>
                            </button>
                            <template x-if="selectedTags.includes({{ $tag->id }})">
                                <input type="hidden" name="tags[]" value="{{ $tag->id }}">
                            </template>
                        @endforeach
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <x-button type="submit" variant="kilat" size="md">
                        Simpan Perubahan Profil
                    </x-button>
                </div>
            </div>
        </form>

        <!-- Delete Account Card -->
        <div class="card-paper p-6 border-2 border-paper-dark rounded-3xl flex flex-col gap-3">
            <h2 class="font-extrabold text-base text-error">Hapus Akun</h2>
            <p class="text-xs text-deep-teal/70 font-semibold leading-relaxed">
                Setelah akun dihapus, seluruh data profil, EXP, streak, dan histori membaca kamu akan terhapus secara permanen.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Apakah kamu benar-benar yakin ingin menghapus akun ini? Masukkan kata sandi pada jendela berikutnya.');">
                @csrf
                @method('DELETE')

                <div class="flex items-center gap-2">
                    <x-text-input name="password" type="password" placeholder="Masukkan kata sandi untuk konfirmasi..." required class="!text-xs !py-2" />
                    <x-button type="submit" variant="error" size="sm" class="flex-shrink-0">
                        Hapus Akun
                    </x-button>
                </div>
            </form>
        </div>
        <!-- Modal Pemotong Foto Profil (Crop Modal) -->
        <div
            x-cloak
            x-show="showCropModal"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-deep-teal/85 backdrop-blur-sm select-none"
            role="dialog"
            aria-modal="true"
        >
            <div
                x-show="showCropModal"
                x-transition:enter="transition ease-out duration-200 transform"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150 transform"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-paper border-2 border-paper-dark rounded-3xl p-5 sm:p-6 w-full max-w-sm flex flex-col gap-4 text-center shadow-2xl"
                @click.away="cancelCrop()"
            >
                <div class="flex items-center justify-between border-b border-paper-dark pb-3">
                    <div class="text-left">
                        <h3 class="font-extrabold text-base text-deep-teal flex items-center gap-2">
                            <x-lucide-crop class="w-4 h-4 text-kilat" />
                            <span>Potong Foto Profil</span>
                        </h3>
                        <p class="text-[11px] font-semibold text-deep-teal/70">Geser dan sesuaikan skala foto profil kamu.</p>
                    </div>
                    <button
                        type="button"
                        @click="cancelCrop()"
                        class="p-1 rounded-xl hover:bg-paper-dark text-deep-teal/70 focus:outline-none"
                    >
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>

                <!-- Crop Canvas Viewport with Circular Mask Overlay -->
                <div class="relative mx-auto rounded-2xl overflow-hidden border-2 border-paper-dark bg-black/10 cursor-grab active:cursor-grabbing touch-none select-none" style="width: 280px; height: 280px;">
                    <canvas
                        x-ref="cropCanvas"
                        class="block w-full h-full"
                        @mousedown="startDrag($event.clientX, $event.clientY)"
                        @mousemove="onDrag($event.clientX, $event.clientY)"
                        @mouseup="endDrag()"
                        @mouseleave="endDrag()"
                        @touchstart.passive="startDrag($event.touches[0].clientX, $event.touches[0].clientY)"
                        @touchmove.prevent="onDrag($event.touches[0].clientX, $event.touches[0].clientY)"
                        @touchend="endDrag()"
                    ></canvas>

                    <!-- Translucent Vignette Mask (1:1 Circle & Rounded Square Highlight) -->
                    <div class="absolute inset-0 pointer-events-none border-2 border-dashed border-white/60 rounded-full shadow-[0_0_0_9999px_rgba(16,42,42,0.65)]"></div>
                </div>

                <!-- Controls: Zoom & Rotate & Live Mini Preview -->
                <div class="flex flex-col gap-3 bg-paper-dark/40 p-3 rounded-2xl border border-paper-dark">
                    <!-- Zoom Slider -->
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="adjustZoom(-0.15)"
                            class="w-8 h-8 rounded-xl bg-paper border border-paper-dark flex items-center justify-center font-extrabold text-deep-teal hover:bg-white active:scale-95"
                            title="Perkecil"
                        >
                            <x-lucide-minus class="w-4 h-4" />
                        </button>

                        <input
                            type="range"
                            :min="minZoom"
                            :max="maxZoom"
                            step="0.05"
                            :value="zoom"
                            @input="onZoomChange($event.target.value)"
                            class="flex-1 accent-kilat h-2 bg-paper rounded-lg cursor-pointer"
                        >

                        <button
                            type="button"
                            @click="adjustZoom(0.15)"
                            class="w-8 h-8 rounded-xl bg-paper border border-paper-dark flex items-center justify-center font-extrabold text-deep-teal hover:bg-white active:scale-95"
                            title="Perbesar"
                        >
                            <x-lucide-plus class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Rotate & Reset & Mini Preview Row -->
                    <div class="flex items-center justify-between pt-1 text-xs font-bold text-deep-teal">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="rotateImage()"
                                class="px-2.5 py-1.5 rounded-xl bg-paper border border-paper-dark hover:bg-white flex items-center gap-1.5 active:scale-95 transition-all cursor-pointer"
                            >
                                <x-lucide-rotate-cw class="w-3.5 h-3.5 text-deep-teal" />
                                <span>Putar 90°</span>
                            </button>
                            <button
                                type="button"
                                @click="resetCrop()"
                                class="px-2.5 py-1.5 rounded-xl bg-paper border border-paper-dark hover:bg-white active:scale-95 transition-all text-deep-teal/70 cursor-pointer"
                            >
                                Reset
                            </button>
                        </div>

                        <!-- Live Mini Avatar Preview -->
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-deep-teal/60 font-semibold">Hasil:</span>
                            <canvas x-ref="miniPreviewCanvas" class="w-8 h-8 rounded-full border-2 border-kilat bg-paper object-cover shadow-sm"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2.5 pt-1">
                    <x-button
                        type="button"
                        @click="cancelCrop()"
                        variant="paper"
                        size="md"
                        class="flex-1 !rounded-2xl"
                    >
                        Batal
                    </x-button>
                    <x-button
                        type="button"
                        @click="applyCrop()"
                        variant="kilat"
                        size="md"
                        class="flex-1 !rounded-2xl"
                    >
                        <x-lucide-check class="w-4 h-4 mr-1" />
                        Gunakan Foto
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
