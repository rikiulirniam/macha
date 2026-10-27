# Log Keputusan Teknis & Arsitektur Macha

Dokumen ini mencatat keputusan-keputusan arsitektural dan desain teknis yang diambil selama pengembangan prototipe Macha sesuai pedoman proyek.

---

### 1. Stack Autentikasi (Breeze Blade)
- **Keputusan**: Menggunakan Laravel Breeze dengan stack Blade, dikombinasikan dengan kustomisasi tampilan dan penambahan field `username` pada alur registrasi & profil.
- **Alasan**: Laravel Breeze versi 2.4.2 terbukti kompatibel penuh dengan Laravel 13 dan PHP 8.5, menyediakan controller otentikasi standar yang teruji (CSRF, throttling, password hashing, session regeneration) tanpa menambahkan framework runtime berat seperti React/Vue/Livewire.
- **Penyesuaian**: Tampilan login, register, dan profil dirombak total menggunakan Design System Macha (palet resmi, chunky borders, typography Plus Jakarta Sans, tombol taktil ala Duolingo).

### 2. Styling & Design System (Tailwind CSS v3 + CSS Variables)
- **Keputusan**: Mendaftarkan token palet resmi di `tailwind.config.js` dan variabel CSS di `resources/css/app.css`.
- **Palet Resmi**:
  - `deep-teal`: `#102A2A` (teks utama, kartu gelap, nav gelap)
  - `kilat`: `#19A974` (aksi utama, tombol, progres)
  - `amber`: `#F2C14E` (EXP, streak, badge, highlight)
  - `paper`: `#F7F5EF` (latar belakang utama aplikasi)
  - `kilat-dark`: `#118A5D` (sisi bawah/chunky border tombol kilat)
  - `amber-dark`: `#D4A238` (sisi bawah tombol amber)
  - `teal-soft`: `#1E4040` (permukaan & divider di atas deep-teal)
  - `paper-dark`: `#ECE8DC` (border/divider di atas paper)
  - `error`: `#D9534F` (hanya untuk pesan error / jawaban salah)
- **Kombinasi Kontras Ketat**:
  - `paper` latar + `deep-teal` teks
  - `deep-teal` latar + `paper` teks
  - `kilat` latar + `deep-teal` teks (tidak ada teks putih di atas kilat)
  - `amber` latar + `deep-teal` teks

### 3. Ikon UI (mallardduck/blade-lucide-icons)
- **Keputusan**: Menggunakan paket `mallardduck/blade-lucide-icons` untuk SVG Lucide murni.
- **Aturan**: DILARANG keras menggunakan emoji sebagai ikon antarmuka pengguna. Seluruh ikon navigasi, status, like, streak, dan badge menggunakan komponen blade `<x-lucide-...>`.

### 4. Font Lokal (Plus Jakarta Sans & Literata)
- **Keputusan**: Font disimpan lokal secara mandiri di `public/fonts/` dalam format TTF/WOFF2 tanpa ketergantungan CDN eksternal.
  - Plus Jakarta Sans (400, 600, 700, 800) untuk seluruh elemen UI, heading, dan tombol.
  - Literata (400, 600, italic 400) khusus untuk konten artikel bacaan di kartu kartu bacaan agar nyaman dibaca.

### 5. Rumus Rekomendasi (RecommendationService)
- **Aturan Skor**:
  - `+5` poin per tag yang cocok dengan tag minat onboarding pengguna
  - `+3` poin per tag yang cocok dengan tag dari bacaan yang disukai (like) dalam 30 hari terakhir
  - `+2` poin jika kreator pernah di-like kontennya oleh pengguna
  - `+2` poin jika bacaan diterbitkan kurang dari 3 hari yang lalu
  - `+1 × log10(1 + jumlah like)` untuk popularitas
  - Skor dikali `0.1` jika pengguna sudah menyelesaikan bacaan tersebut (deprioritas)
  - `Jitter` acak `±1` untuk menghindari stagnasi feed
  - **Eksplorasi**: Setiap kelipatan 5 item pada feed rekomendasi, disisipkan 1 bacaan di luar minat pengguna.

### 6. Arsitektur Gamifikasi & EXP Mingguan
- **Format Week Key**: Menggunakan format ISO `GGGG-[W]WW` (contoh: `2026-W44`) berbasis zona waktu `Asia/Jakarta`.
- **Reset Otomatis**: Leaderboard mingguan dihitung dengan `SUM(amount)` dari tabel `exp_logs` pada `week_key` minggu berjalan. Dengan pendekatan ini, reset mingguan terjadi secara deterministik dan otomatis setiap hari Senin pukul 00:00 WIB tanpa memerlukan cron job atau worker latar belakang tambahan.
- **Logika Streak**: Dihitung berdasarkan tanggal kalender Asia/Jakarta.
  - Jika membaca pada hari yang sama dengan `last_read_date`: streak tidak berubah.
  - Jika membaca tepat satu hari setelah `last_read_date` (kemarin): streak bertambah `+1`. Jika `current_streak > longest_streak`, rekor diperbarui.
  - Jika terlewat lebih dari 1 hari: streak di-reset ke `1`. Rekor `longest_streak` tetap dipertahankan.
