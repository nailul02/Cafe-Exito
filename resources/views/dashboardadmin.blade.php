<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Cafe Exito</title>
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
                    GE
                </div>
                <div>
                    <h1 class="text-sm font-bold leading-tight">Cafe Exito</h1>
                    <p class="text-[10px] text-amber-200">Shift Manager & Admin</p>
                </div>
            </div>

            <!-- Menu Navigasi Utama -->
            <nav class="flex flex-col gap-1.5 text-xs font-medium">
                <a href="{{ route('dashboard-admin') }}" class="py-2.5 px-4 rounded-xl bg-[#f97316] text-white shadow-sm flex items-center gap-3 font-semibold cursor-pointer">
                    <span>📊</span> Dashboard
                </a>
                <a href="{{ route('kelola-menu') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>☕</span> Kelola Menu
                </a>
                <a href="{{ route('kelola-stok') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center justify-between cursor-pointer">
                    <span class="flex items-center gap-3"><span>📦</span> Kelola Stok</span>
                    <span class="bg-red-600 text-white text-[9px] px-2 py-0.5 rounded-full font-bold">3 Kritis</span>
                </a>
                <a href="{{ route('kelola-jadwal-shift') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>📅</span> Kelola Jadwal Shift
                </a>
                <a href="{{ route('kelola-absensi') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>📋</span> Kelola Absensi
                </a>
                <a href="{{ route('kelola-laporan') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>📈</span> Kelola Laporan
                </a>
                <a href="{{ route('profil-akun') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>⚙️</span> Profil Akun
                </a>
            </nav>
        </div>

        <!-- Bagian Bawah Sidebar -->
        <div class="space-y-3 pt-4 border-t border-white/10 text-xs">
            <a href="#" onclick="alert('Membuka panel bantuan sistem...')" class="flex items-center gap-2 text-gray-200 hover:text-white px-2 py-1">
                <span>❓</span> Bantuan
            </a>
            <a href="#" onclick="alert('Membuka panel pengaturan sistem...')" class="flex items-center gap-2 text-gray-200 hover:text-white px-2 py-1">
                <span>⚙️</span> Pengaturan
            </a>
            <a href="{{ route('proses-login') }}" class="w-full py-2 px-3 bg-red-600/20 hover:bg-red-600/40 text-red-200 rounded-xl transition flex items-center justify-center gap-2 font-bold cursor-pointer text-center">
                🚪 Logout
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA DASHBOARD ADMIN -->
    <main class="flex-1 p-6 overflow-y-auto">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-6 bg-white p-3.5 rounded-2xl shadow-sm border border-gray-100">
            <!-- Kolom Pencarian -->
            <div class="relative w-72">
                <input type="text" id="inputCariGlobal" onkeyup="cariDataGlobal()" placeholder="Cari pesanan, menu, staf..." class="w-full pl-9 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                <span class="absolute left-3 top-2 text-gray-400 text-xs">🔍</span>
            </div>

            <!-- Informasi Shift & Tanggal Berjalan -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700">
                    <span>⏳ Shift Pagi: <strong class="text-gray-900">08:00 - 16:00 WIB</strong></span>
                </div>
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700">
                    <span>📅 23 Feb 2025</span>
                </div>
                <button onclick="toggleDropdownCetakAtas()" class="px-4 py-1.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                    🖨️ Cetak Laporan ▾
                </button>
                <!-- Dropdown Cetak Atas -->
                <div id="dropdownCetakAtas" class="hidden absolute right-12 mt-20 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-20 text-xs font-medium">
                    <button onclick="jalankanAksiCetak('harian')" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">📄 Cetak Ringkasan Harian</button>
                    <button onclick="jalankanAksiCetak('shift')" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">📊 Cetak Absensi Shift</button>
                </div>
                <button onclick="alert('Ada 1 pemberitahuan sistem baru.')" class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 relative text-gray-600">
                    🔔 <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden flex items-center justify-center font-bold text-xs text-gray-700">
                    GE
                </div>
            </div>
        </header>

        <!-- 4 KARTU STATISTIK UTAMA -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <!-- Kotak 1 -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Penjualan Hari Ini</span>
                        <span class="text-[9px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full">+12,4%</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Rp 4.850.000</h2>
                </div>
                <p class="text-[10px] text-gray-400 mt-2 pt-2 border-t border-gray-50">vs Kemarin (Rp 4.32M) <span class="text-emerald-600 font-semibold">+12%</span></p>
            </div>

            <!-- Kotak 2 -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pesanan Aktif / Total</span>
                        <span class="text-[9px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">Progres Aktif</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">8 <span class="text-xs font-normal text-gray-500">Antrean Masuk</span></h2>
                </div>
                <div class="flex gap-2 text-[10px] text-gray-500 mt-2 pt-2 border-t border-gray-50 font-medium">
                    <span class="text-red-600 font-bold">4 Menunggu</span> • <span class="text-amber-600 font-bold">2 Sedang Diracik</span>
                </div>
            </div>

            <!-- Kotak 3 -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-red-600 uppercase tracking-wider">Status Bahan Kritis</span>
                        <span class="text-[9px] font-bold bg-red-50 text-red-600 px-2 py-0.5 rounded-full">🚨 Perlu Order</span>
                    </div>
                    <h2 class="text-xl font-bold text-red-600 tracking-tight">3 <span class="text-xs font-normal text-gray-500">Bahan Baku</span></h2>
                </div>
                <p class="text-[10px] text-gray-500 mt-2 pt-2 border-t border-gray-50 truncate">Arabica, Greenfields, Sirup Gourmet</p>
            </div>

            <!-- Kotak 4 -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kehadiran Staf (On-time)</span>
                        <span class="text-[9px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full">100% Hadir</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">8 / 8 <span class="text-xs font-normal text-gray-500">Lengkap</span></h2>
                </div>
                <p class="text-[10px] text-gray-500 mt-2 pt-2 border-t border-gray-50">Lengkap, 4 Barista, 2 Kasir, 2 Koki</p>
            </div>
        </div>

        <!-- GRID UTAMA TENGAH (KIRI: PANTAU PESANAN | KANAN: STOK KRITIS) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <!-- KOLOM KIRI (2 Kolom): PANTAU PESANAN MASUK -->
            <div class="lg:col-span-2 bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Pantau Pesanan Masuk (Real-time)</h3>
                            <p class="text-[11px] text-gray-400">Kelola pesanan yang sedang diproses bar, meja, atau takeaway.</p>
                        </div>
                        <!-- Filter Tab Pesanan -->
                        <div class="flex bg-gray-100 p-1 rounded-xl text-xs font-semibold gap-1">
                            <button onclick="filterPesananStatus('semua', this)" id="tabSemua" class="px-3 py-1 bg-white shadow-sm rounded-lg text-gray-900 transition">Semua (8)</button>
                            <button onclick="filterPesananStatus('diracik', this)" id="tabDiracik" class="px-3 py-1 text-gray-500 hover:text-gray-900 rounded-lg transition">Diracik (3)</button>
                            <button onclick="filterPesananStatus('siap', this)" id="tabSiap" class="px-3 py-1 text-gray-500 hover:text-gray-900 rounded-lg transition">Siap (2)</button>
                        </div>
                    </div>

                    <!-- KARTU-KARTU PESANAN GRID (4 KARTU) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mb-2" id="kontainerKartuPesanan">
                        
                        <!-- Pesanan 1 -->
                        <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80 flex flex-col justify-between item-pesanan" data-status="diracik">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">#EX-1042</p>
                                        <p class="text-[10px] text-gray-500">Meja 05</p>
                                    </div>
                                    <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-bold rounded-full">☕ Sedang Diracik</span>
                                </div>
                                <p class="text-xs font-medium text-gray-800 mb-2">2x Caramel Macchiato (Oat Milk, Less Sweet)<br><span class="text-[10px] text-gray-400">1x Butter Croissant Warm</span></p>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200 text-xs">
                                <span class="font-bold text-gray-900">Rp 128.000 <span class="text-[10px] font-normal text-emerald-600">Tunai</span></span>
                                <button onclick="ubahStatusCard(this, 'siap')" class="px-3 py-1 bg-[#6b5344] hover:bg-[#584437] text-white font-bold rounded-xl text-[11px] transition shadow">
                                    Tandai Siap
                                </button>
                            </div>
                        </div>

                        <!-- Pesanan 2 -->
                        <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80 flex flex-col justify-between item-pesanan" data-status="siap">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">#EX-1043</p>
                                        <p class="text-[10px] text-gray-500">Takeaway</p>
                                    </div>
                                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full">✔ Siap Saji</span>
                                </div>
                                <p class="text-xs font-medium text-gray-800 mb-2">1x Iced Spanish Latte (Regular)<br><span class="text-[10px] text-gray-400">Catatan: Extra shot espresso</span></p>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200 text-xs">
                                <span class="font-bold text-gray-900">Rp 47.000 <span class="text-[10px] font-normal text-blue-600">QRIS</span></span>
                                <button onclick="selesaikanPesananCard(this)" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[11px] transition shadow">
                                    Selesaikan
                                </button>
                            </div>
                        </div>

                        <!-- Pesanan 3 -->
                        <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80 flex flex-col justify-between item-pesanan" data-status="diracik">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">#EX-1044</p>
                                        <p class="text-[10px] text-gray-500">Meja 01</p>
                                    </div>
                                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-full">⏳ Antrean Masuk</span>
                                </div>
                                <p class="text-xs font-medium text-gray-800 mb-2">1x Manual Brew V60 Flores Ethiopia Gayo<br><span class="text-[10px] text-gray-400">1x Cinnamon Roll</span></p>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200 text-xs">
                                <span class="font-bold text-gray-900">Rp 76.000 <span class="text-[10px] font-normal text-emerald-600">Tunai</span></span>
                                <button onclick="ubahStatusCard(this, 'diracik')" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold rounded-xl text-[11px] transition shadow">
                                    Mulai Diracik
                                </button>
                            </div>
                        </div>

                        <!-- Pesanan 4 -->
                        <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200/80 flex flex-col justify-between item-pesanan" data-status="siap">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">#EX-1045</p>
                                        <p class="text-[10px] text-gray-500">Delivery Ojol</p>
                                    </div>
                                    <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-bold rounded-full">🛵 Sedang Dikirim</span>
                                </div>
                                <p class="text-xs font-medium text-gray-800 mb-2">2x Flat White Double Ristretto<br><span class="text-[10px] text-gray-400">1x Almond Tart GOURMET</span></p>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200 text-xs">
                                <span class="font-bold text-gray-900">Rp 112.000 <span class="text-[10px] font-normal text-blue-600">QRIS</span></span>
                                <button onclick="selesaikanPesananCard(this)" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[11px] transition shadow">
                                    Selesaikan
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 text-center text-xs text-gray-400">
                    Sistem Antrean Terhubung Otomatis ke Kitchen Display System (KDS)
                </div>
            </div>

            <!-- KOLOM KANAN (1 Kolom): STOK KRITIS & REORDER -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-900">Stok Kritis</h3>
                        <a href="{{ route('kelola-stok') }}" class="text-[11px] text-[#f97316] font-bold hover:underline">Kelola Stok &gt;</a>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-4">Perlu restock segera untuk menghindari kehabisan stok.</p>

                    <!-- Daftar Bahan Kritis -->
                    <div class="space-y-3.5 text-xs">
                        
                        <!-- Bahan 1 -->
                        <div class="p-3 bg-red-50/50 rounded-xl border border-red-200 flex flex-col gap-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-gray-900">Biji Kopi House Blend</h4>
                                    <p class="text-[10px] text-gray-500">Min Buffer: 5.0 kg</p>
                                </div>
                                <span class="bg-red-600 text-white font-bold text-[10px] px-2 py-0.5 rounded-full">Sisa 1.8 kg</span>
                            </div>
                            <!-- Bar Sisa Stok -->
                            <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-red-600 h-full" style="width: 36%"></div>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-[10px] text-gray-400">Dago Roastery</span>
                                <div class="flex gap-1.5">
                                    <button onclick="pesanBahan('Biji Kopi House Blend')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[10px]">Restock</button>
                                    <button onclick="lihatDetailBahan('Biji Kopi House Blend')" class="px-2.5 py-1 bg-gray-200 hover:bg-gray-300 font-bold rounded-lg text-gray-700 text-[10px]">Detail</button>
                                </div>
                            </div>
                        </div>

                        <!-- Bahan 2 -->
                        <div class="p-3 bg-red-50/50 rounded-xl border border-red-200 flex flex-col gap-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-gray-900">Fresh Milk Greenfields</h4>
                                    <p class="text-[10px] text-gray-500">Min Buffer: 12.0 Liter</p>
                                </div>
                                <span class="bg-red-600 text-white font-bold text-[10px] px-2 py-0.5 rounded-full">Sisa 3.2 L</span>
                            </div>
                            <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-red-600 h-full" style="width: 25%"></div>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-[10px] text-gray-400">Sentosa Dairy</span>
                                <div class="flex gap-1.5">
                                    <button onclick="pesanBahan('Fresh Milk Greenfields')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[10px]">Restock</button>
                                    <button onclick="lihatDetailBahan('Fresh Milk Greenfields')" class="px-2.5 py-1 bg-gray-200 hover:bg-gray-300 font-bold rounded-lg text-gray-700 text-[10px]">Detail</button>
                                </div>
                            </div>
                        </div>

                        <!-- Bahan 3 -->
                        <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-200 flex flex-col gap-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-gray-900">Sirup Gourmet Rasberry</h4>
                                    <p class="text-[10px] text-gray-500">Min Buffer: 4 Botol</p>
                                </div>
                                <span class="bg-amber-600 text-white font-bold text-[10px] px-2 py-0.5 rounded-full">Sisa 2 Btl</span>
                            </div>
                            <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-amber-500 h-full" style="width: 50%"></div>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-[10px] text-gray-400">PT Gourmet Prima</span>
                                <div class="flex gap-1.5">
                                    <button onclick="pesanBahan('Sirup Gourmet Rasberry')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[10px]">Order PO</button>
                                    <button onclick="lihatDetailBahan('Sirup Gourmet Rasberry')" class="px-2.5 py-1 bg-gray-200 hover:bg-gray-300 font-bold rounded-lg text-gray-700 text-[10px]">Detail</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    <button onclick="bukaPanelTambahPO()" class="w-full py-2 bg-gray-900 hover:bg-black text-white font-bold rounded-xl text-xs transition shadow">
                        📦 Buat Purchase Order (PO) Massal
                    </button>
                </div>
            </div>

        </div>

        <!-- BAGIAN BAWAH: JADWAL SHIFT & KEHADIRAN STAF -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Tabel Jadwal & Kehadiran (2 Kolom) -->
            <div class="lg:col-span-2 bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Jadwal Shift & Kehadiran Staf</h3>
                        <p class="text-[11px] text-gray-400">Shift Pagi • 8 Staff Aktif di Area Cafe</p>
                    </div>
                    <button onclick="bukaKelolaShift()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                        Kelola Jadwal &gt;
                    </button>
                </div>

                <!-- Tabel Staf -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-gray-50 text-gray-400 font-bold uppercase tracking-wider border-b border-gray-100">
                                <th class="py-2.5 px-3">Nama & Role</th>
                                <th class="py-2.5 px-3">Posisi Primary</th>
                                <th class="py-2.5 px-3">Jam Shift</th>
                                <th class="py-2.5 px-3">Status Kehadiran</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                            <tr>
                                <td class="py-3 px-3 flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-gray-800 text-white font-bold flex items-center justify-center text-[10px]">AR</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Ahmad Ridwan</p>
                                        <p class="text-[9px] text-gray-400">ID: STF-01</p>
                                    </div>
                                </td>
                                <td class="py-3 px-3">Head Barista</td>
                                <td class="py-3 px-3">07:00 - 15:00</td>
                                <td class="py-3 px-3"><span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">✔ Tepat Waktu (06:50)</span></td>
                                <td class="py-3 px-3 text-center">
                                    <button onclick="hubungiStaf('Ahmad Ridwan')" class="p-1 hover:bg-gray-200 rounded text-gray-600" title="Hubungi">💬</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 px-3 flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-900 font-bold flex items-center justify-center text-[10px]">DS</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Dewi Sartika</p>
                                        <p class="text-[9px] text-gray-400">ID: STF-02</p>
                                    </div>
                                </td>
                                <td class="py-3 px-3">Kasir POS</td>
                                <td class="py-3 px-3">07:30 - 15:30</td>
                                <td class="py-3 px-3"><span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">✔ Tepat Waktu (07:15)</span></td>
                                <td class="py-3 px-3 text-center">
                                    <button onclick="hubungiStaf('Dewi Sartika')" class="p-1 hover:bg-gray-200 rounded text-gray-600" title="Hubungi">💬</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 px-3 flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-900 font-bold flex items-center justify-center text-[10px]">RN</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Rian Nugraha</p>
                                        <p class="text-[9px] text-gray-400">ID: STF-03</p>
                                    </div>
                                </td>
                                <td class="py-3 px-3">Junior Barista</td>
                                <td class="py-3 px-3">07:00 - 15:00</td>
                                <td class="py-3 px-3"><span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">✔ Tepat Waktu (06:55)</span></td>
                                <td class="py-3 px-3 text-center">
                                    <button onclick="hubungiStaf('Rian Nugraha')" class="p-1 hover:bg-gray-200 rounded text-gray-600" title="Hubungi">💬</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kotak Kanan Bawah: Cetak Rekap Operasional -->
            <div class="bg-[#584437] text-white p-5 rounded-2xl shadow-xl flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold mb-1">Cetak Rekap Operasional Hari Ini</h3>
                    <p class="text-[11px] text-amber-200 mb-4">Laporan komprehensif kasir, stok bahan, dan absensi staf di outlet Dago.</p>

                    <div class="space-y-2 text-xs">
                        <button onclick="cetakLaporanSpesifik('ringkasan')" class="w-full p-2.5 bg-white/10 hover:bg-white/20 rounded-xl font-bold flex justify-between items-center transition border border-white/20">
                            <span>📄 Laporan Ringkas Shift</span>
                            <span>&gt;</span>
                        </button>
                        <button onclick="cetakLaporanSpesifik('keuangan')" class="w-full p-2.5 bg-white/10 hover:bg-white/20 rounded-xl font-bold flex justify-between items-center transition border border-white/20">
                            <span>📊 Laporan Finansial Kasir</span>
                            <span>&gt;</span>
                        </button>
                        <button onclick="cetakLaporanSpesifik('absensi')" class="w-full p-2.5 bg-white/10 hover:bg-white/20 rounded-xl font-bold flex justify-between items-center transition border border-white/20">
                            <span>📋 Laporan Absensi & Shift Staf</span>
                            <span>&gt;</span>
                        </button>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-white/10 flex gap-2">
                    <button onclick="cetakSemuaLaporan()" class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold rounded-xl text-xs transition shadow">
                        Cetak Semua PDF
                    </button>
                </div>
            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        // 1. Dropdown Cetak Atas
        function toggleDropdownCetakAtas() {
            const dropdown = document.getElementById('dropdownCetakAtas');
            dropdown.classList.toggle('hidden');
        }

        function jalankanAksiCetak(jenis) {
            document.getElementById('dropdownCetakAtas').classList.add('hidden');
            alert(`🖨️ Mencetak laporan operasional jenis: [ ${jenis.toUpperCase()} ]...`);
        }

        window.addEventListener('click', function(e) {
            if (!e.target.closest('button')) {
                const dropdown = document.getElementById('dropdownCetakAtas');
                if (dropdown && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            }
        });

        // 2. Filter Tab Status Pesanan (Semua, Diracik, Siap)
        function filterPesananStatus(status, element) {
            document.getElementById('tabSemua').className = "px-3 py-1 text-gray-500 hover:text-gray-900 rounded-lg transition";
            document.getElementById('tabDiracik').className = "px-3 py-1 text-gray-500 hover:text-gray-900 rounded-lg transition";
            document.getElementById('tabSiap').className = "px-3 py-1 text-gray-500 hover:text-gray-900 rounded-lg transition";

            element.className = "px-3 py-1 bg-white shadow-sm rounded-lg text-gray-900 font-bold transition";

            const kartuList = document.querySelectorAll('.item-pesanan');
            kartuList.forEach(kartu => {
                let st = kartu.getAttribute('data-status');
                if (status === 'semua' || st === status) {
                    kartu.style.display = "";
                } else {
                    kartu.style.display = "none";
                }
            });
        }

        // 3. Aksi Kartu Pesanan
        function ubahStatusCard(btn, statusBaru) {
            alert(`🔄 Status pesanan berhasil diperbarui menjadi: ${statusBaru.toUpperCase()}`);
            btn.textContent = "✔ Selesai";
            btn.className = "px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[11px] transition shadow";
        }

        function selesaikanPesananCard(btn) {
            const kartu = btn.closest('.item-pesanan');
            alert("✅ Pesanan berhasil diselesaikan dan diarsipkan ke riwayat transaksi!");
            kartu.style.opacity = "0.4";
            btn.disabled = true;
            btn.textContent = "Selesai";
        }

        // 4. Aksi Stok Kritis
        function pesanBahan(namaBahan) {
            const konfirmasi = confirm(`🛒 Buat pesanan restock otomatis untuk bahan [ ${namaBahan} ] ke supplier utama?`);
            if (konfirmasi) {
                alert(`✅ Purchase Order untuk [ ${namaBahan} ] berhasil dikirim!`);
            }
        }

        function lihatDetailBahan(namaBahan) {
            alert(`🔍 Membuka panel detail log inventaris dan estimasi habis untuk bahan: ${namaBahan}`);
        }

        function bukaPanelTambahPO() {
            alert("📦 Membuka wizard pembuatan PO massal untuk seluruh bahan kritis.");
        }

        // 5. Aksi Staf & Jadwal
        function hubungiStaf(nama) {
            alert(`💬 Membuka obrolan pesan cepat dengan staf: ${nama}`);
        }

        function bukaKelolaShift() {
            alert("📅 Mengalihkan ke panel pengaturan manajemen jadwal shift staf.");
        }

        // 6. Cetak Rekap
        function cetakLaporanSpesifik(jenis) {
            alert(`📄 Menyiapkan dokumen PDF untuk: ${jenis.toUpperCase()}`);
        }

        function cetakSemuaLaporan() {
            alert("🖨️ Mengunduh bundel lengkap seluruh laporan operasional harian kafe.");
        }

        // 7. Pencarian Global
        function cariDataGlobal() {
            let keyword = document.getElementById('inputCariGlobal').value.toLowerCase();
            if (keyword.length > 2) {
                // Simulasi pencarian
                console.log("Mencari data: " + keyword);
            }
        }
    </script>

</body>
</html>