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
  - Cukup 1 Bacaan per Hari: Pengguna tidak perlu membaca 3 bacaan untuk streak. Menyelesaikan 1 bacaan dan mengklaim EXP pertama pada hari tersebut langsung mengaktifkan streak hari itu.
  - Jika membaca pada hari yang sama dengan `last_read_date`: streak aktif tetap dipertahankan.
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

### 12. Optimalisasi Tata Letak Bebas Tumpuk pada Tampilan Mobile
- **Keputusan**:
  1. Pada `slide.blade.php`, menambahkan safe padding kanan `pr-14 sm:pr-16 md:pr-0` pada kontainer konten bacaan agar teks artikel tidak pernah tertimpa atau menabrak kolom tombol aksi vertikal (Suka, Komentar, Bagikan).
  2. Menyesuaikan padding atas slide menjadi `pt-16 sm:pt-14 md:pt-10` sehingga header kartu bacaan berada tepat di bawah floating tab "Untukmu/Terbaru" tanpa saling bertumpuk.
  3. Meningkatkan jarak bawah slide (`h-44 sm:h-36 md:h-28`) agar teks artikel berhenti dengan ruang nyaman di atas overlay informasi kreator, judul, segmen slide, dan tombol selesai bacaan.
  4. Merapikan baris kreator dan tag di `reading-card.blade.php` dengan `flex-wrap`, pembatasan lebar teks nama (`truncate`), dan tag responsif agar tidak meluap keluar layar pada viewport mobile sempit (<= 375px).
  5. Menyesuaikan posisi bilah lengket peringkat pengguna di `leaderboard.blade.php` ke `bottom-[68px] md:bottom-4` agar tidak menimpa bilah navigasi bawah mobile.

### 13. Notifikasi Pencapaian (Achievement Toast) di Kanan Atas dengan Efek Suara
- **Keputusan**:
  1. Menyediakan notifikasi pencapaian khusus di sudut kanan atas (`fixed top-4 right-4 sm:top-5 sm:right-5`) yang terpisah dari notifikasi sistem biasa (top center).
  2. Mengimplementasikan efek suara perayaan (*celebratory fanfare chime*) secara mandiri menggunakan **Web Audio API** sintetis (akor C5, E5, G5, C6 bertipe triangle wave). Hal ini menjamin suara berputar instan dengan latensi 0ms tanpa perlu mengunduh file audio eksternal dan bebas resiko error 404.
  3. Desain visual notifikasi mengikuti Design System Macha: berlatar amber solid, border chunky deep-teal, ikon piala dengan animasi pantul, nama lencana, dan deskripsi pencapaian.

### 14. Pemotongan Foto Profil Interaktif (PFP Cropper)
- **Keputusan**:
  1. Membangun modal pemotong foto interaktif mandiri menggunakan **HTML5 Canvas + Alpine.js** tanpa pustaka eksternal pihak ketiga yang berat.
  2. Fitur mencakup: geser/pan dengan sentuhan jari maupun mouse, slider zoom (0.5x – 3.0x) dengan tombol cepat +/- , rotasi foto 90 derajat, tombol reset, dan pratinjau lingkaran avatar mini secara langsung (*real-time*).
  3. Hasil potongan diekspor dalam rasio 1:1 tajam (400x400 piksel) dan didukung secara ganda melalui `DataTransfer` file input dan string `avatar_base64` di backend `ProfileController@update`, memastikan kompatibilitas penuh di seluruh browser modern.

### 15. Pembaruan EXP Real-Time & Pencegahan Klaim Ulang (Idempotensi Gamifikasi)
- **Keputusan**:
  1. **Pencegahan Klaim Ganda (Idempotensi EXP)**:
     - Di `GamificationService::completeReading`, status kelulusan diperiksa melalui `ReadingProgress::completed_at` dan catatan pemberian di `ExpLog` (`source = 'reading_complete'`).
     - Jika bacaan sudah pernah diselesaikan sebelumnya, sistem tidak memberikan EXP tambahan (`exp_awarded = 0`), menandai `'already_completed' => true`, dan mengembalikan statistik terkini pengguna.
     - Begitu pula pada kuis (`GamificationService::answerQuiz`), jawaban yang telah tercatat (`quiz_answered_at !== null`) mengembalikan hasil sebelumnya tanpa memberikan EXP ganda.
  2. **Pembaruan Real-Time Status Pengguna**:
     - Widget kartu status pengguna di sidebar desktop (`layouts.app`) dijadikan komponen reaktif Alpine.js yang mendengarkan event `@exp-gained.window`.
     - Ketika pengguna menyelesaikan bacaan atau menjawab kuis, `reading-card.blade.php` memancarkan custom event `exp-gained` yang membawa payload `total_exp`, `streak`, `level`, dan `progress`.
     - Kartu sidebar secara otomatis menganimasikan angka EXP, jumlah streak, dan lebar level progress bar secara halus (*smooth transition*) dengan efek *bump highlight* tanpa memerlukan reload halaman (*zero page refresh*).
  3. **Visual Feedback pada Tombol & Modal Ringkasan**:
     - Jika bacaan sudah diselesaikan, tombol pada slide terakhir otomatis berstatus `"✓ Sudah Selesai Dibaca (Lihat Ringkasan)"` dengan style paper sekunder.
     - Modal ringkasan menampilkan label `"EXP SUDAH DIKLAIM SEBELUMNYA"` dan keterangan ramah bahwa EXP hanya dapat diperoleh satu kali per bacaan.

