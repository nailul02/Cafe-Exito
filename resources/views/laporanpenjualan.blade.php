<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan & Finansial - Exito Cafe</title>
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

    <!-- SIDEBAR OWNER -->
    <aside class="w-64 bg-[#6b5344] text-white flex flex-col p-6 min-h-screen shadow-md justify-between relative z-30">
        <div>
            <!-- Logo & Nama Cafe -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-gray-800 font-serif italic font-bold shadow-md">
                    exito
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-wide">Cafe Exito</h1>
                    <p class="text-xs text-amber-200">Owner Dashboard</p>
                </div>
            </div>

            <!-- Profil User Owner -->
            <div class="flex items-center gap-3 bg-[#584437] p-3 rounded-xl mb-6">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-900 font-bold flex items-center justify-center text-sm">
                    PA
                </div>
                <div>
                    <p class="text-sm font-bold leading-tight">Pak Adrian</p>
                    <p class="text-[10px] text-amber-200">adrian@exito.coffee</p>
                </div>
            </div>

            <!-- Menu Navigasi Owner -->
            <nav class="flex flex-col gap-2 text-sm font-medium">
                <a href="{{ route('owner-dashboard') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    📊 Dashboard
                </a>
                <a href="{{ route('laporan-penjualan') }}" class="py-2.5 px-4 rounded-xl bg-[#584437] text-white shadow-sm flex items-center gap-3 cursor-pointer">
                    📈 Laporan Penjualan
                </a>
                <a href="{{ route('stok-bahan') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center justify-between cursor-pointer">
                    <span class="flex items-center gap-3">📦 Stok Bahan</span>
                    <span class="bg-red-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">3 Kritis</span>
                </a>
                <a href="{{ route('profil-akun') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
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
        <header class="flex justify-between items-center mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Laporan Penjualan & Finansial</h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span> 
                        Outlet: Utama (Dago) • Shift Pagi Kasir: Rian & Budi
                    </p>
                </div>
            </div>
            
            <!-- Tombol Kanan Atas -->
            <div class="flex items-center gap-3">
                <!-- Filter Tanggal -->
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700">
                    <span>📅 17 Feb 2025 - 23 Feb 2025 ▾</span>
                </div>
                <button onclick="alert('🔄 Menyinkronkan ulang data finansial cloud...')" class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 text-gray-600 transition" title="Sinkronisasi">
                    🔄
                </button>
                
                <!-- Dropdown Cetak & Ekspor -->
                <div class="relative">
                    <button onclick="toggleDropdownCetak()" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-2">
                        🖨️ Cetak & Ekspor ▾
                    </button>
                    <div id="dropdownCetak" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-20 text-xs font-medium">
                        <button onclick="jalankanAksiCetak('pdf')" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">📄 Cetak Laporan PDF</button>
                        <button onclick="jalankanAksiCetak('excel')" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">📊 Ekspor File Excel</button>
                        <button onclick="jalankanAksiCetak('print')" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">🖨️ Cetak Langsung</button>
                    </div>
                </div>
            </div>
        </header>

        <!-- TAB NAVIGASI LAPORAN (HARIAN, MINGGUAN, BULANAN, KUSTOM) -->
        <div class="flex items-center justify-between bg-white p-3 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <div class="flex gap-2 text-xs font-semibold">
                <button onclick="gantiTabLaporan('harian', this)" id="tabHarian" class="px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition">Laporan Harian (Aktif)</button>
                <button onclick="gantiTabLaporan('mingguan', this)" id="tabMingguan" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition">Laporan Mingguan</button>
                <button onclick="gantiTabLaporan('bulanan', this)" id="tabBulanan" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition">Laporan Bulanan</button>
                <button onclick="gantiTabLaporan('kustom', this)" id="tabKustom" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition">Kustom Rentang</button>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500 font-medium px-2">
                <span>⚡ Sinkronisasi Terakhir: 14:42 WIB</span>
                <span class="flex items-center gap-1 text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded-lg">✔ Kasir Sesuai Kas Fisik</span>
            </div>
        </div>

        <!-- 4 KOTAK STATISTIK UTAMA -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-5 mb-6">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Omset Kotor</span>
                        <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full">+14.2%</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Rp 48.750.000</h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-50">Minggu Ini <span class="text-gray-900 font-semibold">(938 transaksi)</span></p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pengeluaran & HPP</span>
                        <span class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">+3.1%</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Rp 18.250.000</h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-50">Bahan Baku, Susu & Operasional</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Net Profit (Bersih)</span>
                        <span class="text-[10px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full">Margin 62.5%</span>
                    </div>
                    <h2 class="text-2xl font-bold text-emerald-700 tracking-tight">Rp 30.500.000</h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-50">Laba bersih sebelum pajak</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-Rata Transaksi (AOV)</span>
                        <span class="text-[10px] font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">Stabil</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Rp 52.500</h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-50">2.4 items / pesanan</p>
            </div>
        </div>

        <!-- GRID BAGIAN TENGAH (METODE PEMBAYARAN & KATEGORI MENU) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <!-- Kolom Kiri: Metode Pembayaran -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-gray-900">Metode Pembayaran</h3>
                        <span class="text-xs text-gray-400">Dominasi transaksi digital outlet</span>
                    </div>

                    <!-- Progress Bar Multi-warna -->
                    <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden flex mb-6">
                        <div class="bg-[#6b5344] h-full" style="width: 65%"></div>
                        <div class="bg-amber-500 h-full" style="width: 20%"></div>
                        <div class="bg-blue-500 h-full" style="width: 15%"></div>
                    </div>

                    <!-- Detail Metode -->
                    <div class="space-y-4 text-xs">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#6b5344]"></span>
                                <div>
                                    <p class="font-bold text-gray-900">QRIS Dinamis / Statis</p>
                                    <p class="text-[10px] text-gray-400">603 Transaksi</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">65%</p>
                                <p class="text-[10px] text-gray-500">Rp 31.687.500</p>
                            </div>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                <div>
                                    <p class="font-bold text-gray-900">Tunai (Cash Tender)</p>
                                    <p class="text-[10px] text-gray-400">188 Transaksi</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">20%</p>
                                <p class="text-[10px] text-gray-500">Rp 9.750.000</p>
                            </div>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                                <div>
                                    <p class="font-bold text-gray-900">Kartu EDC Debit / Kredit</p>
                                    <p class="text-[10px] text-gray-400">147 Transaksi</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">15%</p>
                                <p class="text-[10px] text-gray-500">Rp 7.312.500</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-3 border-t border-gray-100 flex justify-between items-center text-xs">
                    <span class="text-gray-500">Settlement MDR QRIS: <strong class="text-gray-900">0.7%</strong></span>
                    <button onclick="alert('📄 Membuka rincian detail settlement gateway pembayaran...')" class="text-amber-800 font-bold hover:underline bg-transparent border-0 cursor-pointer">Rincian Settlement &gt;</button>
                </div>
            </div>

            <!-- Kolom Kanan: Kategori Menu & Penjualan Jam Sibuk -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-gray-900">Kategori Menu & Penjualan Jam Sibuk</h3>
                        <span class="bg-gray-100 text-gray-700 text-[10px] font-bold px-2.5 py-1 rounded-lg">Peak Hour: 15:00 - 16:30</span>
                    </div>

                    <!-- Kategori Progress -->
                    <div class="grid grid-cols-3 gap-3 mb-5 text-xs">
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between font-bold mb-1">
                                <span>Espresso & Coffee</span>
                                <span class="text-amber-700">58%</span>
                            </div>
                            <p class="text-[10px] text-gray-400">538 Cangkir • Rp 20.275.000</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between font-bold mb-1">
                                <span>Artisan Pastry</span>
                                <span class="text-amber-700">32%</span>
                            </div>
                            <p class="text-[10px] text-gray-400">296 Pcs • Rp 15.600.000</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between font-bold mb-1">
                                <span>Whole Bean</span>
                                <span class="text-amber-700">10%</span>
                            </div>
                            <p class="text-[10px] text-gray-400">94 Pack • Rp 4.878.000</p>
                        </div>
                    </div>

                    <!-- Top 3 Terlaris -->
                    <div class="bg-amber-50/50 p-3.5 rounded-xl border border-amber-200/60 mb-4 text-xs">
                        <p class="font-bold text-gray-900 mb-2">⭐ TOP 3 TERLARIS:</p>
                        <div class="grid grid-cols-3 gap-2 text-[11px] font-medium text-gray-700">
                            <div class="bg-white p-2 rounded-lg border border-amber-100 shadow-sm">1. Asen Oat Latte (214 cup)</div>
                            <div class="bg-white p-2 rounded-lg border border-amber-100 shadow-sm">2. Butter Croissant (142 pcs)</div>
                            <div class="bg-white p-2 rounded-lg border border-amber-100 shadow-sm">3. Dirty Magic Espresso (98 cup)</div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                    <span>Kecepatan Saji Rata-rata: <strong class="text-gray-900">3 Menit 40 Detik / Order</strong></span>
                    <span class="text-emerald-600 font-bold">✔ Target SLA Terpenuhi</span>
                </div>
            </div>

        </div>

        <!-- TABEL AUDIT LOG TRANSAKSI KASIR -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-3">
                    <h3 class="text-base font-bold text-gray-900">Audit Log Transaksi Kasir</h3>
                    <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2.5 py-1 rounded-full">Live Feed</span>
                </div>
                <!-- Filter & Search Tabel -->
                <div class="flex items-center gap-3 text-xs">
                    <div class="relative">
                        <input type="text" id="inputCariTransaksi" onkeyup="cariTransaksiLog()" placeholder="Cari No. Order / Menu..." class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6b5344] w-52">
                    </div>
                    <select id="filterMetodeBayar" onchange="filterTabelLog()" class="px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6b5344] text-gray-700 font-medium">
                        <option value="semua">Semua Metode Pembayaran</option>
                        <option value="qris">QRIS</option>
                        <option value="tunai">Tunai</option>
                        <option value="edc">Kartu EDC</option>
                    </select>
                    <button onclick="refreshTabelLog()" class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 text-gray-600" title="Refresh Log">🔄</button>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto">
                <table id="tabelAuditLog" class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-400 font-bold uppercase tracking-wider border-b border-gray-100">
                            <th class="py-3 px-4">Tanggal & Jam</th>
                            <th class="py-3 px-4">No. Transaksi</th>
                            <th class="py-3 px-4">Metode Bayar</th>
                            <th class="py-3 px-4">Kategori & Ringkasan Item</th>
                            <th class="py-3 px-4">Jumlah</th>
                            <th class="py-3 px-4">Total Transaksi</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                        <tr class="hover:bg-gray-50/80 transition" data-metode="qris">
                            <td class="py-3.5 px-4 text-gray-500">23 Feb 2025<br><span class="text-[10px]">14:39:12 WIB</span></td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">#EXT-20250223-094</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-gray-900 text-white font-bold rounded-lg text-[10px]">QRIS</span></td>
                            <td class="py-3.5 px-4">2x Asen Oat Latte, 1x Almond Croissant<br><span class="text-[10px] text-gray-400">Kategori: Kopi, Makanan</span></td>
                            <td class="py-3.5 px-4">3 Item</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">Rp 118.000</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">✔ Sukses</span></td>
                            <td class="py-3.5 px-4 text-center">
                                <button onclick="cetakStrukItem('#EXT-20250223-094')" class="p-1.5 hover:bg-gray-200 rounded-lg text-gray-600 transition" title="Cetak Struk">🖨️</button>
                                <button onclick="lihatDetailItem('#EXT-20250223-094')" class="p-1.5 hover:bg-gray-200 rounded-lg text-gray-600 transition" title="Lihat Detail">👁️</button>
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50/80 transition" data-metode="tunai">
                            <td class="py-3.5 px-4 text-gray-500">23 Feb 2025<br><span class="text-[10px]">14:35:05 WIB</span></td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">#EXT-20250223-093</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-amber-500 text-white font-bold rounded-lg text-[10px]">Tunai</span></td>
                            <td class="py-3.5 px-4">1x Filter V60 Gayo Anaerob<br><span class="text-[10px] text-gray-400">Kategori: Kopi Manual Brew</span></td>
                            <td class="py-3.5 px-4">1 Item</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">Rp 42.000</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">✔ Sukses</span></td>
                            <td class="py-3.5 px-4 text-center">
                                <button onclick="cetakStrukItem('#EXT-20250223-093')" class="p-1.5 hover:bg-gray-200 rounded-lg text-gray-600 transition" title="Cetak Struk">🖨️</button>
                                <button onclick="lihatDetailItem('#EXT-20250223-093')" class="p-1.5 hover:bg-gray-200 rounded-lg text-gray-600 transition" title="Lihat Detail">👁️</button>
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50/80 transition" data-metode="edc">
                            <td class="py-3.5 px-4 text-gray-500">23 Feb 2025<br><span class="text-[10px]">14:11:48 WIB</span></td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">#EXT-20250223-092</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-blue-600 text-white font-bold rounded-lg text-[10px]">Kartu EDC</span></td>
                            <td class="py-3.5 px-4">1x Exito House Blend (250g), 1x Tumbler Matte<br><span class="text-[10px] text-gray-400">Kategori: Merchandise, Beans</span></td>
                            <td class="py-3.5 px-4">2 Item</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">Rp 345.000</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">✔ Sukses</span></td>
                            <td class="py-3.5 px-4 text-center">
                                <button onclick="cetakStrukItem('#EXT-20250223-092')" class="p-1.5 hover:bg-gray-200 rounded-lg text-gray-600 transition" title="Cetak Struk">🖨️</button>
                                <button onclick="lihatDetailItem('#EXT-20250223-092')" class="p-1.5 hover:bg-gray-200 rounded-lg text-gray-600 transition" title="Lihat Detail">👁️</button>
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50/80 transition" data-metode="qris">
                            <td class="py-3.5 px-4 text-gray-500">23 Feb 2025<br><span class="text-[10px]">13:56:19 WIB</span></td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">#EXT-20250223-091</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-gray-900 text-white font-bold rounded-lg text-[10px]">QRIS</span></td>
                            <td class="py-3.5 px-4">3x Iced Dirty Magic, 2x Canele Vanilla<br><span class="text-[10px] text-gray-400">Kategori: Kopi, Pastry</span></td>
                            <td class="py-3.5 px-4">5 Item</td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">Rp 195.000</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">✔ Sukses</span></td>
                            <td class="py-3.5 px-4 text-center">
                                <button onclick="cetakStrukItem('#EXT-20250223-091')" class="p-1.5 hover:bg-gray-200 rounded-lg text-gray-600 transition" title="Cetak Struk">🖨️</button>
                                <button onclick="lihatDetailItem('#EXT-20250223-091')" class="p-1.5 hover:bg-gray-200 rounded-lg text-gray-600 transition" title="Lihat Detail">👁️</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginasi Tabel -->
            <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100 text-xs">
                <span class="text-gray-400">Menampilkan 1–4 dari 938 transaksi</span>
                <div class="flex items-center gap-1">
                    <button class="px-3 py-1 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">Sebelumnya</button>
                    <button class="w-7 h-7 bg-[#6b5344] text-white font-bold rounded-lg shadow">1</button>
                    <button onclick="gantiHalamanTabel(2)" class="w-7 h-7 hover:bg-gray-100 font-semibold rounded-lg">2</button>
                    <button onclick="gantiHalamanTabel(5)" class="w-7 h-7 hover:bg-gray-100 font-semibold rounded-lg">5</button>
                    <span class="px-1 text-gray-400">...</span>
                    <button onclick="gantiHalamanTabel(47)" class="w-7 h-7 hover:bg-gray-100 font-semibold rounded-lg">47</button>
                    <button onclick="gantiHalamanTabel('next')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg">Selanjutnya</button>
                </div>
            </div>
        </div>

        <!-- KARTU BAWAH: SIAP CETAK TUTUP KASIR HARIAN -->
        <div class="bg-[#584437] text-white p-6 rounded-2xl shadow-xl flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center text-xl font-bold shadow">
                    📜
                </div>
                <div>
                    <h3 class="text-base font-bold">Siap Cetak Tutup Kasir Harian</h3>
                    <p class="text-xs text-amber-200 mt-0.5">Total Fisik: Rp 48.750.000 | 4 Kasir Divalidasi</p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button onclick="pratinjauStrukThermal()" class="flex-1 sm:flex-none px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl text-xs transition border border-white/20">
                    Pratinjau Struk (Thermal)
                </button>
                <button onclick="cetakTutupKasirSekarang()" class="flex-1 sm:flex-none px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold rounded-xl text-xs transition shadow-md">
                    Cetak Sekarang
                </button>
            </div>
        </div>

    </main>

    <!-- SCRIPT JAVASCRIPT UNTUK SEMUA INTERAKSI TOMBOL -->
    <script>
        // 1. Dropdown Cetak & Ekspor
        function toggleDropdownCetak() {
            const dropdown = document.getElementById('dropdownCetak');
            dropdown.classList.toggle('hidden');
        }

        function jalankanAksiCetak(jenis) {
            document.getElementById('dropdownCetak').classList.add('hidden');
            if (jenis === 'pdf') {
                alert("📄 Berhasil mengunduh dokumen Laporan Penjualan (PDF).");
            } else if (jenis === 'excel') {
                alert("📊 Berhasil mengekspor data laporan ke Spreadsheet (Excel).");
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

        // 2. Tab Navigasi Laporan (Harian, Mingguan, Bulanan, Kustom)
        function gantiTabLaporan(jenis, element) {
            document.getElementById('tabHarian').className = "px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition";
            document.getElementById('tabMingguan').className = "px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition";
            document.getElementById('tabBulanan').className = "px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition";
            document.getElementById('tabKustom').className = "px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition";

            element.className = "px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition font-bold";
            alert(`📈 Menampilkan data untuk: [ ${jenis.toUpperCase()} ]`);
        }

        // 3. Pencarian Audit Log Transaksi
        function cariTransaksiLog() {
            let input = document.getElementById('inputCariTransaksi').value.toLowerCase();
            let rows = document.querySelectorAll('#tabelAuditLog tbody tr');

            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                if (text.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // 4. Filter Tabel Berdasarkan Metode Pembayaran
        function filterTabelLog() {
            let filter = document.getElementById('filterMetodeBayar').value;
            let rows = document.querySelectorAll('#tabelAuditLog tbody tr');

            rows.forEach(row => {
                let metode = row.getAttribute('data-metode');
                if (filter === 'semua' || metode === filter) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        function refreshTabelLog() {
            document.getElementById('inputCariTransaksi').value = "";
            document.getElementById('filterMetodeBayar').value = "semua";
            let rows = document.querySelectorAll('#tabelAuditLog tbody tr');
            rows.forEach(r => r.style.display = "");
            alert("🔄 Audit log transaksi berhasil diperbarui secara real-time!");
        }

        // 5. Aksi Tombol pada Baris Tabel
        function cetakStrukItem(noOrder) {
            alert(`🖨️ Mencetak ulang struk thermal untuk transaksi: ${noOrder}`);
        }

        function lihatDetailItem(noOrder) {
            alert(`👁️ Membuka rincian lengkap pesanan dan item untuk nomor transaksi: ${noOrder}`);
        }

        function gantiHalamanTabel(halaman) {
            alert(`📄 Berpindah ke halaman tabel transaksi ke-${halaman}`);
        }

        // 6. Tombol Bawah (Tutup Kasir Harian)
        function pratinjauStrukThermal() {
            alert("🧾 Membuka jendela pratinjau struk kasir thermal (80mm) sebelum dicetak.");
        }

        function cetakTutupKasirSekarang() {
            const konfirmasi = confirm("🖨️ Apakah Anda ingin langsung mencetak laporan Tutup Kasir Harian ke printer thermal utama?");
            if (konfirmasi) {
                alert("✅ Laporan Tutup Kasir berhasil dikirim ke printer thermal!");
            }
        }
    </script>

</body>
</html>