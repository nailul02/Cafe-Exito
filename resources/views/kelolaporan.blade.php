<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Laporan & Analitik - Cafe Exito</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-[#fcf8f5] min-h-screen flex text-gray-800">

    <!-- SIDEBAR ADMIN / MANAJEMEN -->
    <aside class="w-64 bg-[#6b5344] text-white flex flex-col p-6 min-h-screen shadow-md justify-between relative z-30">
        <div>
            <!-- Header Profil Admin di Sidebar -->
            <div class="flex items-center gap-3 bg-[#584437] p-3 rounded-2xl mb-6 shadow-inner">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-900 font-bold flex items-center justify-center text-sm shadow">
                    BS
                </div>
                <div>
                    <h1 class="text-sm font-bold leading-tight">Cafe Exito</h1>
                    <p class="text-[10px] text-amber-200">Shift Manager • On Duty</p>
                </div>
            </div>

            <!-- Menu Navigasi Utama -->
            <nav class="flex flex-col gap-1.5 text-xs font-medium">
                <a href="{{ route('dashboard-admin') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>📊</span> Dashboard
                </a>
                <a href="{{ route('kelola-menu') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>☕</span> Kelola Menu
                </a>
                <a href="{{ route('kelola-stok') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center justify-between cursor-pointer">
                    <span class="flex items-center gap-3"><span>📦</span> Kelola Stok</span>
                    <span class="bg-red-600 text-white text-[9px] px-2 py-0.5 rounded-full font-bold">3 Alert</span>
                </a>
                <a href="{{ route('kelola-jadwal-shift') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>📅</span> Kelola Jadwal Shift
                </a>
                <a href="{{ route('kelola-absensi') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>📋</span> Kelola Absensi
                </a>
                <a href="{{ route('kelola-laporan') }}" class="py-2.5 px-4 rounded-xl bg-[#f97316] text-white shadow-sm flex items-center gap-3 cursor-pointer font-semibold">
                    <span>📈</span> Kelola Laporan
                </a>
                <a href="{{ route('profil-akun') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>⚙️</span> Profil Akun
                </a>
            </nav>
        </div>

        <!-- Bagian Bawah Sidebar -->
        <div class="space-y-3 pt-4 border-t border-white/10 text-xs">
            <a href="#" onclick="alert('Membuka pusat bantuan laporan...')" class="flex items-center gap-2 text-gray-200 hover:text-white px-2 py-1">
                <span>❓</span> Bantuan
            </a>
            <a href="{{ route('proses-login') }}" class="w-full py-2 px-3 bg-red-600/20 hover:bg-red-600/40 text-red-200 rounded-xl transition flex items-center justify-center gap-2 font-bold cursor-pointer text-center">
                🚪 Logout
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA KELOLA LAPORAN & ANALITIK -->
    <main class="flex-1 p-6 overflow-y-auto">
        
        <!-- HEADER ATAS (Tombol Export atas dihilangkan agar lebih bersih & terpusat di panel kanan) -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 bg-white p-3.5 rounded-2xl shadow-sm border border-gray-100 gap-3">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700">
                    <span>📍 Operasional Store #01 • Analitik & Rekap Operasional</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700">
                    <span>📅 Kam, 27 Maret 2025 ▾</span>
                </div>
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700">
                    <span>⏱️ Shift Pagi & Siang ▾</span>
                </div>
            </div>
        </header>

        <!-- SUB-HEADER / JUDUL HALAMAN -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 gap-2">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Kelola Laporan & Analitik</h2>
                <p class="text-xs text-gray-400">Rekap komprehensif performa penjualan kasir, mutasi stok bahan, jam kerja & absensi staf.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-full">✔ POS Kasir Online (2 Terminal)</span>
                <span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold rounded-full">1 Shift Berjalan</span>
            </div>
        </div>

        <!-- TAB KATEGORI UTAMA LAPORAN -->
        <div class="flex gap-2 text-xs font-semibold overflow-x-auto pb-2 mb-6">
            <button onclick="gantiTabLaporan Utama('penjualan', this)" id="tabPenjualan" class="px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition cursor-pointer flex items-center gap-2">
                <span>💰 Laporan Penjualan</span> <span class="bg-amber-500 text-gray-950 px-1.5 py-0.5 rounded text-[10px]">POS</span>
            </button>
            <button onclick="gantiTabLaporanUtama('stok', this)" id="tabStok" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer flex items-center gap-2">
                <span>📦 Laporan Stok & Mutasi</span> <span class="bg-red-500 text-white px-1.5 py-0.5 rounded text-[10px]">!</span>
            </button>
            <button onclick="gantiTabLaporanUtama('absensi', this)" id="tabAbsensi" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer">
                <span>📋 Laporan Absensi & Shift</span>
            </button>
        </div>

        <!-- FILTER PERIODE WAKTU -->
        <div class="flex gap-1 bg-white p-1.5 rounded-xl shadow-sm border border-gray-100 mb-6 max-w-xs text-xs font-semibold">
            <button onclick="gantiPeriodeWaktu('hari', this)" id="periodeHari" class="flex-1 py-1.5 text-center rounded-lg text-gray-500 hover:text-gray-900 transition cursor-pointer">Hari Ini</button>
            <button onclick="gantiPeriodeWaktu('minggu', this)" id="periodeMinggu" class="flex-1 py-1.5 text-center rounded-lg text-gray-500 hover:text-gray-900 transition cursor-pointer">Minggu Ini</button>
            <button onclick="gantiPeriodeWaktu('bulan', this)" id="periodeBulan" class="flex-1 py-1.5 text-center rounded-lg bg-gray-900 text-white shadow transition cursor-pointer">Bulan Ini</button>
            <button onclick="gantiPeriodeWaktu('kustom', this)" id="periodeKustom" class="flex-1 py-1.5 text-center rounded-lg text-gray-500 hover:text-gray-900 transition cursor-pointer">Kustom ▾</button>
        </div>

        <!-- GRID KOTAK STATISTIK UTAMA (4 KOTAK) -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Penjualan Kotor</span>
                        <span class="text-[9px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full">+14.2%</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Rp 48.750.000</h2>
                </div>
                <p class="text-[10px] text-gray-400 mt-2 pt-2 border-t border-gray-50">Minggu Ini <span class="text-gray-900 font-semibold">(1.220 Transaksi)</span></p>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-Rata Penjualan / Ticket</span>
                        <span class="text-[9px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">📊</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Rp 34.300 <span class="text-xs font-normal text-gray-500">/ cup</span></h2>
                </div>
                <p class="text-[10px] text-emerald-600 font-semibold mt-2 pt-2 border-t border-gray-50">✔ Target Rp 32.000 tercapai</p>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Keluar Penggunaan Stok</span>
                        <span class="text-[9px] font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">📅</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Rp 14.280.000</h2>
                </div>
                <p class="text-[10px] text-gray-500 mt-2 pt-2 border-t border-gray-50">Margin: <strong class="text-gray-900">70.1%</strong> • FoodCost: <strong class="text-gray-900">29.5%</strong></p>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Akumulasi Jam Kerja Shift</span>
                        <span class="text-[9px] font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">ℹ️</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">1.240 Jam</h2>
                </div>
                <p class="text-[10px] text-gray-500 mt-2 pt-2 border-t border-gray-50"><span class="text-emerald-600 font-bold">96% Efisiensi</span> • 18 Jam Lembur</p>
            </div>
        </div>

        <!-- GRID UTAMA TENGAH (KIRI: GRAFIK & KATEGORI | KANAN: PANEL CETAK & RINGKASAN CEPAT) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <!-- KOLOM KIRI (2 Kolom): GRAFIK TREN PENJUALAN & KATEGORI MENU -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Grafik Tren Penjualan & Jam Sibuk (Peak Hours) -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Tren Penjualan & Jam Sibuk (Peak Hours)</h3>
                            <p class="text-[11px] text-gray-400">Pola transaksi per jam untuk optimasi alokasi bar/karyawan persiapan bahan baku.</p>
                        </div>
                        <div class="flex gap-3 text-[10px] font-semibold">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-[#6b5344] rounded-sm"></span> Volume Penjualan (Rp)</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-amber-500 rounded-sm"></span> Transaksi / Struk</span>
                        </div>
                    </div>

                    <!-- WADAH GRAFIK BATANG (VISUAL INTERAKTIF) -->
                    <div class="h-48 bg-[#faf7f5] rounded-xl border border-dashed border-gray-200 p-4 flex items-end justify-between gap-2 relative">
                        <div class="absolute top-3 right-3 bg-gray-900 text-white text-[10px] px-2.5 py-1 rounded-lg font-bold shadow">
                            Peak Hour: 15:00 - 16:30
                        </div>

                        <!-- Batang Grafik -->
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344]/30 h-16 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">08:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344] h-24 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">09:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344]/40 h-20 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">10:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344]/30 h-16 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">11:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344]/50 h-28 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">12:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344]/60 h-32 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">13:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344] h-36 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">14:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-amber-500 h-44 rounded-t-md relative group">
                                <span class="absolute -top-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[9px] px-1.5 py-0.5 rounded font-bold">PEAK STALL</span>
                            </div>
                            <span class="text-[9px] font-bold text-amber-900">15:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344]/70 h-32 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">16:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344]/40 h-20 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">17:00</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-[#6b5344]/20 h-12 rounded-t-md"></div>
                            <span class="text-[9px] text-gray-500">18:00+</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-xs text-gray-500 pt-3 border-t border-gray-50 mt-3">
                        <span>Waktu Emas Penjualan: <strong class="text-gray-900">08:00 - 10:30 (Espresso/Latte) & 14:00 - 16:30</strong></span>
                        <span class="text-emerald-600 font-bold">2 Barista Wajib Standby</span>
                    </div>
                </div>

                <!-- Penjualan per Kategori & Top 5 Menu Terlaris -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    
                    <!-- Kategori -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-sm font-bold text-gray-900">Penjualan per Kategori</h3>
                                <span class="text-xs text-gray-400 cursor-pointer" title="Info Kategori">ℹ️</span>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <div class="flex justify-between font-bold mb-1">
                                        <span>☕ Coffee & Espresso</span>
                                        <span>Rp 28.275.000 (58%)</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-[#6b5344] h-full" style="width: 58%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between font-bold mb-1">
                                        <span>🧋 Non-Coffee (Tea & Cocoa)</span>
                                        <span>Rp 9.750.000 (20%)</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-amber-500 h-full" style="width: 20%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between font-bold mb-1">
                                        <span>🥐 Pastry & Bakery</span>
                                        <span>Rp 7.312.500 (15%)</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-blue-500 h-full" style="width: 15%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between font-bold mb-1">
                                        <span>📦 Beans / Bag Retail (250g)</span>
                                        <span>Rp 3.412.500 (7%)</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-gray-400 h-full" style="width: 7%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-50 flex justify-between text-[11px] text-gray-400">
                            <span>Total Kategori: 4 Divisi Menu</span>
                            <span class="text-emerald-600 font-bold">100% Tercatat</span>
                        </div>
                    </div>

                    <!-- Top 5 Menu Terlaris -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-sm font-bold text-gray-900">Top 5 Menu Terlaris</h3>
                                <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Terlaris</span>
                            </div>

                            <div class="space-y-2.5 text-xs">
                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded-xl">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-amber-500 text-gray-950 font-bold text-[10px] flex items-center justify-center">1</span>
                                        <div>
                                            <p class="font-bold text-gray-900">Iced Spanish Latte</p>
                                            <p class="text-[9px] text-gray-400">348 cup terjual</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-gray-900">Rp 11.970.000</span>
                                </div>

                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded-xl">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-700 font-bold text-[10px] flex items-center justify-center">2</span>
                                        <div>
                                            <p class="font-bold text-gray-900">Caramel Macchiato</p>
                                            <p class="text-[9px] text-gray-400">284 cup terjual</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-gray-900">Rp 9.940.000</span>
                                </div>

                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded-xl">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-700 font-bold text-[10px] flex items-center justify-center">3</span>
                                        <div>
                                            <p class="font-bold text-gray-900">Manual Brew V60</p>
                                            <p class="text-[9px] text-gray-400">195 cup terjual</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-gray-900">Rp 7.410.000</span>
                                </div>

                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded-xl">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-700 font-bold text-[10px] flex items-center justify-center">4</span>
                                        <div>
                                            <p class="font-bold text-gray-900">Butter Croissant</p>
                                            <p class="text-[9px] text-gray-400">160 pcs terjual</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-gray-900">Rp 4.480.000</span>
                                </div>

                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded-xl">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-700 font-bold text-[10px] flex items-center justify-center">5</span>
                                        <div>
                                            <p class="font-bold text-gray-900">Flat White Double</p>
                                            <p class="text-[9px] text-gray-400">142 cup terjual</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-gray-900">Rp 4.970.000</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-right">
                            <button onclick="alert('Membuka daftar lengkap seluruh 45 menu kafe...')" class="text-[11px] text-[#f97316] font-bold hover:underline">Lihat 40 Menu Lainnya &gt;</button>
                        </div>
                    </div>

                </div>

            </div>

            <!-- KOLOM KANAN (1 Kolom): PANEL CETAK & EKSPOR & RINGKASAN BAHAN -->
            <div class="space-y-6">
                
                <!-- Panel Cetak & Ekspor (Full Interaktif) -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-bold text-gray-900">Cetak & Ekspor</h3>
                        <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Bar Utama</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-3">Format rekap kasir & audit manajer.</p>

                    <div class="space-y-2 mb-4 text-xs">
                        <label class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                            <span class="font-semibold text-gray-800">Laporan Penjualan (POS)</span>
                            <input type="checkbox" checked class="text-[#f97316] rounded w-4 h-4">
                        </label>
                        <label class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                            <span class="font-semibold text-gray-800">Rekap Penggunaan Stok</span>
                            <input type="checkbox" class="text-[#f97316] rounded w-4 h-4">
                        </label>
                        <label class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                            <span class="font-semibold text-gray-800">Rekap Gaji & Lembur Shift</span>
                            <input type="checkbox" class="text-[#f97316] rounded w-4 h-4">
                        </label>
                    </div>

                    <!-- Format Dokumen -->
                    <div class="mb-4">
                        <p class="text-[11px] font-bold text-gray-700 mb-1.5">FORMAT DOKUMEN:</p>
                        <div class="grid grid-cols-3 gap-2 text-xs font-semibold">
                            <button onclick="pilihFormatCetak('thermal', this)" id="btnFmtThermal" class="p-2 bg-gray-900 text-white rounded-xl text-center shadow cursor-pointer">Thermal 80mm</button>
                            <button onclick="pilihFormatCetak('pdf', this)" id="btnFmtPdf" class="p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl text-center cursor-pointer">Lembar PDF A4</button>
                            <button onclick="pilihFormatCetak('excel', this)" id="btnFmtExcel" class="p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl text-center cursor-pointer">Excel (Xlsx)</button>
                        </div>
                    </div>

                    <!-- Tombol Eksekusi Cetak & Unduh -->
                    <div class="space-y-2">
                        <button onclick="cetakRekapLaporanSekarang()" class="w-full py-2.5 bg-[#f97316] hover:bg-[#ea580c] text-white font-bold rounded-xl text-xs transition shadow-md cursor-pointer text-center">
                            🖨️ Cetak Rekap Laporan Sekarang
                        </button>
                        <button onclick="unduhPDFLengkap()" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition cursor-pointer text-center">
                            📥 Unduh PDF Lengkap
                        </button>
                    </div>
                </div>

                <!-- Ringkasan Bahan & Absensi Cepat -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-900">Ringkasan Bahan & Absensi Cepat</h3>
                        <span class="text-[10px] text-gray-400">Shift #2</span>
                    </div>

                    <div class="p-3 bg-red-50/50 rounded-xl border border-red-200 mb-3 text-xs">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-red-700">🚨 3 Bahan Kritis Butuh PO</span>
                            <a href="{{ route('kelola-stok') }}" class="text-[10px] font-bold text-red-600 hover:underline">Kelola Stok &gt;</a>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-gray-700">
                            <li><strong>Biji House Blend (Full Arabica):</strong> Sisa 1.8 Kg</li>
                            <li><strong>Sirup Karamel Monin:</strong> Sisa 1 Btl (100ml)</li>
                            <li><strong>Fresh Milk Greenfields:</strong> Sisa 4 Liter</li>
                        </ul>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-center">
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Kepatuhan Shift</p>
                            <p class="text-lg font-bold text-emerald-600 mt-0.5">98%</p>
                            <p class="text-[9px] text-gray-500">Ketepatan Waktu</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-center">
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Izin Staf</p>
                            <p class="text-lg font-bold text-amber-700 mt-0.5">2 Izin</p>
                            <p class="text-[9px] text-gray-500">Sakit/Bulanan</p>
                        </div>
                    </div>

                    <!-- Status Verifikasi Laporan -->
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs flex justify-between items-center">
                        <div>
                            <p class="font-bold text-emerald-900">Status Verifikasi Laporan:</p>
                            <p class="text-[10px] text-emerald-700">Disetujui oleh Budi Santoso (Shift Manajer • 27 Mar, 16:45 WIB)</p>
                        </div>
                        <span class="text-emerald-600 text-lg font-bold">✔</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- BAGIAN BAWAH: REKAP KASIR & SHIFT BERJALAN HARI INI -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Rekap Kasir & Shift Berjalan Hari Ini</h3>
                    <p class="text-[11px] text-gray-400">Terminal Kasir Utama #01 & #02</p>
                </div>
                <button onclick="alert('Membuka audit log transaksi mendetail kasir...')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                    Audit Log Kasir &gt;
                </button>
            </div>

            <!-- Tabel Kasir Shift -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-400 font-bold uppercase tracking-wider border-b border-gray-100">
                            <th class="py-2.5 px-3">Shift</th>
                            <th class="py-2.5 px-3">Kasir Bertugas</th>
                            <th class="py-2.5 px-3">Waktu Tugas</th>
                            <th class="py-2.5 px-3">Transaksi</th>
                            <th class="py-2.5 px-3">Total Omset</th>
                            <th class="py-2.5 px-3 text-center">Status Kas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                        <tr>
                            <td class="py-3 px-3"><span class="px-2 py-0.5 bg-amber-100 text-amber-900 font-bold rounded text-[10px]">Shift Pagi</span></td>
                            <td class="py-3 px-3 font-bold text-gray-900">Siti Rahmawati (Kasir 1)</td>
                            <td class="py-3 px-3">07:00 - 15:00 (Selesai)</td>
                            <td class="py-3 px-3">68 Struk</td>
                            <td class="py-3 px-3 font-bold text-gray-900">Rp 2.450.000</td>
                            <td class="py-3 px-3 text-center"><span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">✔ Klop (Sesuai)</span></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-3"><span class="px-2 py-0.5 bg-blue-100 text-blue-900 font-bold rounded text-[10px]">Shift Siang</span></td>
                            <td class="py-3 px-3 font-bold text-gray-900">Dimas Arya (Kasir 2)</td>
                            <td class="py-3 px-3">15:00 - Sekarang (Aktif)</td>
                            <td class="py-3 px-3">57 Struk</td>
                            <td class="py-3 px-3 font-bold text-gray-900">Rp 1.050.000</td>
                            <td class="py-3 px-3 text-center"><span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-full text-[10px]">⏳ Berjalan</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        // 1. Tab Kategori Laporan Utama
        function gantiTabLaporanUtama(jenis, element) {
            document.getElementById('tabPenjualan').className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer flex items-center gap-2";
            document.getElementById('tabStok').className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer flex items-center gap-2";
            document.getElementById('tabAbsensi').className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer";

            if (jenis === 'penjualan') {
                element.className = "px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition cursor-pointer flex items-center gap-2 font-bold";
            } else if (jenis === 'stok') {
                element.className = "px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition cursor-pointer flex items-center gap-2 font-bold";
            } else {
                element.className = "px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition cursor-pointer font-bold";
            }

            alert(`📈 Memuat laporan analitik untuk kategori: [ ${jenis.toUpperCase()} ]`);
        }

        // 2. Filter Periode Waktu
        function gantiPeriodeWaktu(periode, element) {
            document.getElementById('periodeHari').className = "flex-1 py-1.5 text-center rounded-lg text-gray-500 hover:text-gray-900 transition cursor-pointer";
            document.getElementById('periodeMinggu').className = "flex-1 py-1.5 text-center rounded-lg text-gray-500 hover:text-gray-900 transition cursor-pointer";
            document.getElementById('periodeBulan').className = "flex-1 py-1.5 text-center rounded-lg text-gray-500 hover:text-gray-900 transition cursor-pointer";
            document.getElementById('periodeKustom').className = "flex-1 py-1.5 text-center rounded-lg text-gray-500 hover:text-gray-900 transition cursor-pointer";

            element.className = "flex-1 py-1.5 text-center rounded-lg bg-gray-900 text-white shadow transition cursor-pointer font-bold";
            alert(`📅 Rentang waktu laporan diubah ke: ${periode.toUpperCase()}`);
        }

        // 3. Format Dokumen Cetak
        function pilihFormatCetak(format, element) {
            document.getElementById('btnFmtThermal').className = "p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl text-center cursor-pointer";
            document.getElementById('btnFmtPdf').className = "p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl text-center cursor-pointer";
            document.getElementById('btnFmtExcel').className = "p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl text-center cursor-pointer";

            element.className = "p-2 bg-gray-900 text-white rounded-xl text-center shadow cursor-pointer font-bold";
        }

        // 4. Aksi Cetak & Unduh
        function cetakRekapLaporanSekarang() {
            const konfirmasi = confirm("🖨️ Apakah Anda ingin segera mencetak rekapitulasi laporan operasional terpilih ke printer thermal / utama?");
            if (konfirmasi) {
                alert("✅ Dokumen laporan berhasil dikirim ke perangkat cetak kafe!");
            }
        }

        function unduhPDFLengkap() {
            alert("📥 Menyiapkan file unduhan Laporan Lengkap Berformat PDF A4 (Termasuk Grafik & Analitik)...");
        }
    </script>

</body>
</html>