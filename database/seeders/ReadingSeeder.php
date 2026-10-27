<?php

namespace Database\Seeders;

use App\Models\Reading;
use App\Models\ReadingQuiz;
use App\Models\ReadingSlide;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReadingSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureIllustrationsExist();

        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $tags = Tag::all()->keyBy('name');

        $readingsData = [
            [
                'title' => 'Teknik Pomodoro: Rahasia Fokus 25 Menit Tanpa Lelah',
                'description' => 'Metode manajemen waktu klasik yang terbukti melipatgandakan produktivitas harian.',
                'tags' => ['Pengembangan Diri', 'Bisnis'],
                'user_index' => 0,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Pernahkah kamu duduk untuk belajar atau bekerja, tapi baru sepuluh menit pikiranmu sudah melayang ke media sosial?\n\nOtak manusia secara alami sulit mempertahankan konsentrasi penuh lebih dari 30 menit tanpa penurunan kualitas fokus. Di sinilah Teknik Pomodoro hadir menyelamatkan harimu.",
                    ],
                    [
                        'type' => 'image',
                        'bg_color' => 'teal',
                        'media_path' => 'illustrations/pomodoro-focus.svg',
                        'body' => 'Siklus 25 menit kerja fokus tanpa gangguan, disusul 5 menit istirahat penuh. Setelah 4 siklus, ambil istirahat panjang 15-30 menit.',
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'amber',
                        'body' => 'Fokus bukanlah tentang seberapa keras kamu memaksakan diri, melainkan seberapa baik kamu mengelola ritme energimu.',
                        'source' => 'Francesco Cirillo, Penemu Pomodoro',
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'kilat',
                        'body' => "Kunci utama: selama 25 menit itu, tutup semua tab yang tidak relevan dan letakkan ponsel di luar jangkauan mata.\n\nSaat bel berbunyi, berdirilah dan gerakkan badan. Kamu akan terkejut betapa banyaknya hal yang bisa kamu selesaikan dalam satu hari!",
                    ],
                ],
                'quiz' => [
                    'question' => 'Berapa durasi kerja fokus standar dalam satu siklus Pomodoro?',
                    'options' => ['15 menit', '25 menit', '45 menit', '60 menit'],
                    'correct_index' => 1,
                    'explanation' => 'Siklus standar Pomodoro adalah 25 menit fokus penuh diikuti istirahat singkat 5 menit.',
                ],
            ],
            [
                'title' => 'Aturan 1% Kaizen: Perubahan Kecil Berdampak Raksasa',
                'description' => 'Filosofi Jepang tentang perbaikan berkelanjutan yang mengubah hidup tanpa rasa kewalahan.',
                'tags' => ['Pengembangan Diri', 'Bisnis'],
                'user_index' => 1,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Banyak orang gagal membangun kebiasaan baru karena memasang target terlalu muluk di awal: lari 10 km, membaca 2 jam sehari, atau diet ekstrem.\n\nOtak kita mendeteksi perubahan drastis sebagai ancaman. Akibatnya timbul rasa malas dan prokrastinasi.",
                    ],
                    [
                        'type' => 'image',
                        'bg_color' => 'paper',
                        'media_path' => 'illustrations/kaizen-step.svg',
                        'body' => 'Jika kamu meningkat 1% setiap hari selama satu tahun, kamu akan menjadi 37 kali lebih baik di akhir tahun!',
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'amber',
                        'body' => 'Perjalanan seribu mil selalu dimulai dari satu langkah kecil.',
                        'source' => 'Lao Tzu',
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'teal',
                        'body' => "Terapkan prinsip 'terlalu kecil untuk gagal': baca 1 halaman buku, push-up 2 kali, atau minum 1 gelas air putih tambahan.\n\nKonsistensi mengalahkan intensitas setiap saat.",
                    ],
                ],
                'quiz' => [
                    'question' => 'Secara matematis, jika meningkat 1% setiap hari selama setahun (1.01^365), kamu menjadi sekitar berapa kali lebih baik?',
                    'options' => ['3,7 kali', '10 kali', '37,8 kali', '100 kali'],
                    'correct_index' => 2,
                    'explanation' => '1.01 dipangkatkan 365 menghasilkan sekitar 37.78, efek bunga majemuk dalam kebiasaan hidup!',
                ],
            ],
            [
                'title' => 'Mengapa Langit Berwarna Biru? Fisika Hamburan Rayleigh',
                'description' => 'Penjelasan ilmiah sederhana tentang rahasia warna langit siang dan jingga senja.',
                'tags' => ['Sains'],
                'user_index' => 2,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Cahaya matahari yang tampak putih sebenarnya adalah gabungan semua warna pelangi, dari merah (gelombang panjang) hingga ungu dan biru (gelombang pendek).\n\nSaat memasuki atmosfer bumi, cahaya ini menabrak partikel gas dan debu.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'kilat',
                        'body' => "Lord Rayleigh membuktikan bahwa gelombang pendek (seperti biru dan ungu) dihamburkan ke segala arah jauh lebih kuat daripada gelombang panjang.\n\nKarena mata manusia jauh lebih peka terhadap warna biru dibanding ungu, langit tampak biru cerah di mata kita!",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'teal',
                        'body' => 'Saat senja, cahaya matahari menempuh atmosfer lebih tebal sehingga warna biru habis terhambur, menyisakan warna merah dan jingga.',
                        'source' => 'Fisika Atmosfer',
                    ],
                ],
                'quiz' => [
                    'question' => 'Mengapa mata kita melihat langit berwarna biru dan bukan ungu, padahal ungu dihamburkan lebih kuat?',
                    'options' => [
                        'Mata manusia lebih sensitif terhadap warna biru',
                        'Matahari tidak memancarkan cahaya ungu',
                        'Warna ungu diserap total oleh awan',
                        'Cahaya ungu bergerak lebih lambat',
                    ],
                    'correct_index' => 0,
                    'explanation' => 'Reseptor sel kerucut di retina mata manusia memiliki sensitivitas puncak yang jauh lebih tinggi pada spektrum biru dibanding ungu.',
                ],
            ],
            [
                'title' => 'Misteri Kapal Hantu Mary Celeste di Samudra Atlantik',
                'description' => 'Salah satu misteri maritim terbesar sepanjang sejarah yang belum terpecahkan hingga kini.',
                'tags' => ['Sejarah'],
                'user_index' => 3,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Pada 4 Desember 1872, kapal Mary Celeste ditemukan terombang-ambing di dekat Kepulauan Azores.\n\nKondisi kapal masih utuh dan layak berlayar, muatan alkohol industri tetap aman, dan bekal makanan cukup untuk enam bulan ke depan.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'amber',
                        'body' => "Namun, tidak ada satu jiwa pun di atasnya. Kapten Benjamin Briggs, istri, putrinya yang berusia dua tahun, serta tujuh anak buah kapal lenyap tanpa jejak.\n\nSatu-satunya sekoci penyelamat hilang, tetapi tidak ditemukan tanda-tanda pertempuran atau badai dahsyat.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'teal',
                        'body' => 'Teori paling masuk akal menduga ada kebocoran uap alkohol yang memicu kepanikan kapten untuk sementara mengungsi ke sekoci, sebelum tali putus terbawa arus laut.',
                        'source' => 'Arsip Maritim Britania',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Psikologi Warna: Bagaimana Merah dan Biru Mempengaruhi Otakmu',
                'description' => 'Bagaimana warna lingkungan dan produk secara bawah sadar mengarahkan emosi dan keputusan.',
                'tags' => ['Psikologi', 'Bisnis'],
                'user_index' => 4,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Pernahkah kamu menyadari bahwa restoran cepat saji hampir selalu memakai warna merah dan kuning, sedangkan bank dan media sosial didominasi warna biru?\n\nIni bukan kebetulan artistik, melainkan penerapan psikologi warna yang presisi.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'kilat',
                        'body' => "Warna merah memicu detak jantung lebih cepat, menciptakan rasa urgensi dan stimulasi nafsu makan.\n\nSebaliknya, biru merangsang pelepasan zat kimia yang menenangkan dan membangun rasa percaya serta stabilitas.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'amber',
                        'body' => 'Warna adalah bahasa visual tercepat yang berbicara langsung ke bagian emosional otak tanpa perlu kata-kata.',
                        'source' => 'Riset Perilaku Konsumen',
                    ],
                ],
                'quiz' => [
                    'question' => 'Mengapa lembaga keuangan dan institusi perbankan sangat sering memakai warna biru dalam identitas mereka?',
                    'options' => [
                        'Memicu nafsu belanja instan',
                        'Membangun rasa percaya, aman, dan stabilitas',
                        'Menandakan harga produk yang murah',
                        'Membuat orang merasa lapar',
                    ],
                    'correct_index' => 1,
                    'explanation' => 'Secara psikologis, warna biru diasosiasikan dengan stabilitas, ketenangan, dan rasa aman yang krusial untuk sektor keuangan.',
                ],
            ],
            [
                'title' => 'Komik Singkat: Derita Buku Tebal vs Macha',
                'description' => 'Format comic strip: dua panel ilustrasi tentang perjalanan seorang pembaca modern.',
                'tags' => ['Pengembangan Diri'],
                'user_index' => 0,
                'slides' => [
                    [
                        'type' => 'image',
                        'bg_color' => 'paper',
                        'media_path' => 'illustrations/comic-reading-1.svg',
                        'body' => 'Panel 1: Rencana ambisius membaca buku 600 halaman, berakhir tertidur di halaman lima...',
                    ],
                    [
                        'type' => 'image',
                        'bg_color' => 'kilat',
                        'media_path' => 'illustrations/comic-reading-2.svg',
                        'body' => 'Panel 2: Membaca 3 menit di Macha sambil mengumpulkan EXP dan menjaga streak harian!',
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'amber',
                        'body' => "Pengetahuan tidak harus selalu ditelan sekaligus seperti maraton.\n\nMembaca 3 menit setiap hari jauh lebih bernilai daripada berniat membaca 5 jam tapi tak pernah dimulai.",
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Efek Dunning-Kruger: Saat Orang Kurang Paham Merasa Paling Ahli',
                'description' => 'Bias kognitif di mana seseorang dengan kemampuan rendah melebih-lebihkan keterampilannya.',
                'tags' => ['Psikologi'],
                'user_index' => 5,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Pernahkah kamu bertemu seseorang yang baru membaca satu artikel berita, tapi berbicara seolah-olah ia seorang profesor ahli dalam topik tersebut?\n\nFenomena ini dalam psikologi disebut Efek Dunning-Kruger.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'teal',
                        'body' => "David Dunning dan Justin Kruger menemukan bahwa orang dengan pengetahuan minim kekurangan metakognisi — kemampuan untuk menyadari ketidaktahuannya sendiri.\n\nSebaliknya, para pakar sejati sering meremehkan keahliannya karena menyadari betapa luas dan kompleksnya bidang tersebut.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'amber',
                        'body' => 'Musuh terbesar pengetahuan bukanlah ketidaktahuan, melainkan ilusi bahwa kita sudah tahu.',
                        'source' => 'Daniel J. Boorstin',
                    ],
                ],
                'quiz' => [
                    'question' => 'Apa yang biasanya terjadi pada rasa percaya diri seseorang saat pengetahuannya tentang suatu bidang mulai bertambah dalam?',
                    'options' => [
                        'Percaya diri langsung naik tanpa batas',
                        'Percaya diri sempat menurun karena menyadari kompleksitas topik',
                        'Tidak ada perubahan sama sekali',
                        'Orang tersebut langsung berhenti belajar',
                    ],
                    'correct_index' => 1,
                    'explanation' => 'Dalam kurva Dunning-Kruger, setelah fase puncak kepedean (Mount Stupid), orang akan melewati "Lembah Keputusasaan" saat menyadari betapa banyaknya hal yang belum ia pahami.',
                ],
            ],
            [
                'title' => 'Deep Work: Seni Menghasilkan Karya Hebat di Era Distraksi',
                'description' => 'Panduan bekerja dengan fokus tanpa gangguan untuk menciptakan nilai maksimal.',
                'tags' => ['Pengembangan Diri', 'Teknologi'],
                'user_index' => 1,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Profesor Cal Newport membagi aktivitas kerja menjadi dua:\n\n1. Shallow Work: tugas administratif seperti membalas chat santai atau merapikan folder.\n2. Deep Work: kemampuan fokus tanpa distraksi pada tugas yang menuntut kognitif tinggi.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'teal',
                        'body' => 'Kemampuan melakukan Deep Work semakin langka pada saat yang sama nilainya menjadi semakin mahal dalam ekonomi modern.',
                        'source' => 'Cal Newport, Penulis Deep Work',
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'kilat',
                        'body' => 'Untuk memulai: jadwalkan blok waktu 90 menit tanpa notifikasi sama sekali. Lindungi waktu ini seperti janji temu penting dengan dokter spesialis.',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Bagaimana Internet Bawah Laut Menghubungkan Benua',
                'description' => 'Fakta menakjubkan tentang 99% lalu lintas internet global yang mengalir di dasar samudra.',
                'tags' => ['Teknologi'],
                'user_index' => 2,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Banyak orang mengira internet antar-negara dihubungkan oleh satelit di luar angkasa.\n\nKenyataannya, lebih dari 99% data internet lintas benua ditransmisikan melalui kabel serat optik yang terbentang di dasar samudra!",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'teal',
                        'body' => "Kabel ini hanya berdiameter sekitar pipa selang taman (sekitar 2-3 cm) di laut dalam, dilindungi lapisan baja dan polietilena.\n\nDi dalamnya terdapat serat kaca setipis helai rambut manusia yang membawa puluhan terabit data per detik dengan kecepatan mendekati kecepatan cahaya.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'amber',
                        'body' => 'Ada lebih dari 1,3 juta kilometer kabel kapal selam aktif yang melintasi samudra di seluruh bumi.',
                        'source' => 'TeleGeography Data Report',
                    ],
                ],
                'quiz' => [
                    'question' => 'Berapa persen lalu lintas data internasional yang ditransmisikan melalui kabel bawah laut?',
                    'options' => ['Sekitar 25%', 'Sekitar 50%', 'Sekitar 75%', 'Lebih dari 99%'],
                    'correct_index' => 3,
                    'explanation' => 'Satelit hanya menyumbang kurang dari 1% karena kabel serat optik jauh lebih cepat, murah, dan berkapasitas masif.',
                ],
            ],
            [
                'title' => 'Mengenal Dana Darurat: Benteng Pertama Keuangan Pribadi',
                'description' => 'Fondasi keamanan finansial yang melindungi kamu dari risiko tak terduga.',
                'tags' => ['Keuangan'],
                'user_index' => 3,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Sebelum memikirkan investasi saham, reksadana, atau kripto, ada satu benteng yang wajib berdiri tegak: Dana Darurat.\n\nDana darurat adalah uang tunai yang disisihkan khusus untuk keadaan mendesak: sakit tiba-tiba, PHK, atau kendaraan rusak.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'kilat',
                        'body' => "Aturan umum besaran dana darurat:\n- Lajang: 3 hingga 6 kali pengeluaran bulanan rutin.\n- Menikah: 6 hingga 9 kali pengeluaran bulanan.\n- Memiliki anak / wirausaha: 9 hingga 12 kali pengeluaran bulanan.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'amber',
                        'body' => 'Tujuan dana darurat bukan untuk menghasilkan return tinggi, melainkan memberikan ketenangan pikiran saat hidup memberi kejutan pahit.',
                        'source' => 'Perencana Keuangan',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Paradoks Pilihan: Mengapa Terlalu Banyak Opsi Membuat Kita Cemas',
                'description' => 'Saat kebebasan memilih justru menjebak kita dalam penyesalan dan ketidakpuasan.',
                'tags' => ['Psikologi'],
                'user_index' => 4,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Masyarakat modern meyakini bahwa semakin banyak pilihan, semakin bebas dan bahagia kita.\n\nNamun psikolog Barry Schwartz membuktikan hal sebaliknya dalam buku 'The Paradox of Choice'.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'teal',
                        'body' => "Saat dihadapkan pada 24 jenis selai di supermarket, pembeli cenderung bingung dan batal membeli.\n\nSaat pilihan dibatasi hanya 6 jenis, penjualan melonjak 10 kali lipat! Terlalu banyak opsi memicu kelumpuhan analisis dan rasa takut salah pilih.",
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Sejarah Kopi: Dari Kambing Menari di Etiopia ke Kedai Modern',
                'description' => 'Kisah penemuan biji kopi oleh seorang penggembala kambing berabad-abad lalu.',
                'tags' => ['Sejarah'],
                'user_index' => 0,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Legenda menyebutkan bahwa pada abad ke-9, seorang penggembala Etiopia bernama Kaldi kebingungan melihat kambing-kambingnya melonjak girang dan tidak tidur di malam hari.\n\nRupanya, kawanan kambing itu baru saja memakan buah beri merah dari semak liar.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'amber',
                        'body' => "Kaldi membawa buah tersebut ke biara setempat. Para biarawan merebus bijinya dan menemukan bahwa ramuan itu membuat mereka tetap terjaga selama doa malam yang panjang.\n\nDari Etiopia, kopi menyebar ke Yaman, Konstantinopel, hingga akhirnya merambah seluruh penjuru dunia.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'teal',
                        'body' => 'Kopi adalah bahan bakar percakapan cerdas para pemikir di kedai-kedai Eropa zaman Pencerahan.',
                        'source' => 'Sejarah Kuliner Dunia',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Mikrobioma Usus: Otak Kedua yang Mengatur Mood Kamu',
                'description' => 'Hubungan mengejutkan antara bakteri di perutmu dengan tingkat stres dan kecemasan.',
                'tags' => ['Kesehatan', 'Sains'],
                'user_index' => 1,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Pernahkah kamu merasakan 'kupu-kupu di perut' saat gugup? Itu bukan sekadar kiasan puisi.\n\nUsus manusia memiliki lebih dari 100 juta neuron dan sering dijuluki oleh para ilmuwan sebagai 'Otak Kedua' (Enteric Nervous System).",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'kilat',
                        'body' => "Fakta mengejutkan: sekitar 90% serotonin tubuh (neurotransmiter pengatur rasa bahagia dan tenang) diproduksi di dalam saluran pencernaan oleh bakteri usus kita!\n\nMakanan tinggi serat dan fermentasi secara langsung mendukung mikroba baik pembentuk mood positif.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'teal',
                        'body' => 'Kesehatan mental yang kuat berakar dari piring makanan yang bergizi seimbang.',
                        'source' => 'Neurologi & Gastroenterologi',
                    ],
                ],
                'quiz' => [
                    'question' => 'Di manakah sebagian besar (sekitar 90%) neurotransmiter serotonin pengatur suasana hati diproduksi dalam tubuh?',
                    'options' => [
                        'Di dalam organ saluran pencernaan (usus)',
                        'Di kelenjar tiroid leher',
                        'Di dalam sumsum tulang belakang',
                        'Hanya diproduksi di lobus frontal otak',
                    ],
                    'correct_index' => 0,
                    'explanation' => 'Sekitar 90% serotonin disintesis di sel enterokromafin pada saluran cerna dengan bantuan mikrobioma usus.',
                ],
            ],
            [
                'title' => 'Compound Interest: Keajaiban Dunia Kedelapan Menurut Einstein',
                'description' => 'Bagaimana bunga majemuk melipatgandakan aset secara eksponensial seiring waktu.',
                'tags' => ['Keuangan'],
                'user_index' => 2,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Bunga majemuk (compound interest) adalah konsep di mana bunga yang kamu dapatkan menghasilkan bunganya sendiri.\n\nDalam jangka pendek, pertumbuhannya tampak lambat dan membosankan. Namun setelah melewati tahun ke-10 atau ke-15, efek kurva eksponensial akan meledak.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'amber',
                        'body' => 'Bunga majemuk adalah keajaiban dunia kedelapan. Siapa yang memahaminya akan memilikinya; siapa yang tidak akan membayarnya.',
                        'source' => 'Albert Einstein',
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'kilat',
                        'body' => 'Waktu adalah faktor terpenting. Orang yang mulai berinvestasi Rp500 ribu/bulan di usia 20 tahun akan memiliki aset jauh lebih besar saat pensiun dibanding orang yang menabung Rp2 juta/bulan tapi baru mulai di usia 40 tahun.',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Filosofi Stoikisme: Mengendalikan Apa yang Bisa Dikendalikan',
                'description' => 'Prinsip ketenangan batin dari filsuf Yunani dan Romawi kuno untuk mengatasi stres modern.',
                'tags' => ['Pengembangan Diri', 'Sastra'],
                'user_index' => 3,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Inti dari ajaran Stoikisme bertumpu pada 'Dikotomi Kendali' (Dichotomy of Control).\n\nAda hal-hal yang berada di bawah kendali kita: pikiran kita, tindakan kita, dan respons emosional kita. Dan ada hal-hal di luar kendali kita: cuaca, kemacetan, opini orang lain, serta hasil akhir.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'teal',
                        'body' => 'Bukan hal-hal di luar sana yang mengganggu kedamaian kita, melainkan persepsi dan penilaian kita sendiri tentang hal-hal tersebut.',
                        'source' => 'Epictetus, Filsuf Stoa',
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'amber',
                        'body' => 'Fokuskan 100% energimu pada hal yang bisa kamu pengaruhi hari ini, dan lepaskan kecemasan pada hal-hal yang tidak bisa kamu ubah.',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Asal Usul Bahasa Indonesia: Mengapa Bahasa Melayu yang Dipilih?',
                'description' => 'Keputusan visioner para pendiri bangsa pada Sumpah Pemuda 1928.',
                'tags' => ['Bahasa', 'Sejarah'],
                'user_index' => 4,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Pada masa kolonial, suku Jawa merupakan mayoritas penduduk Hindia Belanda. Namun pada kongres pemuda 1928, para pendiri bangsa justru sepakat memilih bahasa Melayu sebagai fondasi Bahasa Indonesia.\n\nMengapa bukan bahasa Jawa atau Belanda?",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'kilat',
                        'body' => "Bahasa Melayu telah berabad-abad menjadi 'lingua franca' (bahasa perantara perdagangan antar-pulau di nusantara).\n\nSelain itu, bahasa Melayu tidak memiliki tingkatan kasta tutur kata yang rumit, menjadikannya sangat demokratis dan mudah dipelajari oleh seluruh suku bangsa secara setara.",
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Hutan Hujan Amazon: Paru-Paru Dunia yang Sedang Berjuang',
                'description' => 'Keanekaragaman hayati raksasa dan peran krusial Amazon dalam iklim planet bumi.',
                'tags' => ['Lingkungan', 'Sains'],
                'user_index' => 5,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Cekungan Amazon membentang seluas 6,7 juta kilometer persegi, melintasi sembilan negara di Amerika Selatan.\n\nHutan ini menampung 10% dari seluruh spesies flora dan fauna yang dikenal di bumi serta menyimpan 150-200 miliar ton karbon.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'teal',
                        'body' => 'Pohon-pohon Amazon melepaskan 20 miliar ton air ke atmosfer setiap hari, menciptakan sungai terbang (flying rivers) yang mengatur hujan di belahan bumi selatan.',
                        'source' => 'National Geographic Science',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Prinsip Pareto 80/20: Maksimalkan Hasil dengan Energi Tepat',
                'description' => 'Cara mengidentifikasi 20% upaya kunci yang menghasilkan 80% dampak terbesar.',
                'tags' => ['Bisnis', 'Pengembangan Diri'],
                'user_index' => 0,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Pada akhir abad ke-19, ekonom Italia Vilfredo Pareto mengamati bahwa 80% tanah di Italia dimiliki oleh 20% penduduk.\n\nYang mengejutkan, pola 80/20 ini berulang di hampir semua aspek kehidupan modern.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'amber',
                        'body' => "Dalam bisnis: 80% keuntungan biasanya datang dari 20% pelanggan terbaik.\nDalam produktivitas pribadi: 80% hasil kerjamu dicapai dari 20% aktivitas paling penting.\n\nTanyakan pada dirimu tiap pagi: 'Tugas mana yang jika selesai akan membuat tugas lain terasa jauh lebih mudah?'",
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Mengapa Kita Menguap Saat Melihat Orang Lain Menguap?',
                'description' => 'Kaitan menguap menular dengan neuron cermin dan empati sosial manusia.',
                'tags' => ['Sains', 'Psikologi'],
                'user_index' => 1,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Melihat teman atau bahkan anjing peliharaan menguap sering kali membuat kita spontan ikut menguap dalam hitungan detik.\n\nBahkan hanya membaca kata 'menguap' di artikel ini bisa memicu refleks tersebut!",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'kilat',
                        'body' => "Para neurosaintis menemukan bahwa ini melibatkan 'neuron cermin' (mirror neurons) di otak.\n\nMenguap menular adalah tanda empati sosial dan ikatan kelompok yang telah berkembang sejak masa evolusi manusia purba untuk menyinkronkan kewaspadaan kelompok.",
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Gaya Hidup Minimalisme: Ruang Bersih, Pikiran Jernih',
                'description' => 'Bukan tentang membuang barang, tapi tentang membebaskan ruang untuk hal bermakna.',
                'tags' => ['Pengembangan Diri'],
                'user_index' => 2,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Banyak orang salah mengira minimalisme berarti rumah kosong serba putih tanpa perabot.\n\nMinimalisme sejatinya adalah keputusan sadar untuk hanya menyimpan barang yang benar-benar memberi nilai tambah atau kebahagiaan dalam hidupmu.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'amber',
                        'body' => 'Bukan orang yang memiliki sedikit barang yang miskin, melainkan orang yang selalu menginginkan lebih.',
                        'source' => 'Seneca',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Jejak Karbon Pribadi: Langkah Nyata Kurangi Emisi Harian',
                'description' => 'Aksi kecil berdampak besar untuk melindungi bumi dari perubahan iklim.',
                'tags' => ['Lingkungan'],
                'user_index' => 3,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Jejak karbon adalah total emisi gas rumah kaca yang dihasilkan oleh aktivitas harian kita: transportasi, listrik, konsumsi makanan, dan sampah.\n\nDi Indonesia, rata-rata jejak karbon pribadi sekitar 2-3 ton CO2 per tahun.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'teal',
                        'body' => "Tiga langkah termudah untuk mengurangi jejak karbon hari ini:\n1. Habiskan makanan di piringmu (sampah makanan menyumbang metana masif).\n2. Gunakan transportasi publik atau jalan kaki untuk jarak dekat.\n3. Bawa botol minum dan kantong belanja guna ulang.",
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Seni Mendengarkan Aktif: Kunci Hubungan Sosial yang Sehat',
                'description' => 'Perbedaan besar antara sekadar mendengar suara dan benar-benar memahami lawan bicara.',
                'tags' => ['Pengembangan Diri', 'Psikologi'],
                'user_index' => 4,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Sebagian besar orang tidak mendengarkan dengan niat memahami, melainkan mendengarkan sambil menyiapkan jawaban apa yang akan mereka katakan berikutnya.\n\nIni adalah jebakan komunikasi paling umum dalam persahabatan maupun dunia kerja.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'kilat',
                        'body' => 'Dua telinga dan satu mulut diberikan kepada manusia agar kita mendengarkan dua kali lebih banyak daripada berbicara.',
                        'source' => 'Zeno dari Citium',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => "Model Mental 'First Principles': Cara Orang Hebat Berpikir",
                'description' => 'Membedah masalah hingga ke kebenaran paling mendasar untuk inovasi tanpa batas.',
                'tags' => ['Bisnis', 'Teknologi'],
                'user_index' => 0,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Berpikir dengan First Principles (prinsip pertama) adalah seni membongkar suatu masalah hingga ke elemen paling dasar yang tidak dapat disangkal lagi kebenarannya.\n\nLalu, kamu menyusun solusi baru dari bawah ke atas.",
                    ],
                    [
                        'type' => 'quote',
                        'bg_color' => 'teal',
                        'body' => 'Saat orang lain berkata baterai roket terlalu mahal karena harganya ratusan ribu dolar, SpaceX membedah harga bahan mentah logamnya yang ternyata hanya 2% dari harga jualnya.',
                        'source' => 'Kajian Inovasi Teknologi',
                    ],
                ],
                'quiz' => null,
            ],
            [
                'title' => 'Bahasa Tubuh dalam Negosiasi: Pesan Tanpa Kata',
                'description' => 'Bagaimana postur, kontak mata, dan intonasi mempengaruhi hasil percakapan bisnismu.',
                'tags' => ['Bisnis', 'Psikologi'],
                'user_index' => 1,
                'slides' => [
                    [
                        'type' => 'text',
                        'bg_color' => 'paper',
                        'body' => "Dalam riset psikologi Albert Mehrabian, efektivitas komunikasi tatap muka sangat dipengaruhi oleh bahasa tubuh (55%) dan nada suara (38%), sementara kata-kata verbal hanya menyumbang 7%.\n\nPostur terbuka dan kontak mata mantap menunjukkan keyakinan tanpa arogansi.",
                    ],
                    [
                        'type' => 'text',
                        'bg_color' => 'amber',
                        'body' => "Tips praktis:\n- Hindari menyilangkan tangan di dada saat mendengarkan ide orang lain.\n- Condongkan badan sedikit ke depan untuk menunjukkan minat tulus.\n- Gunakan jeda hening sejenak sebelum merespons agar terkesan matang dan tenang.",
                    ],
                ],
                'quiz' => null,
            ],
        ];

        foreach ($readingsData as $index => $item) {
            $user = $users[$item['user_index'] % $users->count()];
            $slug = Str::slug($item['title']).'-'.Str::lower(Str::random(6));

            // Calculate reading minutes based on word count (approx 200 wpm) + 15s per image/audio slide
            $totalWords = 0;
            $mediaSlidesCount = 0;
            foreach ($item['slides'] as $s) {
                if (isset($s['body'])) {
                    $totalWords += str_word_count(strip_tags($s['body']));
                }
                if (in_array($s['type'], ['image', 'audio'])) {
                    $mediaSlidesCount++;
                }
            }
            $estimatedMinutes = max(1, min(5, (int) ceil(($totalWords / 200) + ($mediaSlidesCount * 0.25))));

            $publishedAt = Carbon::now('Asia/Jakarta')->subDays(rand(0, 10))->subHours(rand(1, 23));

            $reading = Reading::create([
                'user_id' => $user->id,
                'title' => $item['title'],
                'slug' => $slug,
                'description' => $item['description'] ?? null,
                'status' => 'published',
                'reading_minutes' => $estimatedMinutes,
                'likes_count' => rand(5, 45),
                'comments_count' => rand(2, 12),
                'completions_count' => rand(15, 80),
                'views_count' => rand(90, 300),
                'published_at' => $publishedAt,
            ]);

            // Attach tags
            if (isset($item['tags'])) {
                $tagIds = [];
                foreach ($item['tags'] as $tagName) {
                    if (isset($tags[$tagName])) {
                        $tagIds[] = $tags[$tagName]->id;
                    }
                }
                $reading->tags()->sync($tagIds);
            }

            // Create slides
            foreach ($item['slides'] as $pos => $slideData) {
                ReadingSlide::create([
                    'reading_id' => $reading->id,
                    'position' => $pos + 1,
                    'type' => $slideData['type'],
                    'body' => $slideData['body'] ?? null,
                    'source' => $slideData['source'] ?? null,
                    'media_path' => $slideData['media_path'] ?? null,
                    'bg_color' => $slideData['bg_color'] ?? 'paper',
                ]);
            }

            // Create quiz if present
            if (isset($item['quiz']) && $item['quiz'] !== null) {
                ReadingQuiz::create([
                    'reading_id' => $reading->id,
                    'question' => $item['quiz']['question'],
                    'options' => $item['quiz']['options'],
                    'correct_index' => $item['quiz']['correct_index'],
                    'explanation' => $item['quiz']['explanation'] ?? null,
                ]);
            }
        }
    }

    /**
     * Ensure Macha styled SVG illustration images exist in storage/app/public/illustrations.
     */
    private function ensureIllustrationsExist(): void
    {
        $dir = storage_path('app/public/illustrations');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // 1. Pomodoro Focus Diagram
        $pomodoroSvg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" fill="none">
    <rect width="400" height="300" rx="20" fill="#102A2A"/>
    <!-- Circular track -->
    <circle cx="200" cy="140" r="80" stroke="#1E4040" stroke-width="16" />
    <circle cx="200" cy="140" r="80" stroke="#19A974" stroke-width="16" stroke-dasharray="415 502" stroke-linecap="round" />
    <text x="200" y="135" font-family="system-ui" font-size="32" font-weight="800" fill="#F7F5EF" text-anchor="middle">25:00</text>
    <text x="200" y="165" font-family="system-ui" font-size="14" font-weight="700" fill="#F2C14E" text-anchor="middle">FOKUS PENUH</text>
    <!-- 5 min break badge -->
    <rect x="130" y="240" width="140" height="36" rx="12" fill="#19A974"/>
    <text x="200" y="263" font-family="system-ui" font-size="13" font-weight="800" fill="#102A2A" text-anchor="middle">+5 Mnt Rehat</text>
</svg>
SVG;
        File::put($dir.'/pomodoro-focus.svg', $pomodoroSvg);

        // 2. Kaizen Step Graphic
        $kaizenSvg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" fill="none">
    <rect width="400" height="300" rx="20" fill="#F7F5EF"/>
    <!-- Staircase blocks -->
    <rect x="50" y="200" width="60" height="60" rx="10" fill="#ECE8DC" stroke="#102A2A" stroke-width="3"/>
    <text x="80" y="235" font-family="system-ui" font-size="12" font-weight="800" fill="#102A2A" text-anchor="middle">Hari 1</text>

    <rect x="130" y="150" width="60" height="110" rx="10" fill="#F2C14E" stroke="#102A2A" stroke-width="3"/>
    <text x="160" y="210" font-family="system-ui" font-size="12" font-weight="800" fill="#102A2A" text-anchor="middle">+1%</text>

    <rect x="210" y="100" width="60" height="160" rx="10" fill="#19A974" stroke="#102A2A" stroke-width="3"/>
    <text x="240" y="180" font-family="system-ui" font-size="12" font-weight="800" fill="#102A2A" text-anchor="middle">+1%</text>

    <rect x="290" y="50" width="60" height="210" rx="10" fill="#102A2A" stroke="#102A2A" stroke-width="3"/>
    <text x="320" y="150" font-family="system-ui" font-size="14" font-weight="800" fill="#F7F5EF" text-anchor="middle">37x</text>

    <text x="200" y="35" font-family="system-ui" font-size="16" font-weight="800" fill="#102A2A" text-anchor="middle">Konsistensi Mengalahkan Beban</text>
</svg>
SVG;
        File::put($dir.'/kaizen-step.svg', $kaizenSvg);

        // 3. Comic Panel 1
        $comic1Svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" fill="none">
    <rect width="400" height="300" rx="20" fill="#F7F5EF" stroke="#102A2A" stroke-width="4"/>
    <!-- Giant book -->
    <rect x="120" y="70" width="160" height="180" rx="12" fill="#1E4040"/>
    <rect x="130" y="80" width="140" height="160" rx="8" fill="#ECE8DC"/>
    <text x="200" y="140" font-family="system-ui" font-size="16" font-weight="800" fill="#102A2A" text-anchor="middle">BUKU TEBAL</text>
    <text x="200" y="165" font-family="system-ui" font-size="13" font-weight="700" fill="#D9534F" text-anchor="middle">600 Halaman</text>
    <!-- Zzz sleep -->
    <text x="300" y="60" font-family="system-ui" font-size="28" font-weight="800" fill="#D4A238">Zzz...</text>
</svg>
SVG;
        File::put($dir.'/comic-reading-1.svg', $comic1Svg);

        // 4. Comic Panel 2
        $comic2Svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" fill="none">
    <rect width="400" height="300" rx="20" fill="#19A974" stroke="#118A5D" stroke-width="4"/>
    <!-- Phone frame -->
    <rect x="140" y="40" width="120" height="220" rx="20" fill="#102A2A"/>
    <rect x="148" y="55" width="104" height="190" rx="12" fill="#F7F5EF"/>
    <!-- Micro card inside phone -->
    <rect x="156" y="70" width="88" height="100" rx="8" fill="#F2C14E"/>
    <text x="200" y="115" font-family="system-ui" font-size="12" font-weight="800" fill="#102A2A" text-anchor="middle">Macha</text>
    <text x="200" y="135" font-family="system-ui" font-size="10" font-weight="700" fill="#102A2A" text-anchor="middle">+10 EXP</text>
    <text x="200" y="200" font-family="system-ui" font-size="12" font-weight="800" fill="#19A974" text-anchor="middle">Selesai 2 Mnt!</text>
</svg>
SVG;
        File::put($dir.'/comic-reading-2.svg', $comic2Svg);
    }
}
