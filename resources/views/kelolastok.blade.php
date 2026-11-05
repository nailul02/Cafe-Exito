<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Stok Bahan & Inventaris - Cafe Exito</title>
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
                <a href="{{ route('kelola-stok') }}" class="py-2.5 px-4 rounded-xl bg-[#f97316] text-white shadow-sm flex items-center justify-between cursor-pointer font-semibold">
                    <span class="flex items-center gap-3"><span>📦</span> Kelola Stok</span>
                    <span class="bg-red-600 text-white text-[9px] px-2 py-0.5 rounded-full font-bold">3 Alert</span>
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

    <!-- KONTEN UTAMA KELOLA STOK -->
    <main class="flex-1 p-6 overflow-y-auto">
        
        <!-- HEADER ATAS -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 bg-white p-3.5 rounded-2xl shadow-sm border border-gray-100 gap-3">
            <!-- Kolom Pencarian -->
            <div class="relative w-full sm:w-80">
                <input type="text" id="inputCariStok" onkeyup="cariStokBahan()" placeholder="Cari bahan baku, sirup, kemasan..." class="w-full pl-9 pr-8 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                <span class="absolute left-3 top-2 text-gray-400 text-xs">🔍</span>
                <span class="absolute right-3 top-2 text-gray-400 text-xs cursor-pointer" onclick="document.getElementById('inputCariStok').value=''; cariStokBahan();">✕</span>
            </div>

            <!-- Tombol Aksi Header Kanan -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <button onclick="cetakLaporanStok()" class="px-3.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition flex items-center gap-1.5 shadow-sm">
                    🖨️ Cetak Laporan
                </button>
                <button onclick="bukaModalTambahStok()" class="px-3.5 py-1.5 bg-[#6b5344] hover:bg-[#584437] text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow">
                    ➕ Tambah Stok
                </button>
                <button onclick="alert('🔄 Menyinkronkan ulang data persediaan dengan cloud server...')" class="p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-gray-600">
                    🔄
                </button>
                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-900 font-bold flex items-center justify-center text-xs shadow">
                    BS
                </div>
            </div>
        </header>

        <!-- SUB-HEADER / JUDUL HALAMAN -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 gap-2">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">OPERASIONAL STORE #01</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-[10px] font-bold text-[#f97316]">BAR COUNTER & GUDANG UTAMA</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Kelola Stok Bahan & Inventaris</h2>
                <p class="text-xs text-gray-400">Pantau sisa persediaan fisik, buffer aman, dan riwayat pengiriman stok.</p>
            </div>
        </div>

        <!-- TAB KATEGORI BAHAN -->
        <div class="flex gap-2 text-xs font-semibold overflow-x-auto pb-2 mb-5">
            <button onclick="filterKategoriBahan('semua', this)" id="tabCatSemua" class="px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition whitespace-nowrap">Semua Kategori (38)</button>
            <button onclick="filterKategoriBahan('biji', this)" id="tabCatBiji" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap">Biji Kopi & Bubuk (8)</button>
            <button onclick="filterKategoriBahan('susu', this)" id="tabCatSusu" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap">Susu & Dairy (6)</button>
            <button onclick="filterKategoriBahan('sirup', this)" id="tabCatSirup" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap">Sirup & Topping (12)</button>
            <button onclick="filterKategoriBahan('bahan', this)" id="tabCatBahan" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap">Bahan Makanan (7)</button>
            <button onclick="filterKategoriBahan('packaging', this)" id="tabCatPackaging" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap">Packaging & Cups (5)</button>
        </div>

        <!-- 4 KOTAK STATISTIK RINGKASAN STOK -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Item Bahan/Barang</span>
                    <h2 class="text-xl font-bold text-gray-900 mt-1">38 <span class="text-xs font-normal text-gray-500">Bahan Baku</span></h2>
                    <p class="text-[10px] text-emerald-600 font-semibold mt-1">✔ Semua dalam pantauan sistem</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center font-bold">📦</div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-wider">Stok Kritis & Habis</span>
                    <h2 class="text-xl font-bold text-red-600 mt-1">3 Bahan</h2>
                    <p class="text-[10px] text-red-500 font-semibold mt-1">🚨 Membutuhkan Restock Segera</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold">🚨</div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Stok Masuk Hari Ini</span>
                    <h2 class="text-xl font-bold text-gray-900 mt-1">+42.5 kg <span class="text-xs font-normal text-gray-500">/ 25%</span></h2>
                    <p class="text-[10px] text-gray-500 mt-1">Diterima 08:30 WIB di Supplier</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">📥</div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estimasi Buffer Habis</span>
                    <h2 class="text-xl font-bold text-gray-900 mt-1">2.4 Hari</h2>
                    <p class="text-[10px] text-gray-500 mt-1">Rata-rata 2 Nilai Operasional</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">⏳</div>
            </div>
        </div>

        <!-- GRID UTAMA (KIRI: TABEL INVENTARIS | KANAN: INPUT STOK MASUK & RIWAYAT) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KOLOM KIRI (2 Kolom): DAFTAR BAHAN BAKU & PERSEDIAAN -->
            <div class="lg:col-span-2 bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-gray-900">Daftar Bahan Baku & Persediaan</h3>
                        <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2.5 py-0.5 rounded-full">Live Real-time</span>
                    </div>
                    <div class="flex gap-2 text-xs">
                        <button onclick="filterStatusTabel('kritis')" class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-xl transition">Filter Stok Kritis</button>
                        <button onclick="urutkanPrioritas()" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition">Urutkan: Prioritas</button>
                    </div>
                </div>

                <!-- TABEL DATA STOK -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs" id="tabelInventarisStok">
                        <thead>
                            <tr class="bg-gray-50 text-gray-400 font-bold uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3 px-3">Bahan Baku & Kategori</th>
                                <th class="py-3 px-3">Status & Level Stok</th>
                                <th class="py-3 px-3">Sisa / Buffer Min</th>
                                <th class="py-3 px-3">Trend Aktif</th>
                                <th class="py-3 px-3 text-center">Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                            
                            <!-- Baris 1: Kritis -->
                            <tr class="hover:bg-gray-50/80 transition item-stok" data-kategori="biji" data-status="kritis">
                                <td class="py-3.5 px-3">
                                    <p class="font-bold text-gray-900">Biji Kopi House Blend</p>
                                    <p class="text-[10px] text-gray-400">Arabica, Robusta (Espresso Base)</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 font-bold rounded-md text-[10px]">🚨 KRITIS (24%)</span>
                                    <div class="w-24 bg-gray-200 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-red-600 h-full" style="width: 24%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-bold text-red-600">1.2 kg</span>
                                    <p class="text-[10px] text-gray-400">Min: 5.0 kg</p>
                                </td>
                                <td class="py-3.5 px-3"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span></td>
                                <td class="py-3.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="restockItem('Biji Kopi House Blend')" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-[10px] transition">📦 Restok</button>
                                        <button onclick="editItemStok('Biji Kopi House Blend')" class="p-1 hover:bg-gray-200 rounded text-gray-600" title="Edit">✏️</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris 2: Kritis -->
                            <tr class="hover:bg-gray-50/80 transition item-stok" data-kategori="susu" data-status="kritis">
                                <td class="py-3.5 px-3">
                                    <p class="font-bold text-gray-900">Fresh Milk Greenfields 1L</p>
                                    <p class="text-[10px] text-gray-400">Susu UHT Fresh Milk</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 font-bold rounded-md text-[10px]">🚨 KRITIS (33%)</span>
                                    <div class="w-24 bg-gray-200 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-red-600 h-full" style="width: 33%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-bold text-red-600">4.0 Liter</span>
                                    <p class="text-[10px] text-gray-400">Min: 12.0 L</p>
                                </td>
                                <td class="py-3.5 px-3"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span></td>
                                <td class="py-3.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="restockItem('Fresh Milk Greenfields')" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-[10px] transition">📦 Restok</button>
                                        <button onclick="editItemStok('Fresh Milk Greenfields')" class="p-1 hover:bg-gray-200 rounded text-gray-600" title="Edit">✏️</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris 3: Habis -->
                            <tr class="hover:bg-gray-50/80 transition item-stok bg-red-50/20" data-kategori="sirup" data-status="habis">
                                <td class="py-3.5 px-3">
                                    <p class="font-bold text-red-700">Sirup Gourmet Karamel 750ml</p>
                                    <p class="text-[10px] text-gray-400">Menu / Terkait Otomatis Mandiri Bubuk POS Aktif</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-red-600 text-white font-bold rounded-md text-[10px]">HABIS (0%)</span>
                                    <div class="w-24 bg-gray-200 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-red-600 h-full" style="width: 0%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-bold text-red-600">0 Botol</span>
                                    <p class="text-[10px] text-gray-400">Min: 2 Botol</p>
                                </td>
                                <td class="py-3.5 px-3"><span class="w-3 h-3 rounded-full bg-gray-300 inline-block"></span></td>
                                <td class="py-3.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="pesanSegeraItem('Sirup Gourmet Karamel')" class="px-2 py-1 bg-red-700 hover:bg-red-800 text-white font-bold rounded-lg text-[10px] transition">🛒 Pesan Segera</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris 4: Aman -->
                            <tr class="hover:bg-gray-50/80 transition item-stok" data-kategori="biji" data-status="aman">
                                <td class="py-3.5 px-3">
                                    <p class="font-bold text-gray-900">Biji Kopi Single Origin Ethiopia Gayo</p>
                                    <p class="text-[10px] text-gray-400">Manual Brewed, Filter Beans</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-md text-[10px]">✔ Aman (190%)</span>
                                    <div class="w-24 bg-gray-200 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-emerald-500 h-full" style="width: 100%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-bold text-emerald-700">3.8 kg</span>
                                    <p class="text-[10px] text-gray-400">Min: 2.0 kg</p>
                                </td>
                                <td class="py-3.5 px-3"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span></td>
                                <td class="py-3.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="tambahStokItem('Biji Kopi Ethiopia')" class="p-1 hover:bg-gray-200 rounded text-gray-600 font-bold text-xs" title="Tambah">+</button>
                                        <button onclick="editItemStok('Biji Kopi Ethiopia')" class="p-1 hover:bg-gray-200 rounded text-gray-600" title="Edit">✏️</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris 5: Aman -->
                            <tr class="hover:bg-gray-50/80 transition item-stok" data-kategori="susu" data-status="aman">
                                <td class="py-3.5 px-3">
                                    <p class="font-bold text-gray-900">Oat Milk Barista Edition</p>
                                    <p class="text-[10px] text-gray-400">Plant-Based Dairy Alternative</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-md text-[10px]">✔ Aman (175%)</span>
                                    <div class="w-24 bg-gray-200 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-emerald-500 h-full" style="width: 100%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-bold text-emerald-700">14.0 Liter</span>
                                    <p class="text-[10px] text-gray-400">Min: 8.0 L</p>
                                </td>
                                <td class="py-3.5 px-3"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span></td>
                                <td class="py-3.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="tambahStokItem('Oat Milk Barista')" class="p-1 hover:bg-gray-200 rounded text-gray-600 font-bold text-xs" title="Tambah">+</button>
                                        <button onclick="editItemStok('Oat Milk Barista')" class="p-1 hover:bg-gray-200 rounded text-gray-600" title="Edit">✏️</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris 6: Aman -->
                            <tr class="hover:bg-gray-50/80 transition item-stok" data-kategori="bahan" data-status="aman">
                                <td class="py-3.5 px-3">
                                    <p class="font-bold text-gray-900">Butter Croissant Frozen Pack</p>
                                    <p class="text-[10px] text-gray-400">Pastry Premier Ready-to-Bake</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-md text-[10px]">✔ Aman (180%)</span>
                                    <div class="w-24 bg-gray-200 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-emerald-500 h-full" style="width: 100%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-bold text-emerald-700">18 Pcs</span>
                                    <p class="text-[10px] text-gray-400">Min: 10 PCs</p>
                                </td>
                                <td class="py-3.5 px-3"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span></td>
                                <td class="py-3.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="tambahStokItem('Butter Croissant')" class="p-1 hover:bg-gray-200 rounded text-gray-600 font-bold text-xs" title="Tambah">+</button>
                                        <button onclick="editItemStok('Butter Croissant')" class="p-1 hover:bg-gray-200 rounded text-gray-600" title="Edit">✏️</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris 7: Menipis -->
                            <tr class="hover:bg-gray-50/80 transition item-stok" data-kategori="packaging" data-status="menipis">
                                <td class="py-3.5 px-3">
                                    <p class="font-bold text-gray-900">Paper Cup 12oz Hot + Lid</p>
                                    <p class="text-[10px] text-gray-400">Takeaway Packaging Essentials</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold rounded-md text-[10px]">⚠ Menipis (80%)</span>
                                    <div class="w-24 bg-gray-200 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-amber-500 h-full" style="width: 80%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="font-bold text-amber-700">120 Pcs</span>
                                    <p class="text-[10px] text-gray-400">Min: 150 Pcs</p>
                                </td>
                                <td class="py-3.5 px-3"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span></td>
                                <td class="py-3.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="restockItem('Paper Cup 12oz')" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-[10px] transition">📦 Restok</button>
                                        <button onclick="editItemStok('Paper Cup 12oz')" class="p-1 hover:bg-gray-200 rounded text-gray-600" title="Edit">✏️</button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Paginasi Tabel -->
                <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100 text-xs">
                    <span class="text-gray-400">Menampilkan 1–7 dari 38 Bahan Baku</span>
                    <div class="flex items-center gap-1">
                        <button class="px-2.5 py-1 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">Sebelumnya</button>
                        <button class="w-7 h-7 bg-[#6b5344] text-white font-bold rounded-lg shadow">1</button>
                        <button onclick="pindahHalaman(2)" class="w-7 h-7 hover:bg-gray-100 font-semibold rounded-lg">2</button>
                        <button onclick="pindahHalaman(3)" class="w-7 h-7 hover:bg-gray-100 font-semibold rounded-lg">3</button>
                        <button onclick="pindahHalaman('next')" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg">Selanjutnya</button>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (1 Kolom): INPUT STOK MASUK BARU & RIWAYAT -->
            <div class="space-y-6">
                
                <!-- Kotak Input Stok Masuk Baru -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-900">Input Stok Masuk Baru</h3>
                        <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full cursor-pointer" onclick="toggleFakturPO()">Faktur PO</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-4">Quick Restock Penerimaan Barang.</p>

                    <form id="formStokMasuk" onsubmit="simpanStokMasuk(event)" class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Supplier / Vendor Penyedia</label>
                            <select id="selectSupplier" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                                <option>PT Sukses Berkah Kopi (Roastery Utama)</option>
                                <option>CV Sentosa Dairy</option>
                                <option>PT Gourmet Prima Indonesia</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Bahan Baku yang Diterima</label>
                            <select id="selectBahanDiterima" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                                <option>Biji Kopi House Blend (Arabica-Robusta)</option>
                                <option>Fresh Milk Greenfields (1L)</option>
                                <option>Sirup Gourmet Karamel (750ml)</option>
                                <option>Oat Milk Barista Edition</option>
                            </select>
                            <p class="text-[10px] text-emerald-600 mt-1 font-semibold">✔ Saat ini sisa 1.2 kg kritis • Dilewati buffer 5.0 kg.</p>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Jumlah Tambahan Stok Masuk</label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="kurangiJumlahStok()" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-xl text-sm flex items-center justify-center">-</button>
                                <input type="number" id="inputJumlahStok" value="10.0" step="0.5" class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold text-center text-gray-900 focus:outline-none">
                                <span class="text-xs font-bold text-gray-600">KG</span>
                                <button type="button" onclick="tambahJumlahStok()" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-xl text-sm flex items-center justify-center">+</button>
                            </div>
                            <div class="flex gap-1.5 mt-2">
                                <button type="button" onclick="tambahNilaiCepat(5)" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-[10px] font-bold text-gray-700">+5 kg</button>
                                <button type="button" onclick="tambahNilaiCepat(10)" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-[10px] font-bold text-gray-700">+10 kg</button>
                                <button type="button" onclick="tambahNilaiCepat(20)" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-[10px] font-bold text-gray-700">+20 kg</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-gray-600 mb-1">EXP Date / Best Before</label>
                                <input type="date" id="inputExpDate" value="2026-10-30" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-600 mb-1">No. Batch / Lot</label>
                                <input type="text" id="inputNoBatch" value="LOT-HB-8991" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Catatan / No. Faktur Pengiriman</label>
                            <div class="relative">
                                <input type="text" id="inputNoFaktur" value="#PO-2024-089" class="w-full pl-3 pr-8 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none">
                                <span class="absolute right-3 top-2.5 text-gray-400 cursor-pointer" title="Scan Barcode" onclick="alert('📸 Membuka pemindai barcode / kamera faktur pengiriman...')">📷</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-[#261c14] hover:bg-black text-white font-bold rounded-xl shadow transition text-center text-xs">
                            💾 Simpan & Update Stok Masuk
                        </button>
                    </form>
                </div>

                <!-- Kotak Riwayat Stok Masuk Terakhir -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-900">Riwayat Stok Masuk Terakhir</h3>
                        <button onclick="lihatSemuaRiwayat()" class="text-[11px] text-[#f97316] font-bold hover:underline">Lihat Semua</button>
                    </div>

                    <div class="space-y-3 text-xs">
                        
                        <!-- Riwayat 1 -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-[10px]">+12L</div>
                                <div>
                                    <p class="font-bold text-gray-900">Fresh Milk Greenfields 1L</p>
                                    <p class="text-[10px] text-gray-400">Penerimaan: Budi Santoso • PT Greenfields</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold text-gray-500 text-right">08:30 WIB<br><span class="text-emerald-600">Hari ini</span></span>
                        </div>

                        <!-- Riwayat 2 -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-[10px]">+50g</div>
                                <div>
                                    <p class="font-bold text-gray-900">Paper Cup 12oz Hot + Lid</p>
                                    <p class="text-[10px] text-gray-400">Penerimaan: Rian (Barista) • PO #PO-4763-8401</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold text-gray-500 text-right">16:45 WIB<br>Kemarin</span>
                        </div>

                        <!-- Riwayat 3 -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex justify-between items-center">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-[10px]">+5 kg</div>
                                <div>
                                    <p class="font-bold text-gray-900">Ethiopia Guji Single Origin</p>
                                    <p class="text-[10px] text-gray-400">Penerimaan: Budi Santoso • Fresh-Roasted</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-semibold text-gray-500 text-right">2 Hari lalu<br>Selesai</span>
                        </div>

                    </div>

                    <!-- Footer Info Bawah Riwayat -->
                    <div class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 flex items-center gap-2">
                        <span>ℹ️</span>
                        <span>Semua log stok masuk diverifikasi dengan fitur fisik & terenkripsi pada audit shift bulanan.</span>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        // 1. Pencarian Stok Bahan
        function cariStokBahan() {
            let input = document.getElementById('inputCariStok').value.toLowerCase();
            let rows = document.querySelectorAll('#tabelInventarisStok tbody tr');

            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                if (text.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // 2. Filter Kategori Bahan
        function filterKategoriBahan(kategori, element) {
            document.getElementById('tabCatSemua').className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap";
            document.getElementById('tabCatBiji').className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap";
            document.getElementById('tabCatSusu').className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap";
            document.getElementById('tabCatSirup').className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap";
            document.getElementById('tabCatBahan').className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap";
            document.getElementById('tabCatPackaging').className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition whitespace-nowrap";

            element.className = "px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition whitespace-nowrap font-bold";

            const rows = document.querySelectorAll('.item-stok');
            rows.forEach(row => {
                let kat = row.getAttribute('data-kategori');
                if (kategori === 'semua' || kat === kategori) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // 3. Filter Status Tabel
        function filterStatusTabel(status) {
            const rows = document.querySelectorAll('.item-stok');
            rows.forEach(row => {
                let st = row.getAttribute('data-status');
                if (st === status || status === 'semua') {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
            alert(`🔍 Menampilkan daftar bahan dengan status: ${status.toUpperCase()}`);
        }

        function urutkanPrioritas() {
            alert("📊 Tabel diurutkan berdasarkan tingkat urgensi stok kritis dan sisa buffer terkecil.");
        }

        // 4. Tombol Aksi Item Tabel
        function restockItem(nama) {
            alert(`📦 Membuka wizard Purchase Order (PO) kilat untuk restock bahan: ${nama}`);
        }

        function pesanSegeraItem(nama) {
            alert(`🚨 PEMESANAN DARURAT: Mengirim notifikasi WhatsApp otomatis ke supplier utama untuk [ ${nama} ]!`);
        }

        function tambahStokItem(nama) {
            alert(`➕ Menambahkan 1 unit satuan standar cepat untuk stok: ${nama}`);
        }

        function editItemStok(nama) {
            alert(`✏️ Membuka form pengaturan detail SKU, harga, dan minimal buffer untuk: ${nama}`);
        }

        // 5. Tombol Form Input Stok Masuk
        function kurangiJumlahStok() {
            let input = document.getElementById('inputJumlahStok');
            let val = parseFloat(input.value);
            if (val > 0.5) {
                input.value = (val - 0.5).toFixed(1);
            }
        }

        function tambahJumlahStok() {
            let input = document.getElementById('inputJumlahStok');
            let val = parseFloat(input.value);
            input.value = (val + 0.5).toFixed(1);
        }

        function tambahNilaiCepat(jumlah) {
            let input = document.getElementById('inputJumlahStok');
            input.value = jumlah.toFixed(1);
        }

        function toggleFakturPO() {
            alert("📄 Mengaktifkan mode pencocokan otomatis nomor Faktur PO dengan database pengiriman supplier.");
        }

        function simpanStokMasuk(e) {
            e.preventDefault();
            let bahan = document.getElementById('selectBahanDiterima').value;
            let jumlah = document.getElementById('inputJumlahStok').value;
            alert(`✅ BERHASIL: Stok masuk untuk [ ${bahan} ] sebanyak [ ${jumlah} kg/L ] telah dicatat dan memperbarui saldo gudang secara real-time!`);
            document.getElementById('formStokMasuk').reset();
        }

        // 6. Tombol Header Atas & Modal
        function cetakLaporanStok() {
            alert("🖨️ Menyiapkan dokumen laporan rekapitulasi persediaan stok gudang untuk dicetak...");
        }

        function bukaModalTambahStok() {
            alert("➕ Membuka jendela dialog registrasi item bahan baku baru ke sistem POS.");
        }

        function bukaStokOpnameModal() {
            alert("📋 Membuka modul opname fisik harian untuk sinkronisasi kasir dan gudang.");
        }

        function bukaUnduhLaporanModal() {
            alert("📥 Mengunduh bundel lengkap laporan mutasi stok format Excel / PDF.");
        }

        function lihatSemuaRiwayat() {
            alert("📂 Membuka arsip lengkap seluruh riwayat log penerimaan barang bulan ini.");
        }

        function pindahHalaman(hal) {
            alert(`📄 Berpindah ke halaman inventaris stok ke-${hal}`);
        }
    </script>

</body>
</html>