### 16. Dukungan Penuh Berkas Audio M4A (MPEG-4 Audio / AAC)
- **Keputusan**:
  1. **Atribut File Picker Klien (`accept`)**:
     - Mengubah `accept="audio/mp3,audio/m4a,audio/ogg"` menjadi `accept=".m4a,.mp3,.ogg,.aac,.wav,audio/*,audio/mp4,audio/x-m4a,audio/mpeg"`.
     - `audio/m4a` bukan tipe MIME standar IANA sehingga dialog pemilihan berkas Windows/macOS menyembunyikan atau memblokir berkas `.m4a`. Penambahan ekstensi eksplisit berawalan titik (`.m4a`, `.mp3`) memastikan sistem operasi membuka dan menampilkan berkas audio secara langsung.
  2. **Validasi Backend Laravel**:
     - Berkas `.m4a` yang dihasilkan oleh berbagai aplikasi perekam suara ponsel (seperti Samsung, Xiaomi, Voice Memos iOS, atau WhatsApp Voice Note) menggunakan kontainer MPEG-4 ber-header `ftyp` yang sering terdeteksi oleh modul PHP `fileinfo` sebagai `audio/mp4`, `audio/x-m4a`, atau `video/mp4`.
     - Validasi diperluas di `StoreReadingRequest` dan `UpdateReadingRequest` dengan kombinasi `mimes:mp3,m4a,mp4,ogg,aac,wav` dan `extensions:mp3,m4a,ogg,aac,wav`, serta batas kapasitas ditingkatkan hingga 10 MB (`max:10240`). Hal ini memungkinkan berkas M4A valid lolos verifikasi tanpa mengizinkan video.
  3. **Formulir Edit Bacaan**:
     - Menambahkan input unggah gambar dan audio pada formulir penyuntingan bacaan (`readings.edit`), sehingga kreator dapat mengganti atau mengunggah audio pada slide yang diedit.

### 17. Lembar Komentar Interaktif, Paginasi, dan Gestur Tarik Mobile
- **Keputusan**:
  1. **Pengambilan Komentar Instan Tanpa Refresh**:
     - Mengubah pemanggilan endpoint `/readings/{id}/comments` agar selalu mengambil data segar secara reaktif setiap kali lembar komentar dibuka via tombol komentar.
     - Badge jumlah komentar pada kartu dan header lembar terhubung secara dinamis dengan Alpine state (`commentsCount`), bertambah dan berkurang secara langsung saat komentar dikirim atau dihapus.
  2. **Paginasi Komentar**:
     - Membatasi kuota awal pemuatan komentar menjadi 10 item per halaman (`per_page=10`).
     - Menyediakan tombol "Lihat lebih banyak komentar" di bagian bawah daftar jika `has_more` bernilai benar untuk memuat halaman berikutnya tanpa membebani browser.
  3. **Interaksi Bottom-Sheet Mobile (Draggable & Expandable)**:
     - Mengintegrasikan gestur sentuh vertikal (`touchstart`, `touchmove`, `touchend`) pada *drag handle* dan *header* lembar komentar mobile.
     - Menggeser ke atas memperbesar tinggi lembar dari 65vh ke 90vh (`h-[90vh]`) untuk membaca lebih banyak komentar secara leluasa. Menggeser ke bawah lebih dari 100px mengecilkan atau menutup lembar komentar.
     - Menyediakan tombol ikon minimize/maximize di pojok kanan atas lembar untuk akses cepat satu ketukan.

### 18. Penguncian Scroll Latar & Header Tab Feed Pinned
- **Keputusan**:
  1. **Kunci Scroll Window pada Feed Mobile**:
     - Layout utama `<x-app-layout :isFeed="true">` mengunci `html`, `body`, dan `<main>` ke tinggi tepat `100dvh` dengan `overflow-hidden` dan `pb-0`, menghilangkan padding berlebih yang sebelumnya menyebabkan jendela browser dapat di-scroll ke bawah.
     - Menambahkan aturan CSS `overscroll-behavior-y: none` untuk mencegah efek membal (*rubber-banding pull*) pada browser mobile.
  2. **Header Tab Pinned Permanen**:
     - Mengubah penempatan header tab "Untukmu | Terbaru" dari `absolute` menjadi `fixed top-0 inset-x-0 md:left-64 lg:left-72 z-30` sehingga tab tetap selalu berada di atas layar dan tidak pernah hilang terdorong saat bernavigasi.
  3. **Background Scroll Lock saat Modal/Sheet Terbuka**:
     - Pada `sheet.blade.php`, watcher Alpine secara otomatis menambahkan kelas `.feed-locked` (`overflow-y: hidden !important; touch-action: none !important;`) pada kontainer feed dan `overflow-hidden` pada `body` saat lembar dibuka.
     - Menambahkan `@touchmove.prevent` pada backdrop dan header serta `overscroll-contain` pada daftar komentar untuk memastikan interaksi di dalam lembar tidak merembes ke feed di belakangnya.

