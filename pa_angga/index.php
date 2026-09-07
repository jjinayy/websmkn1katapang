<?php
/**
 * index.php
 * -----------------------------------------------------------
 * Halaman utama website SMKN 1 Katapang. Desain, isi, dan alur
 * SAMA PERSIS dengan file referensi (index__4_.html) — bedanya,
 * bagian "Katapang Store" sekarang benar-benar tersambung ke
 * database MySQL (config.php) untuk login siswa & simpan pesanan.
 * -----------------------------------------------------------
 */
require_once __DIR__ . '/config.php';

// Status login diambil dari SESSION PHP (bukan cuma variabel JS),
// jadi kalau halaman di-refresh, status login siswa tidak hilang.
$sudahLogin   = isLoggedIn();
$namaSiswa    = $_SESSION['siswa_nama'] ?? '';
$kelasSiswa   = $_SESSION['siswa_kelas'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMKN 1 KATAPANG - Sekolah Manusia Unggul (MAUNG)</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- AOS Animation Library CDN -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandBlue: '#2563eb',
                        softBlue: '#eff6ff',
                        darkBlue: '#1e3a8a',
                        brandYellow: '#eab308',
                        softYellow: '#fef9c3',
                        accentYellow: '#fde047'
                    },
                    fontFamily: {
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                        body: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <style>
        .fade-detail-enter {
            opacity: 0;
            transform: translateY(20px) scale(0.98);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fade-detail-show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-card-pop {
            animation: fadeInUp 0.4s ease forwards;
        }

        html {
            scroll-behavior: smooth;
            font-family: 'Inter', sans-serif;
        }

        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @keyframes glowText {
            0%, 100% {
                text-shadow: 0 0 12px rgba(234, 179, 8, 0.7), 0 0 24px rgba(234, 179, 8, 0.4);
            }
            50% {
                text-shadow: 0 0 20px rgba(37, 99, 235, 0.8), 0 0 35px rgba(37, 99, 235, 0.6);
            }
        }

        @keyframes slowZoom {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }

        .hero-image-animate {
            animation: slowZoom 20s infinite ease-in-out alternate;
        }

        .glow-title {
            animation: glowText 3.5s infinite ease-in-out;
        }

        /* Glassmorphism Card Style ala Katapang Store */
        .glass-card-store {
            background-color: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card-store:hover {
            border-color: rgba(234, 179, 8, 0.6);
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(234, 179, 8, 0.15);
        }

        .glass-card-blue:hover {
            border-color: rgba(37, 99, 235, 0.6);
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.15);
        }
    </style>
</head>
<body class="bg-slate-950 text-white">

    <!-- NAVBAR -->
    <nav class="sticky top-0 z-50 bg-slate-950/90 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-3">
                    <img src="logo.png" alt="Logo SMKN 1 Katapang" class="w-12 h-12 object-contain drop-shadow-sm transition hover:scale-105">
                    <div>
                        <span class="font-heading font-extrabold text-xl text-white block leading-none tracking-tight">SMKN 1 KATAPANG</span>
                        <span class="text-xs font-semibold text-brandYellow tracking-wider uppercase">Sekolah Manusia Unggul (MAUNG)</span>
                    </div>
                </div>
                
                <div class="hidden lg:flex items-center space-x-6 font-semibold text-sm text-slate-300">
                    <a href="#hero" class="hover:text-brandYellow transition">Beranda</a>
                    <a href="#maung" class="hover:text-brandYellow transition">Program MAUNG</a>
                    <a href="#jurusan" class="hover:text-brandYellow transition">Jurusan</a>
                    <a href="#fasilitas" class="hover:text-brandYellow transition">Fasilitas</a>
                    <a href="#eskul" class="hover:text-brandYellow transition">Ekstrakurikuler</a>
                    <a href="#katapang-store" class="hover:text-brandYellow transition">Katapang Store</a>
                    <a href="#pengumuman" class="hover:text-brandYellow transition">Pengumuman</a>
                </div>

                <a href="#katapang-store" onclick="bukaKatalogLangsung()" class="hidden sm:inline-flex items-center gap-2 bg-brandYellow hover:bg-yellow-400 text-slate-950 px-5 py-2.5 rounded-xl font-heading font-bold text-sm shadow-[0_0_15px_rgba(234,179,8,0.4)] transition">
                    <i class="fa-solid fa-cart-shopping"></i> Katapang Store
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section id="hero" class="relative min-h-[90vh] flex items-center justify-center text-center px-4 py-20 overflow-hidden text-white border-b border-slate-800">
        <div class="absolute inset-0 z-0 overflow-hidden">
            <img src="smkn1katapang.jpg" alt="SMKN 1 Katapang Gate" class="w-full h-full object-cover hero-image-animate filter brightness-50">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-slate-950/60 backdrop-blur-[2px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto" data-aos="zoom-in" data-aos-duration="1000">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 text-yellow-300 rounded-full font-heading text-xs font-bold mb-6 shadow-xl tracking-wider uppercase">
                <span class="w-2 h-2 rounded-full bg-brandYellow animate-ping"></span> Official Website SMKN 1 Katapang
            </span>
            
            <h1 class="text-4xl sm:text-6xl font-heading font-extrabold leading-tight mb-6 tracking-tight drop-shadow-md">
                Mewujudkan Generasi <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandYellow via-amber-300 to-yellow-500 glow-title">Manusia Unggul (MAUNG)</span>
            </h1>
            
            <p class="text-slate-300 text-base sm:text-lg mb-10 max-w-2xl mx-auto font-normal leading-relaxed drop-shadow">
                Pusat keunggulan pendidikan vokasi terintegrasi yang mencetak lulusan berkarakter kuat, kompeten, dan siap bersaing di dunia industri.
            </p>
            
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#jurusan" class="bg-brandBlue hover:bg-blue-600 text-white px-8 py-3.5 rounded-2xl font-heading font-bold shadow-lg transition border border-blue-400/30 hover:scale-105 transform duration-200">
                    Lihat Program Keahlian
                </a>
                <button onclick="bukaKatalogLangsung()" class="bg-gradient-to-r from-brandYellow to-yellow-500 hover:from-yellow-400 hover:to-amber-500 text-slate-950 px-8 py-3.5 rounded-2xl font-heading font-extrabold shadow-[0_0_20px_rgba(234,179,8,0.4)] transition hover:scale-105 transform duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-store"></i> Buka Katalog Seragam
                </button>
            </div>
        </div>
    </section>

    <!-- PROGRAM MANUSIA UNGGUL (MAUNG) -->
    <section id="maung" class="py-24 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 relative z-10 overflow-hidden border-b border-slate-800">
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-brandBlue/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
                <span class="text-xs font-heading font-extrabold uppercase tracking-widest text-slate-950 bg-brandYellow px-4 py-1.5 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.3)]">Pilar Utama</span>
                <h2 class="text-3xl sm:text-5xl font-heading font-extrabold text-white mt-4">Kategori Sekolah Manusia Unggul (MAUNG)</h2>
                <p class="text-slate-400 mt-3 text-sm">Pembentukan karakter disiplin dan kompetensi keahlian tingkat tinggi.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div data-aos="fade-up" data-aos-delay="100" class="p-6 rounded-3xl glass-card-store glass-card-blue">
                    <div class="w-14 h-14 bg-brandBlue/20 text-brandBlue rounded-2xl flex items-center justify-center text-2xl mb-5 shadow-inner border border-brandBlue/30">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-white mb-2">Karakter Strong</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Membentuk kepribadian berintegritas, disiplin tinggi, dan berakhlak mulia.</p>
                </div>

                <div data-aos="fade-up" data-aos-delay="200" class="p-6 rounded-3xl glass-card-store">
                    <div class="w-14 h-14 bg-brandYellow/20 text-brandYellow rounded-2xl flex items-center justify-center text-2xl mb-5 shadow-inner border border-brandYellow/30">
                        <i class="fa-solid fa-gears"></i>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-white mb-2">Kompetensi Ahli</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Keahlian teknis presisi yang sesuai dengan kebutuhan industri modern.</p>
                </div>

                <div data-aos="fade-up" data-aos-delay="300" class="p-6 rounded-3xl glass-card-store glass-card-blue">
                    <div class="w-14 h-14 bg-brandBlue/20 text-brandBlue rounded-2xl flex items-center justify-center text-2xl mb-5 shadow-inner border border-brandBlue/30">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-white mb-2">Inovasi Kreatif</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Mendorong karya teknologi tepat guna yang solutif bagi masyarakat.</p>
                </div>

                <div data-aos="fade-up" data-aos-delay="400" class="p-6 rounded-3xl glass-card-store">
                    <div class="w-14 h-14 bg-brandYellow/20 text-brandYellow rounded-2xl flex items-center justify-center text-2xl mb-5 shadow-inner border border-brandYellow/30">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <h3 class="font-heading font-bold text-lg text-white mb-2">Jiwa Pemimpin</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Melatih ketahanan mental, kerjasama tim, dan kepemimpinan profesional.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PROGRAM PEMBIASAAN HARIAN -->
    <section id="pembiasaan" class="py-24 bg-slate-950 relative z-10 overflow-hidden border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
                <span class="text-xs font-heading font-extrabold uppercase tracking-widest text-slate-950 bg-brandYellow px-4 py-1.5 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.3)]">Budaya Sekolah</span>
                <h2 class="text-3xl sm:text-5xl font-heading font-extrabold text-white mt-4">Pembiasaan & Rutin Mingguan Siswa</h2>
                <p class="text-slate-400 mt-3 text-sm">Pembentukan karakter Manusia Unggul (MAUNG) yang dilaksanakan secara konsisten setiap hari sebelum KBM.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div data-aos="fade-up" data-aos-delay="100" class="glass-card-store p-5 rounded-3xl flex flex-col justify-between relative overflow-hidden group">
                    <div class="w-1.5 h-full bg-brandBlue absolute left-0 top-0"></div>
                    <div>
                        <div class="flex items-center justify-between mb-3 pl-2">
                            <span class="font-heading font-black text-xs text-brandBlue uppercase tracking-widest">Senin</span>
                            <span class="p-2 bg-brandBlue/20 text-brandBlue rounded-lg text-sm border border-brandBlue/30"><i class="fa-solid fa-flag"></i></span>
                        </div>
                        <h3 class="font-heading font-bold text-base text-white mb-2 pl-2">Upacara & Pembinaan</h3>
                        <p class="text-xs text-slate-400 leading-relaxed pl-2">Upacara bendera penanaman jiwa nasionalisme diselingi pembinaan wali kelas untuk evaluasi kedisiplinan.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-800 pl-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kedisiplinan & Karakter</span>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="200" class="glass-card-store p-5 rounded-3xl flex flex-col justify-between relative overflow-hidden group">
                    <div class="w-1.5 h-full bg-brandYellow absolute left-0 top-0"></div>
                    <div>
                        <div class="flex items-center justify-between mb-3 pl-2">
                            <span class="font-heading font-black text-xs text-brandYellow uppercase tracking-widest">Selasa</span>
                            <span class="p-2 bg-brandYellow/20 text-brandYellow rounded-lg text-sm border border-brandYellow/30"><i class="fa-solid fa-user-gear"></i></span>
                        </div>
                        <h3 class="font-heading font-bold text-base text-white mb-2 pl-2">Bimbingan Guru BK</h3>
                        <p class="text-xs text-slate-400 leading-relaxed pl-2">Sesi bimbingan konseling dan motivasi mengenai pengembangan diri, etika industri, serta karir siswa.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-800 pl-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Mental & Counseling</span>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="300" class="glass-card-store p-5 rounded-3xl flex flex-col justify-between relative overflow-hidden group">
                    <div class="w-1.5 h-full bg-brandBlue absolute left-0 top-0"></div>
                    <div>
                        <div class="flex items-center justify-between mb-3 pl-2">
                            <span class="font-heading font-black text-xs text-brandBlue uppercase tracking-widest">Rabu</span>
                            <span class="p-2 bg-brandBlue/20 text-brandBlue rounded-lg text-sm border border-brandBlue/30"><i class="fa-solid fa-microphone-lines"></i></span>
                        </div>
                        <h3 class="font-heading font-bold text-base text-white mb-2 pl-2">Literasi & Public Speaking</h3>
                        <p class="text-xs text-slate-400 leading-relaxed pl-2">Pembacaan wawasan literasi dilatih langsung dengan praktik public speaking menyampaikan ide di depan kelas.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-800 pl-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Komunikasi & Wawasan</span>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="400" class="glass-card-store p-5 rounded-3xl flex flex-col justify-between relative overflow-hidden group">
                    <div class="w-1.5 h-full bg-brandYellow absolute left-0 top-0"></div>
                    <div>
                        <div class="flex items-center justify-between mb-3 pl-2">
                            <span class="font-heading font-black text-xs text-brandYellow uppercase tracking-widest">Kamis</span>
                            <span class="p-2 bg-brandYellow/20 text-brandYellow rounded-lg text-sm border border-brandYellow/30"><i class="fa-solid fa-book-quran"></i></span>
                        </div>
                        <h3 class="font-heading font-bold text-base text-white mb-2 pl-2">Mengaji Bersama</h3>
                        <p class="text-xs text-slate-400 leading-relaxed pl-2">Pembacaan ayat suci Al-Qur'an dan pemantapan nilai spiritual untuk membentuk pribadi yang berakhlak mulia.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-800 pl-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Spiritual & Moral</span>
                    </div>
                </div>

                <div data-aos="fade-up" data-aos-delay="500" class="glass-card-store p-5 rounded-3xl flex flex-col justify-between relative overflow-hidden group sm:col-span-2 lg:col-span-1">
                    <div class="w-1.5 h-full bg-brandBlue absolute left-0 top-0"></div>
                    <div>
                        <div class="flex items-center justify-between mb-3 pl-2">
                            <span class="font-heading font-black text-xs text-brandBlue uppercase tracking-widest">Jumat</span>
                            <span class="p-2 bg-brandBlue/20 text-brandBlue rounded-lg text-sm border border-brandBlue/30"><i class="fa-solid fa-heart-pulse"></i></span>
                        </div>
                        <h3 class="font-heading font-bold text-base text-white mb-2 pl-2">Senam & Jumsih</h3>
                        <p class="text-xs text-slate-400 leading-relaxed pl-2">Olahraga senam kesegaran jasmani berselang-seling dengan Jumat Bersih (gotong royong kebersihan lingkungan).</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-800 pl-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kesehatan & Lingkungan</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- QUIZ PILIH JURUSAN INTERAKTIF -->
    <section id="quiz-jurusan" class="py-24 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 text-white relative overflow-hidden border-b border-slate-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div data-aos="fade-up">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-brandYellow text-slate-950 rounded-full font-heading text-xs font-extrabold mb-4 uppercase tracking-widest shadow-[0_0_15px_rgba(234,179,8,0.4)]">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Fitur Simulasi Interaktif
                </span>
                <h2 class="text-3xl sm:text-5xl font-heading font-extrabold mb-3 text-white">Bingung Pilih Jurusan?</h2>
                <p class="text-slate-400 text-sm max-w-xl mx-auto mb-8">Jawab 3 pertanyaan singkat ini untuk menemukan Konsentrasi Keahlian yang paling sesuai dengan minat & bakatmu di SMKN 1 Katapang!</p>
            </div>

            <div data-aos="zoom-in" class="glass-card-store p-6 sm:p-10 rounded-3xl text-left relative min-h-[360px] flex flex-col justify-between">
                <div id="quiz-progress-container" class="mb-6">
                    <div class="flex justify-between text-xs font-semibold text-slate-300 mb-2">
                        <span id="quiz-step-text">Pertanyaan 1 dari 3</span>
                        <span id="quiz-percent-text">33%</span>
                    </div>
                    <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden border border-white/10">
                        <div id="quiz-progress-bar" class="bg-brandYellow h-full w-1/3 transition-all duration-500"></div>
                    </div>
                </div>

                <div id="quiz-content" class="my-auto"></div>

                <div id="quiz-result" class="hidden text-center py-4">
                    <div class="w-16 h-16 bg-brandYellow text-slate-950 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 shadow-[0_0_20px_rgba(234,179,8,0.5)] animate-bounce">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <span class="text-xs font-heading font-bold uppercase tracking-widest text-brandYellow">Rekomendasi Terbaik Untukmu:</span>
                    <h3 id="result-title" class="text-2xl sm:text-4xl font-heading font-extrabold text-white mt-1 mb-3">Nama Jurusan</h3>
                    <p id="result-desc" class="text-xs sm:text-sm text-slate-300 max-w-lg mx-auto leading-relaxed mb-6">Deskripsi alasan jurusan ini cocok.</p>
                    
                    <div class="flex flex-wrap justify-center gap-3">
                        <button id="result-action-btn" class="bg-brandYellow hover:bg-yellow-400 text-slate-950 font-heading font-extrabold px-6 py-3 rounded-2xl transition text-sm shadow-[0_0_15px_rgba(234,179,8,0.4)]">
                            Lihat Detail Jurusan &rarr;
                        </button>
                        <button onclick="resetQuiz()" class="bg-white/10 hover:bg-white/20 text-white font-heading font-bold px-5 py-3 rounded-2xl border border-white/20 transition text-sm">
                            <i class="fa-solid fa-rotate-right"></i> Coba Lagi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JURUSAN / KONSENTRASI KEAHLIAN -->
    <section id="jurusan" class="py-24 bg-slate-950 relative overflow-hidden border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
                <span class="text-xs font-heading font-extrabold uppercase tracking-widest text-slate-950 bg-brandYellow px-4 py-1.5 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.3)]">Akademik</span>
                <h2 class="text-3xl sm:text-5xl font-heading font-extrabold text-white mt-4">Konsentrasi Keahlian / Jurusan</h2>
                <p class="text-slate-400 mt-3 text-sm">Klik salah satu jurusan di bawah untuk melihat detail & foto galeri praktikum.</p>
            </div>

            <!-- GRID DAFTAR JURUSAN -->
            <div id="jurusan-list" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- 1. Otomotif -->
                <div onclick="showDetail('to')" class="cursor-pointer glass-card-store glass-card-blue p-6 rounded-3xl flex items-start gap-4">
                    <div class="rounded-2xl shrink-0 w-14 h-14 overflow-hidden border border-white/10">
                        <img src="otomotif.jpeg" alt="Teknik Otomotif" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-lg text-white flex items-center gap-2">Teknik Otomotif <i class="fa-solid fa-arrow-right text-xs opacity-50"></i></h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Pemeliharaan & perbaikan kendaraan bermotor, sistem kelistrikan, serta teknologi otomotif terkini.</p>
                        <span class="inline-block mt-3 text-xs font-bold text-brandBlue">Lihat Detail & Foto &rarr;</span>
                    </div>
                </div>

                <!-- 2. Teknik Mesin -->
                <div onclick="showDetail('tm')" class="cursor-pointer glass-card-store p-6 rounded-3xl flex items-start gap-4">
                    <div class="rounded-2xl shrink-0 w-14 h-14 overflow-hidden border border-white/10">
                        <img src="mesin.png" alt="Teknik Mesin" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-lg text-white flex items-center gap-2">Teknik Mesin <i class="fa-solid fa-arrow-right text-xs opacity-50"></i></h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Pengoperasian mesin perkakas, perancangan manufaktur, permesinan CNC, dan fabrikasi logam.</p>
                        <span class="inline-block mt-3 text-xs font-bold text-brandYellow">Lihat Detail & Foto &rarr;</span>
                    </div>
                </div>

                <!-- 3. Teknik Tekstil -->
                <div onclick="showDetail('tt')" class="cursor-pointer glass-card-store glass-card-blue p-6 rounded-3xl flex items-start gap-4">
                    <div class="rounded-2xl shrink-0 w-14 h-14 overflow-hidden border border-white/10">
                        <img src="tekstil.png" alt="Teknik Tekstil" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-lg text-white flex items-center gap-2">Teknik Tekstil <i class="fa-solid fa-arrow-right text-xs opacity-50"></i></h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Proses pembuatan benang, pembuat kain, pencelupan, serta pengujian mutu produk tekstil industri.</p>
                        <span class="inline-block mt-3 text-xs font-bold text-brandBlue">Lihat Detail & Foto &rarr;</span>
                    </div>
                </div>

                <!-- 4. TJKT / TKJ -->
                <div onclick="showDetail('tkj')" class="cursor-pointer glass-card-store p-6 rounded-3xl flex items-start gap-4">
                    <div class="rounded-2xl shrink-0 w-14 h-14 overflow-hidden border border-white/10">
                        <img src="tjkt.png" alt="TJKT" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-lg text-white flex items-center gap-2">TJKT <i class="fa-solid fa-arrow-right text-xs opacity-50"></i></h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Administrasi server, instalasi jaringan komputer, mikrotik, fiber optic, dan keamanan siber.</p>
                        <span class="inline-block mt-3 text-xs font-bold text-brandYellow">Lihat Detail & Foto &rarr;</span>
                    </div>
                </div>

                <!-- 5. RPL -->
                <div onclick="showDetail('rpl')" class="cursor-pointer glass-card-store glass-card-blue p-6 rounded-3xl flex items-start gap-4">
                    <div class="rounded-2xl shrink-0 w-14 h-14 overflow-hidden border border-white/10">
                        <img src="rpl.jpeg" alt="RPL" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-lg text-white flex items-center gap-2">RPL <i class="fa-solid fa-arrow-right text-xs opacity-50"></i></h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Pemrograman web, aplikasi mobile, basis data, dan pengembangan sistem perangkat lunak modern.</p>
                        <span class="inline-block mt-3 text-xs font-bold text-brandBlue">Lihat Detail & Foto &rarr;</span>
                    </div>
                </div>

                <!-- 6. Broadcasting & Perfilman (BP) -->
                <div onclick="showDetail('bc')" class="cursor-pointer glass-card-store p-6 rounded-3xl flex items-start gap-4">
                    <div class="rounded-2xl shrink-0 w-14 h-14 overflow-hidden border border-white/10">
                        <img src="bp.png" alt="Broadcasting & Perfilman" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-lg text-white flex items-center gap-2">Broadcasting & Perfilman <i class="fa-solid fa-arrow-right text-xs opacity-50"></i></h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Produksi program TV/video, teknik kamera, tata suara, editing audio-visual, dan penyiaran.</p>
                        <span class="inline-block mt-3 text-xs font-bold text-brandYellow">Lihat Detail & Foto &rarr;</span>
                    </div>
                </div>

                <!-- 7. Teknik Elektronika -->
                <div onclick="showDetail('tek')" class="cursor-pointer glass-card-store glass-card-blue p-6 rounded-3xl flex items-start gap-4 sm:col-span-2 lg:col-span-1">
                    <div class="rounded-2xl shrink-0 w-14 h-14 overflow-hidden border border-white/10">
                        <img src="elektro.jpeg" alt="Teknik Elektronika" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-lg text-white flex items-center gap-2">Teknik Elektronika <i class="fa-solid fa-arrow-right text-xs opacity-50"></i></h3>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">Sistem kendali otomatisasi, mikrokarakter/IoT, perakitan rangkaian elektronik, dan mekatronika.</p>
                        <span class="inline-block mt-3 text-xs font-bold text-brandBlue">Lihat Detail & Foto &rarr;</span>
                    </div>
                </div>
            </div>

            <!-- DETAIL VIEW CONTAINER -->
            <div id="jurusan-detail" class="hidden glass-card-store p-6 sm:p-10 rounded-3xl border border-white/20 relative">
                <button onclick="hideDetail()" class="mb-6 inline-flex items-center gap-2 text-xs font-heading font-bold text-slate-300 hover:text-white bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl transition border border-white/10">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Jurusan
                </button>

                <div class="flex items-center gap-4 mb-4">
                    <div id="detail-icon" class="text-3xl text-brandYellow bg-brandYellow/10 p-4 rounded-2xl border border-brandYellow/20"></div>
                    <div>
                        <span class="text-xs font-heading font-bold text-brandYellow uppercase tracking-wider block">Detail Konsentrasi Keahlian</span>
                        <h3 id="detail-title" class="text-2xl sm:text-3xl font-heading font-extrabold text-white"></h3>
                    </div>
                </div>

                <p id="detail-desc" class="text-sm text-slate-300 leading-relaxed mb-8 max-w-4xl"></p>

                <h4 class="font-heading font-bold text-base text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-images text-brandYellow"></i> Galeri Kegiatan Praktikum & Fasilitas Bengkel
                </h4>

                <div id="detail-gallery" class="grid sm:grid-cols-3 gap-4"></div>
            </div>
        </div>
    </section>

    <!-- FASILITAS SEKOLAH -->
    <section id="fasilitas" class="py-24 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 relative z-10 overflow-hidden border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
                <span class="text-xs font-heading font-extrabold uppercase tracking-widest text-slate-950 bg-brandYellow px-4 py-1.5 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.3)]">Lingkungan Belajar</span>
                <h2 class="text-3xl sm:text-5xl font-heading font-extrabold text-white mt-4">Fasilitas Penunjang Pembelajaran</h2>
                <p class="text-slate-400 mt-3 text-sm">Fasilitas modern yang mendukung kenyamanan riset, kolaborasi, dan kegiatan belajar siswa.</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8 items-stretch">
                <div data-aos="fade-right" class="glass-card-store glass-card-blue rounded-3xl p-8 flex flex-col justify-between relative overflow-hidden group">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 bg-brandBlue/20 text-brandBlue rounded-2xl flex items-center justify-center text-2xl shadow-md border border-brandBlue/30">
                                <i class="fa-solid fa-book-bookmark"></i>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wider bg-brandBlue/20 text-brandBlue px-3 py-1 rounded-full border border-brandBlue/30">Indoor Quiet Zone</span>
                        </div>

                        <h3 class="font-heading font-extrabold text-2xl text-white mb-3">Perpustakaan Digital</h3>
                        <p class="text-sm text-slate-400 leading-relaxed mb-6">
                            Pusat literasi modern yang menyediakan akses ribuan judul buku fisik & e-book terintegrasi, ruang baca ber-AC yang tenang, serta komputer kecepatan tinggi untuk riset siswa.
                        </p>
                    </div>

                    <ul class="space-y-2.5 border-t border-slate-800 pt-6 text-xs font-semibold text-slate-300">
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-brandBlue"></i> Koleksi E-Book & Jurnal Digital
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-brandBlue"></i> Ruang Baca Ber-AC & Hening
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-brandBlue"></i> PC Terminal Akses Katalog
                        </li>
                    </ul>
                </div>

                <div data-aos="fade-up" data-aos-delay="100" class="flex flex-col gap-4 justify-center">
                    <div class="relative rounded-3xl overflow-hidden h-48 border border-white/10 shadow-md group">
                        <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=600&q=80" alt="Suasana Perpustakaan" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent flex items-end p-4">
                            <span class="text-xs font-heading font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-camera text-brandYellow"></i> Area Perpustakaan & Riset
                            </span>
                        </div>
                    </div>

                    <div class="relative rounded-3xl overflow-hidden h-48 border border-white/10 shadow-md group">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=600&q=80" alt="Suasana Taman Digital" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent flex items-end p-4">
                            <span class="text-xs font-heading font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-camera text-brandYellow"></i> Suasana Belajar Outdoor
                            </span>
                        </div>
                    </div>
                </div>

                <div data-aos="fade-left" data-aos-delay="200" class="glass-card-store rounded-3xl p-8 flex flex-col justify-between relative overflow-hidden group">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 bg-brandYellow/20 text-brandYellow rounded-2xl flex items-center justify-center text-2xl shadow-md border border-brandYellow/30">
                                <i class="fa-solid fa-wifi"></i>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wider bg-brandYellow/20 text-brandYellow px-3 py-1 rounded-full border border-brandYellow/30">Outdoor Study Space</span>
                        </div>

                        <h3 class="font-heading font-extrabold text-2xl text-white mb-3">Taman Digital</h3>
                        <p class="text-sm text-slate-400 leading-relaxed mb-6">
                            Area terbuka hijau yang dilengkapi koneksi Wi-Fi super cepat, gazebo kerja kelompok, dan colokan listrik. Tempat favorit siswa untuk belajar bareng, diskusi proyek, dan mengerjakan tugas luar ruangan.
                        </p>
                    </div>

                    <ul class="space-y-2.5 border-t border-slate-800 pt-6 text-xs font-semibold text-slate-300">
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-brandYellow"></i> High-Speed Outdoor Wi-Fi Zone
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-brandYellow"></i> Gazebo & Charging Station
                        </li>
                        <li class="flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-check text-brandYellow"></i> Area Asri & Estetik Buat Diskusi
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- EKSTRAKURIKULER -->
    <section id="eskul" class="py-24 bg-slate-950 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
                <h2 class="text-3xl sm:text-5xl font-heading font-extrabold text-white">Ekstrakurikuler & Organisasi</h2>
                <p class="text-slate-400 mt-3 text-sm">Wadah pengembangan minat, bakat, dan kepemimpinan siswa.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store glass-card-blue text-center">
                    <i class="fa-solid fa-campground text-brandBlue text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">Pramuka</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store text-center">
                    <i class="fa-solid fa-torii-gate text-brandYellow text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">Japanese Club</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store glass-card-blue text-center">
                    <i class="fa-solid fa-language text-brandBlue text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">English Club</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store text-center">
                    <i class="fa-solid fa-notes-medical text-brandYellow text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">PMR</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store glass-card-blue text-center">
                    <i class="fa-solid fa-palette text-brandBlue text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">Seni</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store text-center">
                    <i class="fa-solid fa-hand-fist text-brandYellow text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">Taekwondo</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store glass-card-blue text-center">
                    <i class="fa-solid fa-flag text-brandBlue text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">Paskibra</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store text-center">
                    <i class="fa-solid fa-users text-brandYellow text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">OSIS</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store glass-card-blue text-center">
                    <i class="fa-solid fa-user-tie text-brandBlue text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">MPK</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store text-center">
                    <i class="fa-solid fa-volleyball text-brandYellow text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">Voli</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store glass-card-blue text-center">
                    <i class="fa-solid fa-basketball text-brandBlue text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">Basket</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store text-center">
                    <i class="fa-solid fa-feather text-brandYellow text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">Badminton</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store glass-card-blue text-center">
                    <i class="fa-solid fa-mosque text-brandBlue text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">IPMI</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store text-center">
                    <i class="fa-solid fa-flask text-brandYellow text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">KIR</h4>
                </div>
                <div data-aos="zoom-in" class="p-4 rounded-2xl glass-card-store glass-card-blue text-center">
                    <i class="fa-solid fa-shield-halved text-brandBlue text-xl mb-2"></i>
                    <h4 class="font-heading font-bold text-xs text-slate-200">Pakubara</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- MITRA INDUSTRI & BKK -->
    <section class="py-20 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-xl mx-auto mb-10" data-aos="fade-up">
                <span class="text-xs font-heading font-extrabold uppercase tracking-widest text-slate-950 bg-brandYellow px-4 py-1.5 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.3)]">Kerjasama Industri</span>
                <h2 class="text-2xl sm:text-4xl font-heading font-extrabold text-white mt-4">Dukungan Mitra DUDI & BKK</h2>
                <p class="text-slate-400 text-xs sm:text-sm mt-2">SMKN 1 Katapang telah bermitra dengan puluhan industri nasional untuk penyaluran kerja & PKL.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12" data-aos="fade-up">
                <div class="p-5 rounded-3xl glass-card-store glass-card-blue text-center">
                    <span class="font-heading font-black text-4xl text-brandBlue block">85%+</span>
                    <span class="text-xs font-semibold text-slate-400 mt-1 block">Terserap Kerja & Usaha</span>
                </div>
                <div class="p-5 rounded-3xl glass-card-store text-center">
                    <span class="font-heading font-black text-4xl text-brandYellow block">50+</span>
                    <span class="text-xs font-semibold text-slate-400 mt-1 block">Perusahaan Mitra</span>
                </div>
                <div class="p-5 rounded-3xl glass-card-store glass-card-blue text-center">
                    <span class="font-heading font-black text-4xl text-brandBlue block">7</span>
                    <span class="text-xs font-semibold text-slate-400 mt-1 block">Konsentrasi Keahlian</span>
                </div>
                <div class="p-5 rounded-3xl glass-card-store text-center">
                    <span class="font-heading font-black text-4xl text-brandYellow block">100%</span>
                    <span class="text-xs font-semibold text-slate-400 mt-1 block">Sertifikasi Kompetensi</span>
                </div>
            </div>
        </div>
    </section>

    <!-- PENGUMUMAN SPMB -->
    <section id="pengumuman" class="py-24 bg-slate-950 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12" data-aos="fade-up">
                <span class="text-xs font-heading font-extrabold uppercase tracking-widest text-slate-950 bg-brandYellow px-4 py-1.5 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.3)]">Penerimaan Siswa Baru</span>
                <h2 class="text-3xl sm:text-5xl font-heading font-extrabold text-white mt-4">Pengumuman & Informasi SPMB</h2>
                <p class="text-slate-400 mt-3 text-sm">Informasi resmi Penerimaan Murid Baru SMKN 1 Katapang Tahun Ajaran 2026/2027.</p>
            </div>

            <div data-aos="fade-up" class="glass-card-store rounded-3xl p-6 sm:p-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-brandYellow/10 rounded-bl-full -z-0 blur-2xl pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 pb-8 border-b border-slate-800">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-xs font-bold mb-3 border border-emerald-500/30">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Pendaftaran Segera Dibuka
                        </div>
                        <h3 class="font-heading font-extrabold text-2xl sm:text-3xl text-white">SPMB SMKN 1 Katapang 2026/2027</h3>
                        <p class="text-sm text-slate-400 mt-1">Siapkan berkas Anda untuk menjadi bagian dari Sekolah Manusia Unggul (MAUNG).</p>
                    </div>

                    <div class="flex flex-wrap gap-3 shrink-0">
                        <a href="https://spmb.jabarprov.go.id/" target="_blank" rel="noopener noreferrer" class="bg-brandBlue hover:bg-blue-600 text-white font-heading font-bold px-6 py-3 rounded-2xl shadow-md transition text-sm flex items-center gap-2 border border-blue-400/30">
                            <i class="fa-solid fa-paper-plane"></i> Daftar Sekarang
                        </a>
                        <button onclick="alert('File Syarat & Panduan SPMB sedang diunduh...')" class="text-slate-200 hover:bg-white/10 font-heading font-bold px-5 py-3 rounded-2xl border border-white/20 transition text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-400"></i> Unduh Panduan PDF
                        </button>
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-6 mt-8">
                    <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-8 h-8 rounded-xl bg-brandBlue text-white font-bold flex items-center justify-center text-xs">01</span>
                            <h4 class="font-heading font-bold text-white text-base">Jalur Afirmasi & KETM</h4>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">Khusus bagi calon peserta didik dari keluarga ekonomi tidak mampu dan penyandang disabilitas.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-8 h-8 rounded-xl bg-brandYellow text-slate-950 font-bold flex items-center justify-center text-xs">02</span>
                            <h4 class="font-heading font-bold text-white text-base">Jalur Raport & Kejuaraan</h4>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">Seleksi berdasarkan nilai akumulasi rapor SMP/MTs serta prestasi perlombaan akademik/non-akademik.</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-8 h-8 rounded-xl bg-brandBlue text-white font-bold flex items-center justify-center text-xs">03</span>
                            <h4 class="font-heading font-bold text-white text-base">Jalur Minat Bakat (Ketat)</h4>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">Uji minat bakat & kesiapan fisik sesuai kriteria konsentrasi keahlian/jurusan yang dipilih.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- KATAPANG STORE MAIN SECTION -->
    <section id="katapang-store" class="py-24 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 text-white relative z-10 scroll-mt-20 overflow-hidden border-b border-slate-800">
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-brandBlue/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-brandYellow/20 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- LANDING STORE -->
            <div id="store-landing" class="transition-all duration-500 transform opacity-100 scale-100">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    
                    <!-- Left Content -->
                    <div data-aos="fade-right" data-aos-duration="1000">
                        <span class="inline-flex items-center gap-2 text-xs font-heading font-extrabold uppercase tracking-widest text-slate-950 bg-brandYellow px-4 py-1.5 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.5)] mb-6 animate-pulse">
                            <i class="fa-solid fa-crown text-slate-950"></i> Fitur Unggulan Utama
                        </span>
                        <h2 class="text-4xl sm:text-6xl font-heading font-extrabold text-white mt-2 leading-tight tracking-tight">
                            Katapang Store <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-brandYellow via-amber-300 to-yellow-500 glow-title">Layanan Seragam Digital</span>
                        </h2>
                        <p class="text-slate-300 text-base mt-6 leading-relaxed max-w-lg">
                            Solusi praktis & modern pemesanan seragam sekolah secara daring. Pilih ukuran, pesan langsung via WhatsApp atau scan QRIS tanpa antre!
                        </p>
                        
                        <div class="mt-10 flex flex-wrap gap-4 items-center">
                            <button id="btn-buka-katalog" type="button" onclick="bukaKatalogLangsung()" class="bg-gradient-to-r from-brandYellow to-yellow-500 hover:from-yellow-400 hover:to-amber-500 text-slate-950 font-heading font-extrabold px-8 py-4 rounded-2xl shadow-[0_0_25px_rgba(234,179,8,0.4)] transition-all duration-300 transform hover:scale-105 active:scale-95 inline-flex items-center gap-3 text-sm cursor-pointer border border-yellow-200">
                                <i class="fa-solid fa-store text-lg"></i> Jelajahi Katalog Seragam &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- Right Preview Cards -->
                    <div class="grid sm:grid-cols-2 gap-6" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="200">
                        <div class="glass-card-store p-6 rounded-3xl group">
                            <div class="w-14 h-14 bg-brandYellow/10 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition duration-300">
                                <i class="fa-solid fa-shirt text-brandYellow text-3xl group-hover:rotate-12 transition duration-300"></i>
                            </div>
                            <h4 class="font-heading font-bold text-lg text-white">Seragam Olahraga</h4>
                            <p class="text-xs text-slate-400 mt-1">Bahan nyaman & menyerap keringat</p>
                            <span class="inline-block mt-4 text-sm font-extrabold text-brandYellow">Rp 150.000</span>
                        </div>

                        <div class="glass-card-store glass-card-blue p-6 rounded-3xl group">
                            <div class="w-14 h-14 bg-brandBlue/10 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition duration-300">
                                <i class="fa-solid fa-user-tie text-brandBlue text-3xl group-hover:-rotate-12 transition duration-300"></i>
                            </div>
                            <h4 class="font-heading font-bold text-lg text-white">Batik Khusus K1</h4>
                            <p class="text-xs text-slate-400 mt-1">Motif khas SMKN 1 Katapang</p>
                            <span class="inline-block mt-4 text-sm font-extrabold text-brandBlue">Rp 120.000</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- KATALOG SERAGAM TERBUNGKUS -->
            <div id="store-catalog" class="hidden opacity-0 scale-95 transition-all duration-500 transform">
                <div class="flex flex-wrap items-center justify-between pb-6 mb-10 border-b border-slate-800 gap-4">
                    <button id="btn-kembali-store" type="button" class="text-slate-300 hover:text-white font-bold text-xs bg-white/10 hover:bg-white/20 px-5 py-3 rounded-2xl transition cursor-pointer flex items-center gap-2 border border-white/10">
                        <i class="fa-solid fa-arrow-left"></i> Kembali Ke Landing Store
                    </button>
                    
                    <div class="flex items-center gap-4">
                        <h3 class="font-heading font-extrabold text-2xl text-white tracking-tight">Katalog Seragam & Atribut</h3>
                        <button onclick="bukaModalKeranjang()" class="relative bg-brandYellow hover:bg-yellow-400 text-slate-950 font-heading font-extrabold px-5 py-2.5 rounded-2xl text-xs flex items-center gap-2 shadow-[0_0_15px_rgba(234,179,8,0.4)] transition">
                            <i class="fa-solid fa-cart-shopping"></i> Keranjang
                            <span id="badge-cart" class="bg-red-600 text-white rounded-full w-5 h-5 text-[10px] flex items-center justify-center font-extrabold">0</span>
                        </button>
                    </div>
                </div>

                <!-- GRID PRODUK -->
                <div id="product-grid" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6"></div>
            </div>
        </div>
    </section>

    <!-- FLOATING CART BUTTON -->
    <div id="floating-cart-btn" class="hidden fixed bottom-6 right-6 z-40">
        <button onclick="bukaModalKeranjang()" class="bg-brandYellow hover:bg-yellow-400 text-slate-950 font-bold p-4 rounded-2xl shadow-[0_0_20px_rgba(234,179,8,0.5)] flex items-center gap-3 transition transform hover:scale-110">
            <div class="relative">
                <i class="fa-solid fa-cart-shopping text-xl"></i>
                <span id="floating-badge" class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">0</span>
            </div>
            <span class="font-heading text-sm font-extrabold">Keranjang Belanja</span>
        </button>
    </div>

    <!-- MODAL LOGIN USER -->
    <div id="modal-login" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-4">
        <div class="glass-card-store w-full max-w-md rounded-3xl p-6 sm:p-8 shadow-2xl relative text-white border border-white/20">
            <button onclick="tutupModalLogin()" class="absolute top-5 right-5 text-slate-400 hover:text-white text-xl">&times;</button>

            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-brandYellow/20 text-brandYellow border border-brandYellow/30 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </div>
                <h3 class="font-heading font-extrabold text-2xl text-white">Login Katapang Store</h3>
                <p class="text-slate-400 text-xs mt-1">Silakan login terlebih dahulu untuk mengakses keranjang & checkout pesanan.</p>
            </div>

            <form onsubmit="handleLoginSubmit(event)" class="space-y-4 text-xs">
                <p id="login-error" class="hidden bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl p-3 text-[11px] font-semibold"></p>
                <div>
                    <label class="block text-slate-300 mb-1 font-semibold">NISN / Username Siswa</label>
                    <div class="relative">
                        <input type="text" id="login-username" required placeholder="Masukkan NISN atau Username" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 pl-10 text-white focus:outline-none focus:border-brandYellow">
                        <i class="fa-solid fa-user absolute left-3.5 top-3.5 text-slate-500"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 mb-1 font-semibold">Kata Sandi / Password</label>
                    <div class="relative">
                        <input type="password" id="login-password" required placeholder="Masukkan Password Anda" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 pl-10 text-white focus:outline-none focus:border-brandYellow">
                        <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-slate-500"></i>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-400 py-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="rounded border-slate-700 bg-slate-900 text-brandYellow focus:ring-0">
                        <span>Ingat saya</span>
                    </label>
                    <a href="javascript:void(0)" onclick="alert('Silakan hubungi admin sekolah/koperasi untuk reset password.')" class="hover:text-brandYellow transition">Lupa password?</a>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-brandYellow to-yellow-500 hover:from-yellow-400 hover:to-amber-500 text-slate-950 font-heading font-extrabold py-3.5 rounded-xl text-sm transition shadow-[0_0_15px_rgba(234,179,8,0.3)]">
                    Login Sekarang & Lanjut Keranjang
                </button>

                <p class="text-center text-slate-400 text-[11px] pt-1">
                    Belum punya akun? <a href="register.php" class="text-brandYellow font-bold hover:underline">Daftar di sini</a>
                </p>
            </form>
        </div>
    </div>

    <!-- MODAL KERANJANG & CHECKOUT PEMBAYARAN -->
    <div id="modal-keranjang" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-4">
        <div class="glass-card-store w-full max-w-xl rounded-3xl p-6 shadow-2xl relative text-white max-h-[90vh] flex flex-col justify-between overflow-y-auto">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
                    <h3 class="font-heading font-extrabold text-lg flex items-center gap-2 text-brandYellow">
                        <i class="fa-solid fa-basket-shopping"></i> Keranjang Belanja Koperasi
                    </h3>
                    <button onclick="tutupModalKeranjang()" class="text-slate-400 hover:text-white text-xl">&times;</button>
                </div>

                <?php if ($sudahLogin): ?>
                <div class="flex items-center justify-between bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 mb-4 text-[11px]">
                    <span class="text-slate-300">
                        <i class="fa-solid fa-circle-user text-brandYellow mr-1"></i>
                        Login sebagai <span class="font-bold text-white"><?= htmlspecialchars($namaSiswa) ?></span>
                    </span>
                    <a href="auth/logout.php" class="text-red-400 hover:text-red-300 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </div>
                <?php endif; ?>

                <!-- LIST PRODUK DI KERANJANG -->
                <div id="cart-item-list" class="max-h-56 overflow-y-auto space-y-3 pr-2 mb-4"></div>

                <!-- TOTAL HARGA -->
                <div class="bg-white/5 p-4 rounded-2xl flex justify-between items-center mb-4 border border-white/10">
                    <span class="text-xs text-slate-400 font-semibold uppercase">Total Pembayaran</span>
                    <span id="total-harga-keranjang" class="text-lg font-black text-brandYellow">Rp 0</span>
                </div>

                <!-- FORM DATA PEMESAN FOR CHECKOUT -->
                <div class="space-y-3 text-xs mb-4">
                    <div>
                        <label class="block text-slate-300 mb-1 font-semibold">Nama Lengkap Siswa / Pemesan</label>
                        <input type="text" id="input-nama" placeholder="Contoh: Ahmad Fauzi" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:outline-none focus:border-brandYellow">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-slate-300 mb-1 font-semibold">Kelas / Jurusan</label>
                            <input type="text" id="input-kelas" placeholder="Contoh: X RPL 1" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:outline-none focus:border-brandYellow">
                        </div>
                        <div>
                            <label class="block text-slate-300 mb-1 font-semibold">Metode Pembayaran</label>
                            <select id="input-metode" onchange="updateButtonState()" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-white focus:outline-none focus:border-brandYellow">
                                <option value="WhatsApp / Kasir Koperasi">Bayar di Koperasi / Cash</option>
                                <option value="Transfer Bank / QRIS">Transfer Bank / QRIS</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BUTTON ACTION -->
            <div class="pt-4 border-t border-slate-800 flex gap-3">
                <button onclick="tutupModalKeranjang()" class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-3 rounded-xl text-xs transition">
                    Batal
                </button>
                <button id="btn-checkout" onclick="handleCheckout()" class="w-2/3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-4 rounded-xl flex items-center justify-center gap-2 text-xs transition-all shadow-md">
                    <svg id="btn-icon" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    <span id="btn-text">Checkout & Bayar via WhatsApp</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL QRIS PEMBAYARAN -->
    <div id="qrModal" class="modal fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4" style="display:none;">
      <div class="modal-content glass-card-store p-6 rounded-3xl text-center text-white max-w-sm w-full">
        <h3 class="text-lg font-bold mb-3 text-brandYellow">Scan QRIS untuk Pembayaran</h3>
        <img id="qrImage" src="qrdidi.jpeg" alt="QRIS Code" class="w-48 h-48 mx-auto mb-3 object-contain bg-white p-2 rounded-2xl" />
        <p class="text-sm mb-4">Total: <strong id="qrTotal" class="text-brandYellow">Rp 20.000</strong></p>
        
        <div class="flex justify-center gap-2">
            <a id="btnDownload" href="qrdidi.jpeg" download="QRIS-Pembayaran.png" onclick="kosongkanKeranjang()" class="btn-download bg-brandBlue hover:bg-blue-600 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition inline-block">Unduh QR Code</a>
            <button onclick="closeQrModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold py-2.5 px-4 rounded-xl transition">Tutup</button>
        </div>
      </div>
    </div>

    <!-- MEDIA SOSIAL -->
    <section class="py-20 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 text-white border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-aos="fade-up">
            <span class="text-xs font-heading font-extrabold uppercase tracking-widest text-slate-950 bg-brandYellow px-4 py-1.5 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.3)]">Terhubung Bersama Kami</span>
            <h2 class="text-3xl sm:text-4xl font-heading font-extrabold mt-4 mb-3">Ikuti Media Sosial Resmi</h2>
            <p class="text-slate-400 text-sm max-w-xl mx-auto mb-8">Dapatkan update kegiatan siswa, keseruan acara sekolah, dan konten edukatif menarik setiap harinya.</p>
            
            <div class="flex flex-wrap justify-center gap-4">
                <a href="https://www.instagram.com/smkn1katapang" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 bg-gradient-to-r from-purple-600 via-pink-600 to-yellow-500 hover:opacity-90 text-white font-heading font-bold px-6 py-3.5 rounded-2xl shadow-lg transition transform hover:-translate-y-1">
                    <i class="fa-brands fa-instagram text-2xl"></i>
                    <div class="text-left">
                        <span class="block text-[10px] opacity-80 uppercase tracking-wider font-semibold">Follow Instagram</span>
                        <span class="text-sm">@smkn1katapang</span>
                    </div>
                </a>

                <a href="https://www.youtube.com/@smkn1katapang" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 bg-red-600 hover:bg-red-700 text-white font-heading font-bold px-6 py-3.5 rounded-2xl shadow-lg transition transform hover:-translate-y-1">
                    <i class="fa-brands fa-youtube text-2xl"></i>
                    <div class="text-left">
                        <span class="block text-[10px] opacity-80 uppercase tracking-wider font-semibold">Subscribe YouTube</span>
                        <span class="text-sm">SMKN 1 Katapang</span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-950 text-slate-400 py-12">
        <div class="max-w-7xl mx-auto px-4 text-center font-body text-sm">
            <p class="text-white font-heading font-bold text-lg mb-1">SMKN 1 KATAPANG</p>
            <p class="text-slate-400 text-xs mb-6">Sekolah Manusia Unggul (MAUNG)</p>
            <p class="text-xs text-slate-500">&copy; 2026 SMKN 1 Katapang. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- AOS Script JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });

        // STATE USER LOGIN
        // Nilai awal diambil dari SESSION PHP di server (lihat atas file),
        // supaya kalau siswa refresh halaman, status login tidak hilang.
        let isUserLoggedIn = <?= $sudahLogin ? 'true' : 'false' ?>;

        // DATA KATALOG SERAGAM & ATRIBUT
        // Diisi langsung dari tabel `produk` di database (bukan hardcode lagi),
        // supaya kalau harga/produk berubah di DB, otomatis berubah di sini juga.
        const dataSeragam = <?php
            $produkDb = $pdo->query('SELECT id, nama, harga, icon FROM produk ORDER BY id')->fetchAll();
            echo json_encode($produkDb, JSON_UNESCAPED_UNICODE);
        ?>;

        // Nomor WA koperasi diambil dari config.php
        const NO_WA_KOPERASI = "<?= NO_WA_KOPERASI ?>";

        let keranjang = [];

        function kosongkanKeranjang() {
            keranjang = [];
            updateKeranjangUI();
        }

        function bukaKatalogLangsung() {
            const landing = document.getElementById("store-landing");
            const catalog = document.getElementById("store-catalog");
            const floatingBtn = document.getElementById("floating-cart-btn");
            const grid = document.getElementById("product-grid");

            landing.classList.remove("opacity-100", "scale-100");
            landing.classList.add("opacity-0", "scale-95");

            setTimeout(() => {
                landing.classList.add("hidden");
                catalog.classList.remove("hidden");
                
                grid.innerHTML = "";
                dataSeragam.forEach((item, index) => {
                    grid.innerHTML += `
                        <div class="glass-card-store p-6 rounded-3xl text-white flex flex-col justify-between animate-card-pop group" style="animation-delay: ${index * 60}ms">
                            <div>
                                <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition duration-300">
                                    <i class="fa-solid ${item.icon} text-brandYellow text-2xl"></i>
                                </div>
                                <h4 class="font-heading font-bold text-base mb-1 text-white">${item.nama}</h4>
                                <p class="text-xs text-slate-400 mb-4 font-semibold">Rp ${item.harga.toLocaleString('id-ID')}</p>
                            </div>
                            <button type="button" onclick="tambahKeKeranjang(${item.id})" class="w-full bg-brandYellow hover:bg-yellow-400 text-slate-950 font-heading font-bold py-3 rounded-2xl text-xs transition flex items-center justify-center gap-2 shadow-sm">
                                <i class="fa-solid fa-cart-plus"></i> Tambah ke Keranjang
                            </button>
                        </div>
                    `;
                });

                setTimeout(() => {
                    catalog.classList.remove("opacity-0", "scale-95");
                    catalog.classList.add("opacity-100", "scale-100");
                    floatingBtn.classList.remove("hidden");
                }, 50);

            }, 300);

            document.getElementById('katapang-store').scrollIntoView({ behavior: 'smooth' });
        }

        function tambahKeKeranjang(id) {
            const produk = dataSeragam.find(p => p.id === id);
            const adaDiKeranjang = keranjang.find(k => k.id === id);

            if (adaDiKeranjang) {
                adaDiKeranjang.jumlah += 1;
            } else {
                keranjang.push({ ...produk, jumlah: 1 });
            }

            updateKeranjangUI();
        }

        function ubahJumlahItem(id, delta) {
            const item = keranjang.find(k => k.id === id);
            if (item) {
                item.jumlah += delta;
                if (item.jumlah <= 0) {
                    keranjang = keranjang.filter(k => k.id !== id);
                }
            }
            updateKeranjangUI();
        }

        function updateKeranjangUI() {
            const totalQty = keranjang.reduce((sum, item) => sum + item.jumlah, 0);
            document.getElementById('badge-cart').innerText = totalQty;
            document.getElementById('floating-badge').innerText = totalQty;

            const cartList = document.getElementById('cart-item-list');
            cartList.innerHTML = '';

            let totalHarga = 0;

            if (keranjang.length === 0) {
                cartList.innerHTML = '<p class="text-xs text-slate-400 text-center py-4">Keranjang belanja kamu masih kosong.</p>';
            } else {
                keranjang.forEach(item => {
                    const subtotal = item.harga * item.jumlah;
                    totalHarga += subtotal;
                    cartList.innerHTML += `
                        <div class="bg-white/5 p-3 rounded-2xl flex items-center justify-between border border-white/10">
                            <div>
                                <h5 class="text-xs font-bold text-white">${item.nama}</h5>
                                <p class="text-[10px] text-slate-400">Rp ${item.harga.toLocaleString('id-ID')} x ${item.jumlah} = <span class="text-brandYellow">Rp ${subtotal.toLocaleString('id-ID')}</span></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="ubahJumlahItem(${item.id}, -1)" class="w-6 h-6 bg-white/10 hover:bg-white/20 rounded text-xs flex items-center justify-center text-white">-</button>
                                <span class="text-xs font-bold px-1 text-white">${item.jumlah}</span>
                                <button onclick="ubahJumlahItem(${item.id}, 1)" class="w-6 h-6 bg-white/10 hover:bg-white/20 rounded text-xs flex items-center justify-center text-white">+</button>
                            </div>
                        </div>
                    `;
                });
            }

            document.getElementById('total-harga-keranjang').innerText = `Rp ${totalHarga.toLocaleString('id-ID')}`;
        }

        // CONTROL MODAL LOGIN
        function bukaModalLogin() {
            document.getElementById('modal-login').classList.remove('hidden');
        }

        function tutupModalLogin() {
            document.getElementById('modal-login').classList.add('hidden');
        }

        function handleLoginSubmit(event) {
            event.preventDefault();
            const username = document.getElementById('login-username').value.trim();
            const password = document.getElementById('login-password').value;
            const errorBox = document.getElementById('login-error');
            const submitBtn = event.target.querySelector('button[type="submit"]');

            if (errorBox) errorBox.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = true;

            fetch('auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        isUserLoggedIn = true;

                        const inputNama = document.getElementById('input-nama');
                        const inputKelas = document.getElementById('input-kelas');
                        if (inputNama && !inputNama.value) inputNama.value = data.siswa.nama || username;
                        if (inputKelas && !inputKelas.value) inputKelas.value = data.siswa.kelas || '';

                        tutupModalLogin();

                        updateKeranjangUI();
                        updateButtonState();
                        document.getElementById('modal-keranjang').classList.remove('hidden');
                    } else {
                        if (errorBox) {
                            errorBox.innerText = data.message || 'Login gagal, silakan coba lagi.';
                            errorBox.classList.remove('hidden');
                        } else {
                            alert(data.message || 'Login gagal, silakan coba lagi.');
                        }
                    }
                })
                .catch(() => {
                    alert('Tidak bisa menghubungi server. Pastikan database & server PHP sudah menyala.');
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
        }

        function bukaModalKeranjang() {
            if (!isUserLoggedIn) {
                bukaModalLogin();
                return;
            }
            updateKeranjangUI();
            updateButtonState();
            document.getElementById('modal-keranjang').classList.remove('hidden');
        }

        function tutupModalKeranjang() {
            document.getElementById('modal-keranjang').classList.add('hidden');
        }

        function openQrModal() {
            const total = keranjang.reduce((sum, item) => sum + (item.harga * item.jumlah), 0);
            document.getElementById('qrTotal').innerText = `Rp ${total.toLocaleString('id-ID')}`;
            document.getElementById('qrModal').style.display = 'flex';
        }

        function closeQrModal() {
            document.getElementById('qrModal').style.display = 'none';
        }

        function updateButtonState() {
            const metode = document.getElementById('input-metode').value;
            const btnText = document.getElementById('btn-text');
            const btnIcon = document.getElementById('btn-icon');

            if (metode === 'Transfer Bank / QRIS') {
                btnText.innerText = 'Tampilkan QR Pembayaran';
                btnIcon.style.display = 'none';
            } else {
                btnText.innerText = 'Checkout & Bayar via WhatsApp';
                btnIcon.style.display = 'inline-block';
            }
        }

        function handleCheckout() {
            if (keranjang.length === 0) {
                alert("Keranjang kamu masih kosong, pilih seragam dulu ya!");
                return;
            }

            const nama = document.getElementById('input-nama').value.trim();
            const kelas = document.getElementById('input-kelas').value.trim();
            const metode = document.getElementById('input-metode').value;

            if (!nama || !kelas) {
                alert("Mohon isi Nama Lengkap dan Kelas/Jurusan!");
                return;
            }

            const btnCheckout = document.getElementById('btn-checkout');
            if (btnCheckout) btnCheckout.disabled = true;

            // Simpan pesanan ke database dulu (tabel pesanan + pesanan_detail),
            // baru lanjut ke WhatsApp / tampilkan QRIS seperti alur aslinya.
            fetch('checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nama, kelas, metode, keranjang })
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Gagal menyimpan pesanan ke database.');
                        return;
                    }

                    if (metode === 'Transfer Bank / QRIS') {
                        tutupModalKeranjang();
                        openQrModal();
                    } else {
                        let pesan = `*PESANAN KOPERASI SMKN 1 KATAPANG*\n`;
                        pesan += `------------------------------------\n`;
                        pesan += `*No. Pesanan:* #${data.pesanan_id}\n`;
                        pesan += `*Nama Pemesan:* ${nama}\n`;
                        pesan += `*Kelas/Jurusan:* ${kelas}\n`;
                        pesan += `*Metode Bayar:* ${metode}\n`;
                        pesan += `------------------------------------\n`;
                        pesan += `*Daftar Barang:* \n`;

                        let total = 0;
                        keranjang.forEach((item, index) => {
                            const subtotal = item.harga * item.jumlah;
                            total += subtotal;
                            pesan += `${index + 1}. ${item.nama} (${item.jumlah}x) = Rp ${subtotal.toLocaleString('id-ID')}\n`;
                        });

                        pesan += `------------------------------------\n`;
                        pesan += `*TOTAL PEMBAYARAN: Rp ${total.toLocaleString('id-ID')}*\n\n`;
                        pesan += `Mohon diproses, terima kasih!`;

                        window.open(`https://wa.me/${NO_WA_KOPERASI}?text=${encodeURIComponent(pesan)}`, '_blank');
                        kosongkanKeranjang();
                        tutupModalKeranjang();
                    }
                })
                .catch(() => {
                    alert('Tidak bisa menghubungi server. Pastikan database & server PHP sudah menyala.');
                })
                .finally(() => {
                    if (btnCheckout) btnCheckout.disabled = false;
                });
        }

        const dataJurusan = {
            to: {
                title: "Teknik Otomotif (TO)",
                icon: '<i class="fa-solid fa-car"></i>',
                desc: "Konsentrasi keahlian Teknik Otomotif membekali siswa dengan pengetahuan mendalam mengenai pemeliharaan dan perbaikan mesin kendaraan bermotor, sistem kelistrikan otomotif, diagnosa kerusakan berbasis komputer (EFI/Scanner), serta pengenalan teknologi mobil listrik & hybrid.",
                images: [
                    { url: "https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98?auto=format&fit=crop&w=600&q=80", caption: "Praktikum Pemeliharaan Mesin" },
                    { url: "https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=600&q=80", caption: "Perbaikan Sistem Kelistrikan" },
                    { url: "https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=600&q=80", caption: "Bengkel Keahlian Otomotif" }
                ]
            },
            tm: {
                title: "Teknik Mesin (TM)",
                icon: '<i class="fa-solid fa-screwdriver-wrench"></i>',
                desc: "Teknik Mesin berfokus pada penguasaan teknologi manufaktur dan pemesinan. Siswa diajarkan pengoperasian mesin bubut, mesin frais, pengelasan listrik/argon, pembuatan gambar teknik dengan CAD, hingga pengoperasian mesin modern berbasis komputer (CNC).",
                images: [
                    { url: "https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80", caption: "Pengoperasian Mesin Industri" },
                    { url: "https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?auto=format&fit=crop&w=600&q=80", caption: "Proses Fabrikasi & Pengelasan" },
                    { url: "https://images.unsplash.com/photo-1581092335397-9583fe92d232?auto=format&fit=crop&w=600&q=80", caption: "Pemrograman Mesin CNC" }
                ]
            },
            tt: {
                title: "Teknik Tekstil (TT)",
                icon: '<i class="fa-solid fa-shirt"></i>',
                desc: "Teknik Tekstil mempelajari proses pengolahan bahan serat menjadi benang, pembuatan kain tenun/rajut, proses pencelupan warna dan pencapan (printing), serta pengujian kualitas fisik & kimia mutu produk tekstil skala industri.",
                images: [
                    { url: "https://images.unsplash.com/photo-1604014237800-1c9102c219da?auto=format&fit=crop&w=600&q=80", caption: "Laboratorium Pengujian Tekstil" },
                    { url: "https://images.unsplash.com/photo-1528458909336-e7a0adfac1d5?auto=format&fit=crop&w=600&q=80", caption: "Proses Pembuatan & Pengolahan Kain" },
                    { url: "https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&w=600&q=80", caption: "Pencelupan & Mutu Produk" }
                ]
            },
            tkj: {
                title: "Teknik Komputer & Jaringan (TKJ)",
                icon: '<i class="fa-solid fa-network-wired"></i>',
                desc: "TKJ mendidik siswa menjadi tenaga ahli di bidang infrastruktur jaringan. Materi unggulan meliputi rancang bangun jaringan Mikrotik & Cisco, instalasi Fiber Optic, konfigurasi Server (Linux/Windows), Wireless Network, serta Cyber Security dasar.",
                images: [
                    { url: "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=600&q=80", caption: "Perakitan & Manajemen Server" },
                    { url: "https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=600&q=80", caption: "Praktikum Pengabelan Fiber Optic" },
                    { url: "https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=600&q=80", caption: "Lab Komputer Networking" }
                ]
            },
            rpl: {
                title: "Rekayasa Perangkat Lunak (RPL)",
                icon: '<i class="fa-solid fa-code"></i>',
                desc: "Siswa RPL dibimbing menjadi Software Developer modern yang menguasai Pemrograman Web (HTML, CSS, JS, PHP/Framework), Pengembangan Aplikasi Mobile (Android/Flutter), Pengelolaan Database (MySQL), UI/UX Design, dan Metodologi Agile Development.",
                images: [
                    { url: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=600&q=80", caption: "Coding & Web Development" },
                    { url: "https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80", caption: "Diskusi Proyek Software" },
                    { url: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80", caption: "Pengembangan Aplikasi Mobile" }
                ]
            },
            bc: {
                title: "Broadcasting & Perfilman",
                icon: '<i class="fa-solid fa-video"></i>',
                desc: "Jurusan Broadcasting menyiapkan lulusan kreatif di bidang industri media. Menguasai manajemen produksi TV/film, pengoperasian kamera pro, pencahayaan (lighting), tata suara (audio mixing), penyutradaraan, dan editing video digital (Premiere/After Effects).",
                images: [
                    { url: "https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?auto=format&fit=crop&w=600&q=80", caption: "Produksi Video di Studio" },
                    { url: "https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?auto=format&fit=crop&w=600&q=80", caption: "Teknik Pengambilan Gambar (Kamera)" },
                    { url: "https://images.unsplash.com/photo-1536240478700-b869070f9279?auto=format&fit=crop&w=600&q=80", caption: "Editing Video & Post-Production" }
                ]
            },
            tek: {
                title: "Teknik Elektronika",
                icon: '<i class="fa-solid fa-microchip"></i>',
                desc: "Teknik Elektronika berfokus pada perangkat elektronik dan otomatisasi industri. Pembelajaran mencakup perancangan skema PCB, pemrograman Microcontroller / Arduino / ESP32, sistem sensor Internet of Things (IoT), PLC, dan Mekatronika dasar.",
                images: [
                    { url: "https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?auto=format&fit=crop&w=600&q=80", caption: "Perakitan Komponen Elektronika" },
                    { url: "https://images.unsplash.com/photo-1553406830-ef2513450d76?auto=format&fit=crop&w=600&q=80", caption: "Pemrograman IoT & Robotik" },
                    { url: "https://images.unsplash.com/photo-1581092580497-e0d23cbdf1dc?auto=format&fit=crop&w=600&q=80", caption: "Pengujian Rangkaian Otomatisasi" }
                ]
            }
        };

        function showDetail(key) {
            const jurusan = dataJurusan[key];
            if (!jurusan) return;

            const listEl = document.getElementById('jurusan-list');
            const detailEl = document.getElementById('jurusan-detail');

            listEl.style.transition = "opacity 0.25s ease, transform 0.25s ease";
            listEl.style.opacity = "0";
            listEl.style.transform = "scale(0.96)";

            setTimeout(() => {
                listEl.classList.add('hidden');

                document.getElementById('detail-icon').innerHTML = jurusan.icon;
                document.getElementById('detail-title').innerText = jurusan.title;
                document.getElementById('detail-desc').innerText = jurusan.desc;

                const galleryContainer = document.getElementById('detail-gallery');
                galleryContainer.innerHTML = '';
                
                jurusan.images.forEach((img, index) => {
                    const imgCard = `
                        <div class="overflow-hidden rounded-2xl border border-white/10 group bg-slate-900 shadow-sm hover:shadow-md transition duration-300 animate-card-pop" style="animation-delay: ${index * 100}ms">
                            <div class="h-48 overflow-hidden relative">
                                <img src="${img.url}" alt="${img.caption}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
                            </div>
                            <div class="p-3.5 text-center bg-white/5">
                                <p class="text-xs font-heading font-bold text-slate-200">${img.caption}</p>
                            </div>
                        </div>
                    `;
                    galleryContainer.innerHTML += imgCard;
                });

                detailEl.classList.remove('hidden');
                detailEl.classList.add('fade-detail-enter');

                document.getElementById('jurusan').scrollIntoView({ behavior: 'smooth' });

                setTimeout(() => {
                    detailEl.classList.add('fade-detail-show');
                }, 50);
            }, 250);
        }

        function hideDetail() {
            const listEl = document.getElementById('jurusan-list');
            const detailEl = document.getElementById('jurusan-detail');

            detailEl.classList.remove('fade-detail-show');
            
            setTimeout(() => {
                detailEl.classList.add('hidden');
                detailEl.classList.remove('fade-detail-enter');

                listEl.classList.remove('hidden');
                
                setTimeout(() => {
                    listEl.style.opacity = "1";
                    listEl.style.transform = "scale(1)";
                }, 50);

                document.getElementById('jurusan').scrollIntoView({ behavior: 'smooth' });
            }, 300);
        }

        const quizQuestions = [
            {
                question: "Apa topik atau kegiatan yang paling kamu sukai saat waktu luang?",
                options: [
                    { text: "Bongkar pasang mesin, ngulik motor/mobil, atau hal berbau mekanik.", category: "to" },
                    { text: "Membuat aplikasi, koding web, atau mainan software komputer.", category: "rpl" },
                    { text: "Mengedit video, memotret, atau bikin konten media kreatif.", category: "bc" },
                    { text: "Mengatur jaringan internet, Wi-Fi, server, dan hardware PC.", category: "tkj" },
                    { text: "Membuat alat otomatis, merangkai komponen kabel/chip elektronik.", category: "tek" },
                    { text: "Suka desain motif kain, bahan pakaian, dan proses tekstil.", category: "tt" },
                    { text: "Mengoperasikan mesin perkakas, las, atau fabrikasi logam.", category: "tm" }
                ]
            },
            {
                question: "Lingkungan kerja seperti apa yang paling kamu impikan di masa depan?",
                options: [
                    { text: "Bengkel otomotif modern atau industri manufaktur kendaraan.", category: "to" },
                    { text: "Perusahaan IT, Startup, atau bekerja sebagai Remote Developer.", category: "rpl" },
                    { text: "Studio TV, Production House film, atau industri kreatif digital.", category: "bc" },
                    { text: "Data Center, Perusahaan ISP (Internet), atau Network Engineer.", category: "tkj" },
                    { text: "Pabrik otomatisasi industri, Robotik, atau IoT Engineering.", category: "tek" },
                    { text: "Industri garmen, pabrik pengolahan serat & pewarnaan kain.", category: "tt" },
                    { text: "Bengkel permesinan presisi, industri logam, atau operator CNC.", category: "tm" }
                ]
            },
            {
                question: "Keahlian utama apa yang ingin kamu kuasai setelah lulus SMKN 1 Katapang?",
                options: [
                    { text: "Bisa mendiagnosa dan memperbaiki kerusakan mesin kendaraan.", category: "to" },
                    { text: "Bisa menciptakan aplikasi Android/Web siap pakai sendiri.", category: "rpl" },
                    { text: "Bisa memproduksi video pendek/film profesional dari Script ke Editing.", category: "bc" },
                    { text: "Bisa membangun jaringan fiber optic & mengamankan server.", category: "tkj" },
                    { text: "Bisa membuat program perangkat pintar (IoT) & microcontroller.", category: "tek" },
                    { text: "Bisa menguji mutu bahan tekstil & teknik pencelupan warna.", category: "tt" },
                    { text: "Bisa membaca gambar teknik CAD & membubut komponen mesin.", category: "tm" }
                ]
            }
        ];

        let currentStep = 0;
        let userScores = { to: 0, tm: 0, tt: 0, tkj: 0, rpl: 0, bc: 0, tek: 0 };

        function renderQuestion() {
            const q = quizQuestions[currentStep];
            const container = document.getElementById('quiz-content');
            
            const percent = Math.round(((currentStep + 1) / quizQuestions.length) * 100);
            document.getElementById('quiz-step-text').innerText = `Pertanyaan ${currentStep + 1} dari ${quizQuestions.length}`;
            document.getElementById('quiz-percent-text').innerText = `${percent}%`;
            document.getElementById('quiz-progress-bar').style.width = `${percent}%`;

            let optionsHTML = '';
            q.options.forEach((opt) => {
                optionsHTML += `
                    <button onclick="selectAnswer('${opt.category}')" class="w-full text-left p-4 rounded-2xl bg-white/5 hover:bg-brandYellow hover:text-slate-950 border border-white/10 hover:border-brandYellow transition duration-200 text-xs sm:text-sm font-medium flex items-center justify-between group">
                        <span>${opt.text}</span>
                        <i class="fa-solid fa-chevron-right opacity-0 group-hover:opacity-100 transition"></i>
                    </button>
                `;
            });

            container.innerHTML = `
                <h4 class="font-heading font-bold text-lg sm:text-xl text-white mb-5">${q.question}</h4>
                <div class="grid gap-3 max-h-[260px] overflow-y-auto pr-1">
                    ${optionsHTML}
                </div>
            `;
        }

        function selectAnswer(category) {
            userScores[category]++;
            currentStep++;

            if (currentStep < quizQuestions.length) {
                renderQuestion();
            } else {
                showQuizResult();
            }
        }

        function showQuizResult() {
            let highestCategory = 'rpl';
            let maxScore = -1;
            for (const cat in userScores) {
                if (userScores[cat] > maxScore) {
                    maxScore = userScores[cat];
                    highestCategory = cat;
                }
            }

            const recommendedJurusan = dataJurusan[highestCategory];

            document.getElementById('quiz-progress-container').classList.add('hidden');
            document.getElementById('quiz-content').classList.add('hidden');

            document.getElementById('result-title').innerText = recommendedJurusan.title;
            document.getElementById('result-desc').innerText = `Berdasarkan jawabanmu, kamu cocok masuk ${recommendedJurusan.title}! ${recommendedJurusan.desc.substring(0, 140)}...`;
            
            const actionBtn = document.getElementById('result-action-btn');
            actionBtn.onclick = function() {
                showDetail(highestCategory);
            };

            document.getElementById('quiz-result').classList.remove('hidden');
        }

        function resetQuiz() {
            currentStep = 0;
            userScores = { to: 0, tm: 0, tt: 0, tkj: 0, rpl: 0, bc: 0, tek: 0 };
            
            document.getElementById('quiz-progress-container').classList.remove('hidden');
            document.getElementById('quiz-content').classList.remove('hidden');
            document.getElementById('quiz-result').classList.add('hidden');

            renderQuestion();
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderQuestion();

            const btnBuka = document.getElementById("btn-buka-katalog");
            const btnKembali = document.getElementById("btn-kembali-store");
            const landing = document.getElementById("store-landing");
            const catalog = document.getElementById("store-catalog");
            const floatingBtn = document.getElementById("floating-cart-btn");

            if (btnBuka) {
                btnBuka.addEventListener("click", function (e) {
                    e.preventDefault();
                    bukaKatalogLangsung();
                });
            }

            if (btnKembali) {
                btnKembali.addEventListener("click", function () {
                    catalog.classList.add("opacity-0", "scale-95");

                    setTimeout(() => {
                        catalog.classList.add("hidden");
                        catalog.classList.remove("opacity-0", "scale-95");
                        catalog.classList.add("opacity-100", "scale-100");
                        floatingBtn.classList.add("hidden");

                        landing.classList.remove("hidden");
                        landing.classList.add("opacity-0", "scale-95");

                        setTimeout(() => {
                            landing.classList.remove("opacity-0", "scale-95");
                            landing.classList.add("opacity-100", "scale-100");
                        }, 50);

                        document.getElementById('katapang-store').scrollIntoView({ behavior: 'smooth' });
                    }, 300);
                });
            }
        });
    </script>
</body>
</html>