- **Sistem Level**: `Level = floor(total_exp / 100) + 1`. Total EXP tidak pernah di-reset.

### 7. Swipe Vertikal & Horisontal (Native CSS Scroll Snap)
- **Keputusan**: Menggunakan murni CSS native `scroll-snap-type: y mandatory` untuk feed vertikal layar penuh (`100dvh`) dan `scroll-snap-type: x mandatory` untuk pergantian slide di dalam satu kartu bacaan.
- **Alasan**: Menghindari pemakaian library swipe pihak ketiga yang berat (seperti Swiper.js atau Hammer.js) yang sering menimbulkan konflik performa atau masalah scroll-locking pada peramban mobile modern. Alpine.js hanya digunakan untuk sinkronisasi indeks aktif dan trigger event AJAX.

### 8. Moderasi Komentar & Keamanan
- **Rate Limiting**: Dibatasi maksimal 10 komentar per menit per pengguna menggunakan `RateLimiter` Laravel.
- **Penyaringan Kata Kasar**: Daftar kata terlarang disimpan di `config/matcha.php` (`banned_words`). Komentar yang melanggar ditolak di server dengan respons HTTP 422 dan pesan edukatif yang ramah tanpa kata-kata menghakimi.

### 9. Penataan Ulang Header Feed & Indikator Slide
- **Keputusan**:
  1. Menghapus deretan chip kategori dari header feed scroll layar penuh agar tidak mendistraksi dan mempersempit pandangan membaca pengguna. Kategori/tag topik tetap dikelola di profil pengguna (`/profil`) dan alur onboarding (`/onboarding`), serta dipersiapkan untuk halaman pencarian/eksplorasi mandiri.
  2. Memindahkan indikator garis segmen slide dari bagian paling atas kartu (`absolute top-0`) ke bagian bawah kartu, ditempatkan tepat di bawah judul bacaan (`<h2 class="title">`) dan di atas tombol aksi.
  3. Header atas feed disederhanakan menjadi pill mengambang (*floating pill*) minimalis di tengah yang hanya memuat tab navigasi **Untukmu** dan **Terbaru**, mengeliminasi tabrakan visual (*overlapping*) dan memberikan fokus penuh pada isi konten slide bacaan.

### 10. Optimalisasi Tata Letak Halaman Pembuatan Konten (/buat)
- **Keputusan**:
  1. Memperbaiki komponen `AppLayout` agar meneruskan prop `maxWidth` ke view induk `layouts.app` secara eksplisit, dan meningkatkan batas lebar halaman pembuatan konten menjadi `max-w-5xl` (sebelumnya terperangkap pada batas default `max-w-[480px]`).
  2. Pada tampilan Desktop (`lg:`), halaman pembuatan konten menggunakan tata letak 2 kolom berdampingan (*side-by-side*): kolom kiri untuk editor slide yang lega tanpa teks dropdown yang terpotong, dan kolom kanan untuk *mockup smartphone* interaktif yang menampilkan live preview dengan rasio presisi beserta garis indikator slide di bawah judul.
  3. Pada tampilan Mobile (< `1024px`), disediakan tab navigasi beralih (*view switcher*): **Editor Slide** (memberikan lebar penuh 100% untuk kenyamanan mengetik dan memilih opsi) dan **Pratinjau Live** (melihat simulasi kartu secara utuh) sehingga formulir tidak terjepit menjadi kolom sempit.

### 11. Perbaikan Keterlihatan Penuh Halaman Bacaan Mandiri (/r/{slug})
- **Keputusan**:
  1. Mengubah header statis pada `readings.public` menjadi header mengambang (*floating navigation*) `absolute top-0` dengan tombol kembali ke Beranda dan profil kreator/daftar, sehingga kontainer kartu bacaan mendapatkan tinggi penuh viewport `100dvh` tanpa terpotong (*overflow-hidden clipping*).
  2. Menambahkan prop boolean `:isFeed` pada `reading-card.blade.php`:
     - Pada feed utama (`:isFeed="true"`): padding bawah overlay diberi `pb-20 md:pb-5` untuk mengosongkan ruang bagi bilah navigasi bawah mobile.
     - Pada halaman mandiri `/r/{slug}` (`:isFeed="false"`): padding bawah overlay disesuaikan menjadi `pb-5` yang ringkas karena tidak ada bilah navigasi bawah, memastikan judul bacaan, garis segmen slide, dan tombol "Selesai Membaca (+10 EXP)" 100% terlihat jelas dan mudah diketuk.
  3. Menambahkan tombol navigasi chevron kiri & kanan di desktop pada kartu bacaan sehingga pembaca di komputer dapat beralih slide menggunakan klik mouse selain klik pada garis indikator.



