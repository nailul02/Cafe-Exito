<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Exito Cafe</title>
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

    <!-- SIDEBAR OWNER (Diberi relative z-30 dan rute aktif agar bisa diklik lancar) -->
    <aside class="w-64 bg-[#6b5344] text-white flex flex-col p-6 min-h-screen shadow-md justify-between relative z-30">
        <div>
            <!-- Logo & Nama Cafe -->
            <div class="flex items-center gap-3 mb-10">
                <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-gray-800 font-serif italic font-bold shadow-md">
                    exito
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-wide">Cafe Exito</h1>
                    <p class="text-xs text-amber-200">Owner Dashboard</p>
                </div>
            </div>

            <!-- Profil User Owner -->
            <div class="flex items-center gap-3 bg-[#584437] p-3 rounded-xl mb-8">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-900 font-bold flex items-center justify-center text-sm">
                    BS
                </div>
                <div>
                    <p class="text-sm font-bold leading-tight">Budi Santoso</p>
                    <p class="text-xs text-amber-200">Owner</p>
                </div>
            </div>

            <!-- Menu Navigasi Owner -->
            <nav class="flex flex-col gap-2 text-sm font-medium">
                <a href="{{ route('owner-dashboard') }}" class="py-2.5 px-4 rounded-xl {{ Request::routeIs('owner-dashboard') ? 'bg-[#584437] text-white shadow-sm' : 'hover:bg-[#584437]/50 text-gray-200 transition' }} flex items-center gap-3 cursor-pointer">
                    📊 Dashboard
                </a>
                <a href="{{ route('laporan-penjualan') }}" class="py-2.5 px-4 rounded-xl {{ Request::routeIs('laporan-penjualan') ? 'bg-[#584437] text-white shadow-sm' : 'hover:bg-[#584437]/50 text-gray-200 transition' }} flex items-center gap-3 cursor-pointer">
                    📈 Laporan Penjualan
                </a>
                <a href="{{ route('stok-bahan') }}" class="py-2.5 px-4 rounded-xl {{ Request::routeIs('stok-bahan') ? 'bg-[#584437] text-white shadow-sm' : 'hover:bg-[#584437]/50 text-gray-200 transition' }} flex items-center justify-between cursor-pointer">
                    <span class="flex items-center gap-3">📦 Stok Bahan</span>
                    <span class="bg-red-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">3 Kritis</span>
                </a>
                <a href="{{ route('profil-akun') }}" class="py-2.5 px-4 rounded-xl {{ Request::routeIs('profil-akun') ? 'bg-[#584437] text-white shadow-sm' : 'hover:bg-[#584437]/50 text-gray-200 transition' }} flex items-center gap-3 cursor-pointer">
                    ⚙️ Profil Akun
                </a>
            </nav>
        </div>

        <!-- Tombol Bawah Sidebar -->
        <div class="space-y-3 pt-6 border-t border-white/10">
            <a href="{{ route('dashboard') }}" class="w-full py-2.5 px-4 bg-[#8b6f5e] hover:bg-[#7a5f4e] text-white text-xs font-bold rounded-xl shadow transition flex items-center justify-center gap-2 cursor-pointer text-center">
                ☕ Buka Kasir POS
            </a>
            <a href="{{ route('proses-login') }}" class="block text-center text-xs text-red-200 hover:text-white transition py-1 cursor-pointer">
                🚪 Logout
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-8 overflow-y-auto relative z-10">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-8 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700">
                    <span>⏳ Shift Berjalan: <strong class="text-gray-900">Shift Pagi (08:00 - 16:00)</strong></span>
                </div>
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700">
                    <span>📅 Hari Ini, 24 Mei 2025</span>
                </div>
            </div>
            
            <!-- Tombol Kanan Atas dengan Dropdown Cetak & Ekspor -->
            <div class="flex items-center gap-3 relative">
                <button class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 relative text-gray-600 transition">
                    🔔 <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                
                <div class="relative">
                    <button onclick="toggleDropdownCetak()" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-2">
                        🖨️ Cetak & Ekspor ▾
                    </button>
                    <!-- Dropdown Menu -->
                    <div id="dropdownCetak" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-20 text-xs font-medium">
                        <button onclick="jalankanAksiCetak('pdf')" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">📄 Cetak Dokumen PDF</button>
                        <button onclick="jalankanAksiCetak('excel')" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">📊 Ekspor Data Excel</button>
                        <button onclick="jalankanAksiCetak('print')" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">🖨️ Cetak Langsung (Print)</button>
                    </div>
                </div>
            </div>
        </header>

        <!-- GRID KARTU STATISTIK UTAMA (4 KOTAK) -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-5 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Penjualan Hari Ini</span>
                        <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full">+12,4%</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Rp 6.840.000</h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-4 pt-3 border-t border-gray-50">142 Transaksi <span class="text-gray-600 font-semibold">vs kemarin (Rp 6.08M)</span></p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Pendapatan Bersih</span>
                        <span class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">Margin 71,9%</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Rp 4.920.000</h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-4 pt-3 border-t border-gray-50">HPP: Rp 1.920.000 <span class="text-emerald-600 font-semibold">Target tercapai 88%</span></p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Kehadiran Karyawan</span>
                        <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full">On-time</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">8 / 8 Hadir</h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-4 pt-3 border-t border-gray-50">Semua shift lengkap <span class="text-gray-900 font-semibold">0 Terlambat</span></p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status Stok Kritis</span>
                        <span class="text-[10px] font-bold bg-red-50 text-red-600 px-2 py-0.5 rounded-full">🚨 Perlu Order</span>
                    </div>
                    <h2 class="text-2xl font-bold text-red-600 tracking-tight">3 Bahan</h2>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-50 flex justify-between items-center text-[11px]">
                    <span class="text-gray-500">Arabica, Oat, Sirup</span>
                    <button onclick="bukaModalStokKritis()" class="text-red-600 font-bold hover:underline bg-transparent border-0 cursor-pointer">Lihat &gt;</button>
                </div>
            </div>
        </div>

        <!-- GRID BAGIAN TENGAH (GRAFIK BERBAGAI TAMPILAN) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Tren Penjualan & Pendapatan</h3>
                        <p id="subTeksTren" class="text-xs text-gray-400">Pola transaksi per jam hari ini (Peak: 12:00 - 14:00 Lunch Rush)</p>
                    </div>
                    <!-- Tombol Filter Interaktif (Harian, Mingguan, Bulanan) -->
                    <div class="bg-gray-100 p-1 rounded-xl flex text-xs font-semibold gap-1">
                        <button onclick="gantiFilterTren('harian', this)" id="btnHarian" class="px-3 py-1 bg-white shadow-sm rounded-lg text-gray-950 transition font-bold">Harian</button>
                        <button onclick="gantiFilterTren('mingguan', this)" id="btnMingguan" class="px-3 py-1 text-gray-500 hover:text-gray-950 rounded-lg transition">Mingguan</button>
                        <button onclick="gantiFilterTren('bulanan', this)" id="btnBulanan" class="px-3 py-1 text-gray-500 hover:text-gray-950 rounded-lg transition">Bulanan</button>
                    </div>
                </div>

                <!-- WADAH GRAFIK VISUAL (SVG DINAMIS) -->
                <div class="h-56 bg-[#faf7f5] rounded-xl border border-dashed border-gray-200 flex flex-col justify-between relative overflow-hidden my-3 p-4">
                    
                    <!-- Tooltip Peak / Puncak Grafik -->
                    <div id="tooltipGrafik" class="absolute left-1/2 -translate-x-1/2 top-3 bg-gray-900 text-white text-[10px] px-3 py-1.5 rounded-lg shadow-md text-center z-10 transition-all duration-300">
                        <p id="labelGrafikPuncak" class="text-amber-300 font-semibold">Peak: 13:00 WIB</p>
                        <p id="nilaiGrafikPuncak" class="text-sm font-bold">Rp 1.420.000</p>
                        <p id="detailGrafikPuncak" class="text-[9px] text-gray-300">38 Cup Terjual</p>
                    </div>

                    <!-- SVG GRAFIK AREA & KURVA -->
                    <div class="w-full h-36 relative mt-6">
                        <svg class="w-full h-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 500 120">
                            <!-- Gradient Warna Area -->
                            <defs>
                                <linearGradient id="gradPenjualan" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#6b5344" stop-opacity="0.35"/>
                                    <stop offset="100%" stop-color="#6b5344" stop-opacity="0.0"/>
                                </linearGradient>
                                <linearGradient id="gradPendapatan" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#059669" stop-opacity="0.25"/>
                                    <stop offset="100%" stop-color="#059669" stop-opacity="0.0"/>
                                </linearGradient>
                            </defs>

                            <!-- Area Isian Bawah Kurva -->
                            <path id="pathAreaGross" d="M 0,90 Q 120,40 250,15 T 500,70 L 500,120 L 0,120 Z" fill="url(#gradPenjualan)" class="transition-all duration-500"/>
                            <path id="pathAreaNet" d="M 0,105 Q 120,60 250,30 T 500,85 L 500,120 L 0,120 Z" fill="url(#gradPendapatan)" class="transition-all duration-500"/>

                            <!-- Garis Kurva Utama (Gross - Cokelat) -->
                            <path id="pathLineGross" d="M 0,90 Q 120,40 250,15 T 500,70" fill="none" stroke="#6b5344" stroke-width="3" stroke-linecap="round" class="transition-all duration-500"/>
                            
                            <!-- Garis Kurva Kedua (Net - Hijau) -->
                            <path id="pathLineNet" d="M 0,105 Q 120,60 250,30 T 500,85" fill="none" stroke="#059669" stroke-width="2.5" stroke-dasharray="4" stroke-linecap="round" class="transition-all duration-500"/>

                            <!-- Titik Puncak (Peak Circle) -->
                            <circle id="dotPeak" cx="250" cy="15" r="5" fill="#f59e0b" stroke="#ffffff" stroke-width="2" class="transition-all duration-500 animate-bounce"/>
                        </svg>
                    </div>

                    <!-- Label Sumbu X (Dinamis) -->
                    <div id="sumbuXContainer" class="flex justify-between items-center text-[10px] font-semibold text-gray-500 pt-2 border-t border-gray-200">
                        <span>08:00</span>
                        <span>10:00</span>
                        <span>12:00</span>
                        <span class="text-[#6b5344] font-bold">14:00 (Aktif)</span>
                        <span>16:00</span>
                        <span>18:00</span>
                        <span>20:00</span>
                        <span>22:00</span>
                    </div>

                </div>

                <div class="flex justify-between items-center text-xs text-gray-500 pt-2 border-t border-gray-50">
                    <div class="flex gap-4">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#6b5344]"></span> Penjualan Kotor (Gross)</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Pendapatan Bersih (Net)</span>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-400">Akurasi Data: Real-time Cloud Sync</span>
                </div>
            </div>

            <!-- Monitoring Shift -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Monitoring Shift & Kehadiran</h3>
                            <p class="text-xs text-gray-400">Shift Pagi • 4 Staff Aktif di Bar</p>
                        </div>
                        <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full">Semua Hadir</span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-xl border border-gray-100 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-gray-800 text-white font-bold flex items-center justify-center text-[10px]">RA</div>
                                <div>
                                    <p class="font-bold text-gray-900">Rian Ardiansyah</p>
                                    <p class="text-[10px] text-gray-400">Head Barista • In: 07:45</p>
                                </div>
                            </div>
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Sedang Bertugas</span>
                        </div>

                        <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-xl border border-gray-100 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-900 font-bold flex items-center justify-center text-[10px]">DN</div>
                                <div>
                                    <p class="font-bold text-gray-900">Dinda Novita</p>
                                    <p class="text-[10px] text-gray-400">Kasir Utama • In: 07:50</p>
                                </div>
                            </div>
                            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Sedang Bertugas</span>
                        </div>

                        <div class="flex justify-between items-center p-2.5 bg-gray-50 rounded-xl border border-gray-100 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-900 font-bold flex items-center justify-center text-[10px]">FY</div>
                                <div>
                                    <p class="font-bold text-gray-900">Fajar Yulianto</p>
                                    <p class="text-[10px] text-gray-400">Junior Barista • In: 07:55</p>
                                </div>
                            </div>
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Istirahat (15m)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center text-xs">
                    <span class="text-gray-500 font-semibold">Serah Terima Shift Score:</span>
                    <a href="#" class="text-amber-800 font-bold hover:underline">15:45 WIB Kelola &gt;</a>
                </div>
            </div>

        </div>

        <!-- GRID BAGIAN BAWAH -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">☕ Menu Terlaris Hari Ini</h3>
                        <p class="text-xs text-gray-400">Kontribusi 64% total revenue</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-900 font-bold text-xs flex items-center justify-center">1</span>
                            <div>
                                <p class="text-xs font-bold text-gray-900">Signature Exito Latte</p>
                                <p class="text-[10px] text-gray-400">Double ristretto, brown sugar, whole milk</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-gray-900">Rp 2.030.000</p>
                            <span class="text-[10px] text-emerald-600 font-semibold">58 Cup</span>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-gray-200 text-gray-700 font-bold text-xs flex items-center justify-center">2</span>
                            <div>
                                <p class="text-xs font-bold text-gray-900">Caramel Macchiato</p>
                                <p class="text-[10px] text-gray-400">Vanilla syrup, steamed milk, caramel</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-gray-900">Rp 1.368.000</p>
                            <span class="text-[10px] text-emerald-600 font-semibold">36 Cup</span>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-gray-200 text-gray-700 font-bold text-xs flex items-center justify-center">3</span>
                            <div>
                                <p class="text-xs font-bold text-gray-900">Butter Croissant</p>
                                <p class="text-[10px] text-gray-400">Flaky French butter pastry freshly baked</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-gray-900">Rp 812.000</p>
                            <span class="text-[10px] text-emerald-600 font-semibold">29 Pcs</span>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-gray-200 text-gray-700 font-bold text-xs flex items-center justify-center">4</span>
                            <div>
                                <p class="text-xs font-bold text-gray-900">Iced Americano</p>
                                <p class="text-[10px] text-gray-400">Blend Flores Bajawa & Gayo cold extracted</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-gray-900">Rp 650.000</p>
                            <span class="text-[10px] text-emerald-600 font-semibold">25 Cup</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Peringatan Stok Menipis -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-gray-900">📦 Peringatan Stok Menipis</h3>
                        <button onclick="bukaModalStokKritis()" class="text-xs text-amber-800 font-bold hover:underline bg-transparent border-0 cursor-pointer">Lihat Stok Lengkap</button>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-4">Bahan berikut telah mencapai batas minimum reorder point (ROP).</p>

                    <div class="space-y-3">
                        <div class="p-3 bg-red-50/50 rounded-xl border border-red-100 flex justify-between items-center">
                            <div>
                                <p class="text-xs font-bold text-gray-900">Biji Kopi Arabica House Blend</p>
                                <p class="text-[10px] text-red-600 font-semibold">Sisa: 1.8 kg • Batas Min: 5.0 kg</p>
                            </div>
                            <button onclick="prosesPemesananSupplier('Biji Kopi Arabica House Blend')" class="px-2.5 py-1 bg-gray-900 hover:bg-black text-white text-[10px] font-bold rounded-lg shadow transition cursor-pointer">
                                🛒 Order
                            </button>
                        </div>

                        <div class="p-3 bg-red-50/50 rounded-xl border border-red-100 flex justify-between items-center">
                            <div>
                                <p class="text-xs font-bold text-gray-900">Susu Oat Barista Edition</p>
                                <p class="text-[10px] text-red-600 font-semibold">Sisa: 3 Liter • Batas Min: 12 Liter</p>
                            </div>
                            <button onclick="prosesPemesananSupplier('Susu Oat Barista Edition')" class="px-2.5 py-1 bg-gray-900 hover:bg-black text-white text-[10px] font-bold rounded-lg shadow transition cursor-pointer">
                                🛒 Order
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-400 text-center">
                    Cafe Exito POS Cloud System v2.4
                </div>
            </div>

        </div>

    </main>

    <!-- MODAL / POP-UP HALAMAN STOK KRITIS -->
    <div id="modalStokKritis" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-900">🚨 Detail Lengkap Status Stok Kritis</h3>
                <button onclick="tutupModalStokKritis()" class="text-gray-400 hover:text-gray-700 font-bold text-lg">✕</button>
            </div>
            <p class="text-xs text-gray-500 mb-4">Daftar inventaris bahan baku kafe yang membutuhkan restock segera:</p>
            <div class="space-y-3 text-xs">
                <div class="p-3 bg-red-50 rounded-xl border border-red-200 flex justify-between items-center">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">1. Biji Kopi Arabica House Blend</p>
                        <p class="text-red-600 font-semibold mt-0.5">Sisa Stok: 1.8 kg (Batas Minimum: 5.0 kg)</p>
                    </div>
                    <span class="bg-red-600 text-white px-2.5 py-1 rounded-lg font-bold">Kritis</span>
                </div>
                <div class="p-3 bg-red-50 rounded-xl border border-red-200 flex justify-between items-center">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">2. Susu Oat Barista Edition</p>
                        <p class="text-red-600 font-semibold mt-0.5">Sisa Stok: 3 Liter (Batas Minimum: 12 Liter)</p>
                    </div>
                    <span class="bg-red-600 text-white px-2.5 py-1 rounded-lg font-bold">Kritis</span>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 flex justify-between items-center">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">3. Sirup Vanille Gourmet 750ml</p>
                        <p class="text-amber-700 font-semibold mt-0.5">Sisa Stok: 2 Botol (Batas Minimum: 4 Botol)</p>
                    </div>
                    <span class="bg-amber-600 text-white px-2.5 py-1 rounded-lg font-bold">Perhatian</span>
                </div>
            </div>
            <div class="mt-6 text-right">
                <button onclick="tutupModalStokKritis()" class="px-5 py-2 bg-gray-900 text-white text-xs font-bold rounded-xl shadow hover:bg-black transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        // 1. Dropdown Cetak & Ekspor
        function toggleDropdownCetak() {
            const dropdown = document.getElementById('dropdownCetak');
            dropdown.classList.toggle('hidden');
        }

        function jalankanAksiCetak(jenis) {
            document.getElementById('dropdownCetak').classList.add('hidden');
            if (jenis === 'pdf') {
                alert("📄 Mengunduh Laporan Pendapatan Berformat PDF...");
            } else if (jenis === 'excel') {
                alert("📊 Mengekspor Data Transaksi ke Spreadsheet Excel...");
            } else if (jenis === 'print') {
                window.print();
            }
        }

        window.addEventListener('click', function(e) {
            if (!e.target.closest('button')) {
                const dropdown = document.getElementById('dropdownCetak');
                if (dropdown && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            }
        });

        // 2. FUNGSI LOGIKA GRAFIK HARIAN, MINGGUAN, & BULANAN
        function gantiFilterTren(periode, element) {
            document.getElementById('btnHarian').className = "px-3 py-1 text-gray-500 hover:text-gray-950 rounded-lg transition";
            document.getElementById('btnMingguan').className = "px-3 py-1 text-gray-500 hover:text-gray-950 rounded-lg transition";
            document.getElementById('btnBulanan').className = "px-3 py-1 text-gray-500 hover:text-gray-950 rounded-lg transition";
            
            element.className = "px-3 py-1 bg-white shadow-sm rounded-lg text-gray-950 font-bold transition";

            const subTeks = document.getElementById('subTeksTren');
            const labelPuncak = document.getElementById('labelGrafikPuncak');
            const nilaiPuncak = document.getElementById('nilaiGrafikPuncak');
            const detailPuncak = document.getElementById('detailGrafikPuncak');
            
            const lineGross = document.getElementById('pathLineGross');
            const areaGross = document.getElementById('pathAreaGross');
            const lineNet = document.getElementById('pathLineNet');
            const areaNet = document.getElementById('pathAreaNet');
            const dotPeak = document.getElementById('dotPeak');
            const tooltipGrafik = document.getElementById('tooltipGrafik');
            const sumbuXContainer = document.getElementById('sumbuXContainer');

            if (periode === 'harian') {
                subTeks.textContent = "Pola transaksi per jam hari ini (Peak: 12:00 - 14:00 Lunch Rush)";
                labelPuncak.textContent = "Peak: 13:00 WIB";
                nilaiPuncak.textContent = "Rp 1.420.000";
                detailPuncak.textContent = "38 Cup Terjual";

                lineGross.setAttribute('d', "M 0,90 Q 120,40 250,15 T 500,70");
                areaGross.setAttribute('d', "M 0,90 Q 120,40 250,15 T 500,70 L 500,120 L 0,120 Z");
                lineNet.setAttribute('d', "M 0,105 Q 120,60 250,30 T 500,85");
                areaNet.setAttribute('d', "M 0,105 Q 120,60 250,30 T 500,85 L 500,120 L 0,120 Z");

                dotPeak.setAttribute('cx', "250");
                dotPeak.setAttribute('cy', "15");
                tooltipGrafik.style.left = "50%";

                sumbuXContainer.innerHTML = `
                    <span>08:00</span>
                    <span>10:00</span>
                    <span>12:00</span>
                    <span class="text-[#6b5344] font-bold">14:00 (Aktif)</span>
                    <span>16:00</span>
                    <span>18:00</span>
                    <span>20:00</span>
                    <span>22:00</span>
                `;
            } else if (periode === 'mingguan') {
                subTeks.textContent = "Akumulasi tren pendapatan per hari dalam seminggu terakhir";
                labelPuncak.textContent = "Hari Teramai: Sabtu";
                nilaiPuncak.textContent = "Rp 8.450.000";
                detailPuncak.textContent = "215 Cup Terjual";

                lineGross.setAttribute('d', "M 0,80 Q 100,95 200,50 T 410,10 L 500,60");
                areaGross.setAttribute('d', "M 0,80 Q 100,95 200,50 T 410,10 L 500,60 L 500,120 L 0,120 Z");
                lineNet.setAttribute('d', "M 0,95 Q 100,110 200,70 T 410,25 L 500,80");
                areaNet.setAttribute('d', "M 0,95 Q 100,110 200,70 T 410,25 L 500,80 L 500,120 L 0,120 Z");

                dotPeak.setAttribute('cx', "410");
                dotPeak.setAttribute('cy', "10");
                tooltipGrafik.style.left = "82%";

                sumbuXContainer.innerHTML = `
                    <span>Senin</span>
                    <span>Selasa</span>
                    <span>Rabu</span>
                    <span>Kamis</span>
                    <span>Jumat</span>
                    <span class="text-[#6b5344] font-bold">Sabtu (Peak)</span>
                    <span>Minggu</span>
                `;
            } else if (periode === 'bulanan') {
                subTeks.textContent = "Rekapitulasi total omzet penjualan sepanjang bulan berjalan";
                labelPuncak.textContent = "Minggu Terproduktif: M2";
                nilaiPuncak.textContent = "Rp 54.200.000";
                detailPuncak.textContent = "1.420 Cup Terjual";

                lineGross.setAttribute('d', "M 0,100 Q 80,100 150,10 T 350,60 L 500,40");
                areaGross.setAttribute('d', "M 0,100 Q 80,100 150,10 T 350,60 L 500,40 L 500,120 L 0,120 Z");
                lineNet.setAttribute('d', "M 0,110 Q 80,110 150,25 T 350,80 L 500,55");
                areaNet.setAttribute('d', "M 0,110 Q 80,110 150,25 T 350,80 L 500,55 L 500,120 L 0,120 Z");

                dotPeak.setAttribute('cx', "150");
                dotPeak.setAttribute('cy', "10");
                tooltipGrafik.style.left = "30%";

                sumbuXContainer.innerHTML = `
                    <span>Minggu 1</span>
                    <span class="text-[#6b5344] font-bold">Minggu 2 (Peak)</span>
                    <span>Minggu 3</span>
                    <span>Minggu 4</span>
                `;
            }
        }

        // 3. Fungsionalitas Modal Stok Kritis
        function bukaModalStokKritis() {
            document.getElementById('modalStokKritis').classList.remove('hidden');
            document.getElementById('modalStokKritis').classList.add('flex');
        }

        function tutupModalStokKritis() {
            document.getElementById('modalStokKritis').classList.remove('flex');
            document.getElementById('modalStokKritis').classList.add('hidden');
        }

        // 4. Tombol Order Supplier
        function prosesPemesananSupplier(namaBahan) {
            const konfirmasi = confirm(`🛒 Apakah Anda ingin segera membuat Purchase Order (PO) untuk bahan [ ${namaBahan} ] ke supplier utama?`);
            if (konfirmasi) {
                alert(`✅ Pesanan bahan [ ${namaBahan} ] berhasil dikirim ke supplier! Notifikasi PO telah dibuat.`);
            }
        }
    </script>

</body>
</html>