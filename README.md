# Macha — Platform Micro-Reading Sosial

Macha adalah prototipe aplikasi web micro-reading sosial modern bergaya Duolingo dan Reels. Pengguna dapat men-swipe vertikal untuk membaca kartu bacaan berdurasi 1–5 menit, mengumpulkan EXP, menjaga streak harian, dan bersaing di leaderboard mingguan.

---

## 🎨 Design System & Estetika
Macha dibangun dengan prinsip desain tegas, datar, ramah, dan taktil:
- **Palet Resmi**:
  - `deep-teal` (`#102A2A`): Teks utama, latar gelap, kartu gelap.
  - `kilat` (`#19A974`): Aksi utama, tombol selesai, progres bacaan.
  - `amber` (`#F2C14E`): EXP, streak harian, lencana, highlight.
  - `paper` (`#F7F5EF`): Latar belakang utama aplikasi.
  - Turunan resmi: `kilat-dark` (`#118A5D`), `amber-dark` (`#D4A238`), `teal-soft` (`#1E4040`), `paper-dark` (`#ECE8DC`), dan `error` (`#D9534F`).
- **Tanpa AI Slop**: Tidak ada gradient ungu/biru, tidak ada blur/glassmorphism, tidak ada shadow lembut mengambang berlebihan. Kedalaman dihadirkan lewat **chunky 3D borders** (`border-bottom: 4px` solid warna gelap, aktif bergeser turun `translate-y-0.5`).
- **Tipografi**:
  - **Plus Jakarta Sans**: Heading, navigasi, dan antarmuka UI.
  - **Literata**: Teks bacaan pada kartu artikel untuk kenyamanan membaca optimal.
  - Tersimpan secara lokal di `public/fonts/` (tanpa ketergantungan CDN).
- **Ikon UI**: Lucide SVG murni (`mallardduck/blade-lucide-icons`), dilarang memakai emoji sebagai ikon antarmuka.

---

## 🚀 Fitur Utama
1. **Feed Swipe-to-Read (100dvh Vertical Scroll-Snap)**:
   - 1 bacaan memenuhi layar, swipe vertikal untuk ganti bacaan.
   - Slide horizontal di dalam bacaan (scroll-snap x) dengan indikator segmen ala Stories.
   - 4 tipe slide: `text`, `quote`, `image` (infografis / komik), dan `audio` (pemutar custom dengan slider durasi).
   - Filter tab "Untukmu" (rekomendasi cerdas rule-based) dan "Terbaru" + chip topik tag.
   - Infinite scroll dengan skeleton loading paper-dark.
2. **Gamifikasi & Streak**:
   - +10 EXP saat selesai membaca seluruh slide.
   - Kuis interaktif opsional di layar penutup: +5 EXP partisipasi, +5 EXP bonus jika benar (+10 EXP total).
   - Target harian (3 bacaan selesai/hari) memberi bonus +10 EXP per hari.
   - Streak harian bertambah jika membaca minimal 1 bacaan per hari (berdasarkan zona waktu `Asia/Jakarta`). Reset otomatis jika terlewat > 1 hari.
   - Sistem Level: `Level = floor(total_exp / 100) + 1` dengan progress bar.
   - Peringkat Mingguan (`/peringkat`): Podium visual 3D top 3 (blok bertinggi beda), posisi sticky pengguna saat ini, dan countdown reset Senin 00:00 WIB.
   - 9 Lencana Gamifikasi unik yang dapat dibuka secara otomatis.
3. **Sosial & Interaksi**:
   - Like dengan animasi pantulan taktil (optimistic UI).
   - Lembar komentar (bottom sheet mobile / panel desktop) dengan rate limiting (10 komentar/menit) dan filter kata kasar ramah (`config/matcha.php`).
   - Bagikan bacaan dengan tombol copy link ke clipboard (`/r/{slug}`) lengkap dengan Open Graph meta tags untuk preview media sosial yang rapi.
4. **Kreator Konten**:
   - Form pembuatan bertahap (Wizard 4 langkah) dengan **pratinjau langsung**.
   - Pengaturan urutan slide (naik/turun), pilihan warna latar kartu, upload gambar (maks 2MB) dan audio (maks 5MB).
   - Dasbor "Bacaan Saya" (`/bacaan-saya`) dengan statistik dibaca, selesai, disukai, komentar, dan rasio akurasi kuis.

---

## 🛠️ Persyaratan Sistem
- PHP >= 8.5
- Composer
- Node.js >= 20 & npm
- MySQL (database default: `macha`)

---

## 💻 Panduan Instalasi Cepat

### 1. Kloning & Pengaturan Dependensi
```bash
composer install
npm install
```

### 2. Konfigurasi Lingkungan (`.env`)
Pastikan konfigurasi database di file `.env` sudah sesuai:
```env
APP_NAME=Macha
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=macha
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Buat Symlink Storage
```bash
php artisan storage:link
```

### 4. Migrasi & Seed Database Demo
Jalankan perintah ini untuk mengisi 8 akun demo, 24 bacaan bermutu berbahasa Indonesia, kuis, statistik EXP mingguan, dan aset ilustrasi SVG:
```bash
php artisan migrate:fresh --seed
```

### 5. Kompilasi Aset Frontend
```bash
npm run build
```
Atau jalankan server pengembang:
```bash
npm run dev
```

### 6. Jalankan Server Lokal
```bash
php artisan serve
```
Buka peramban di `http://127.0.0.1:8000`.

---

## 👥 Akun Demo untuk Pengujian
Seluruh akun demo memiliki password: `password`.

| Nama | Username | Email | Streak | Total EXP | Keahlian |
| :--- | :--- | :--- | :---: | :---: | :--- |
| **Budi Santoso** | `budi` | `budi@macha.id` | 7 hari | 350 | Peringkat #1 Mingguan, Kreator |
| **Anisa Rahma** | `anisa` | `anisa@macha.id` | 5 hari | 280 | Peringkat #2 Mingguan |
| **Reza Pratama** | `reza` | `reza@macha.id` | 3 hari | 210 | Peringkat #3 Mingguan |
| **Dinda Ayu** | `dinda` | `dinda@macha.id` | 4 hari | 170 | Pembaca Aktif |
| **Fajar Nugroho** | `fajar` | `fajar@macha.id` | 2 hari | 120 | Pembaca Baru |
| **Siti Nurhaliza** | `siti` | `siti@macha.id` | 1 hari | 90 | Pembaca Baru |
| **Kevin Sanjaya** | `kevin` | `kevin@macha.id` | 1 hari | 60 | Pembaca Baru |
| **Maya Putri** | `maya` | `maya@macha.id` | 0 hari | 30 | Baru Bergabung |

---

## 🧪 Menjalankan Tes Otomatis
Jalankan seluruh Unit dan Feature tests:
```bash
php vendor/bin/phpunit
```
Periksa format kode:
```bash
php vendor/bin/pint --test
```
