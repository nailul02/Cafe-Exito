<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Bahan & Inventaris - Exito Cafe</title>
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
                <a href="{{ route('laporan-penjualan') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    📈 Laporan Penjualan
                </a>
                <a href="{{ route('stok-bahan') }}" class="py-2.5 px-4 rounded-xl bg-[#584437] text-white shadow-sm flex items-center justify-between cursor-pointer">
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
        
        <!-- HEADER ATAS (INFORMASI OUTLET & TOMBOL AKSI CEPAT) -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Manajemen & Inventarisasi Stok Bahan</h2>
                 </div>
                <p class="text-xs text-gray-400 mt-0.5">Monitoring sisa bahan baku, estimasi status buffer minimum, dan pemesanan harian.</p>
            </div>
            
            <!-- Tombol-tombol Aksi Header Atas -->
            <div class="flex items-center gap-2 flex-wrap">
                <button onclick="bukaStokOpname()" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 transition flex items-center gap-1.5 shadow-sm">
                    📋 Stok Opname Cepat
                </button>
                <button onclick="bukaBuatPO()" class="px-3 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 transition flex items-center gap-1.5 shadow-sm">
                    📦 Buat PO Supplier
                </button>
                <button onclick="bukaTambahBahan()" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                    ➕ Tambah Bahan
                </button>
            </div>
        </header>

        <!-- 4 KOTAK RINGKASAN STATISTIK INVENTARIS -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Item Bahan</span>
                    <span class="p-1.5 bg-amber-50 rounded-lg text-amber-800 text-xs">📦</span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">49 <span class="text-xs font-semibold text-gray-500">SKU Aktif</span></h2>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">✔ 100% Terkoneksi ke Resepsi POS</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-wider">Stok Kritis / Perlu</span>
                    <span class="p-1.5 bg-red-50 rounded-lg text-red-600 text-xs font-bold">🚨 Perlu Restock</span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-red-600 tracking-tight">3 <span class="text-xs font-semibold text-gray-500">Bahan di bawah buffer</span></h2>
                    <p class="text-[10px] text-gray-400 mt-1 truncate">Arabica (2), Oat (1), Vanilla (1)</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nilai Total Aset Stok</span>
                    <span class="p-1.5 bg-amber-50 rounded-lg text-amber-800 text-xs">💰</span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Rp 14.850.000</h2>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">+3.2% <span class="text-gray-400 font-normal">valuasi stok minggu lalu</span></p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Buffer Sisa Karyawan / Hari</span>
                    <span class="p-1.5 bg-blue-50 rounded-lg text-blue-600 text-xs">⏳</span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">3,5 <span class="text-xs font-semibold text-gray-500">Hari Operasional</span></h2>
                    <p class="text-[11px] text-gray-400 mt-1">Estimasi pengiriman supplier: <strong class="text-gray-700">Besok</strong></p>
                </div>
            </div>
        </div>

        <!-- GRID UTAMA (KIRI: DAFTAR BAHAN & PENCARIAN | KANAN: RESTOCK CEPAT & LOG) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KOLOM KIRI (2 Kolom): DAFTAR STOK BAHAN -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- Filter Kategori & Status -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 space-y-3">
                    <div class="flex gap-2 text-xs font-semibold overflow-x-auto pb-1">
                        <button onclick="filterKategori('semua', this)" id="catSemua" class="px-3.5 py-1.5 bg-[#6b5344] text-white rounded-xl shadow transition whitespace-nowrap">Semua Bahan (49)</button>
                        <button onclick="filterKategori('kopi', this)" id="catKopi" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition whitespace-nowrap">Biji Kopi (8)</button>
                        <button onclick="filterKategori('susu', this)" id="catSusu" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition whitespace-nowrap">Dairy & Plant Milk (12)</button>
                        <button onclick="filterKategori('sirup', this)" id="catSirup" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition whitespace-nowrap">Sirup & Puree (20)</button>
                    </div>

                    <!-- Pencarian & Filter Cepat Status -->
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <input type="text" id="inputCariBahan" onkeyup="cariBahanInventaris()" placeholder="Cari nama bahan, SKU..." class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#6b5344]">
                            <span class="absolute left-3 top-2.5 text-gray-400 text-xs">🔍</span>
                        </div>
                        <div class="flex gap-1.5 text-xs">
                            <button onclick="filterStatusBahan('semua')" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl text-gray-700">Semua</button>
                            <button onclick="filterStatusBahan('kritis')" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 font-medium rounded-xl text-red-600">🚨 Kritis (3)</button>
                            <button onclick="filterStatusBahan('menipis')" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 font-medium rounded-xl text-amber-700">⚠ Menipis (2)</button>
                            <button onclick="filterStatusBahan('aman')" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 font-medium rounded-xl text-emerald-700">✔ Aman</button>
                        </div>
                    </div>
                </div>

                <!-- DAFTAR KARTU INVENTARIS BAHAN -->
                <div class="space-y-3" id="daftarListBahan">
                    
                    <!-- Item 1: Aman -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 item-bahan" data-kategori="kopi" data-status="aman">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-900">Matcha Ceremonial Grade Uji (100g)</h4>
                                <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full">✔ Status Aman</span>
                            </div>
                            <p class="text-[11px] text-gray-400">SKU: TEP-MTC-005 • Kategori: Bubuk/Teh (Jepang) • Supplier: Kyoto Tea Import</p>
                            <div class="flex gap-4 text-xs pt-1 font-semibold text-gray-700">
                                <span>Sisa Stok Fisik: <strong class="text-emerald-700">6 Kaleng</strong> (Min: 3)</span>
                                <span>Estimasi Bertahan: <strong class="text-gray-900">~ 14 Hari (Normal)</strong></span>
                                <span class="text-gray-400 font-normal">Kadaluarsa: 24 Nov 2024</span>
                            </div>
                        </div>
                        <div>
                            <button onclick="detailBahan('Matcha Ceremonial Grade Uji')" class="px-3.5 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl transition shadow-sm">
                                🔍 Restock Terjadwal
                            </button>
                        </div>
                    </div>

                    <!-- Item 2: Kritis -->
                    <div class="bg-red-50/40 p-4 rounded-2xl shadow-sm border border-red-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 item-bahan" data-kategori="kopi" data-status="kritis">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-900">Biji Kopi Arabica House Blend (Dago Blend)</h4>
                                <span class="bg-red-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full animate-pulse">🚨 KRITIS!</span>
                            </div>
                            <p class="text-[11px] text-gray-500">SKU: CP-BRB-001 • Kemasan Valve Bag 500gr • Supplier: Dago Roastery</p>
                            <div class="flex gap-4 text-xs pt-1 font-semibold text-gray-700">
                                <span>Sisa Stok Fisik: <strong class="text-red-600 text-sm">1.8 kg</strong> (Min: 5.0kg)</span>
                                <span class="text-red-600">⚠ Estimasi Bertahan: ~ 1.2 Hari</span>
                                <span class="text-gray-500">Reorder Batch Baru: 16 Nov 2024 (Segera!)</span>
                            </div>
                        </div>
                        <div>
                            <button onclick="pesanCepatSupplier('Biji Kopi Arabica House Blend')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition shadow">
                                🛒 Pesan Cepat
                            </button>
                        </div>
                    </div>

                    <!-- Item 3: Menipis -->
                    <div class="bg-amber-50/30 p-4 rounded-2xl shadow-sm border border-amber-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 item-bahan" data-kategori="susu" data-status="menipis">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-900">Oatside Barista Blend (1L)</h4>
                                <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">⚠ MENIPIS</span>
                            </div>
                            <p class="text-[11px] text-gray-500">SKU: MLK-OAT-001 • 1 Box = 12 PACK • Supplier: CV Sentosa Dairy</p>
                            <div class="flex gap-4 text-xs pt-1 font-semibold text-gray-700">
                                <span>Sisa Stok Fisik: <strong class="text-amber-700">4 Karton</strong> (Min: 10)</span>
                                <span class="text-amber-700">Estimasi Bertahan: ~ 2.8 Hari</span>
                                <span class="text-gray-500">Kadaluarsa: 14 Mar 2025</span>
                            </div>
                        </div>
                        <div>
                            <button onclick="pesanCepatSupplier('Oatside Barista Blend')" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow">
                                📦 Order Tambahan
                            </button>
                        </div>
                    </div>

                    <!-- Item 4: Kritis -->
                    <div class="bg-red-50/40 p-4 rounded-2xl shadow-sm border border-red-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 item-bahan" data-kategori="sirup" data-status="kritis">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-900">Monin French Vanilla Syrup (700ml)</h4>
                                <span class="bg-red-600 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full">🚨 KRITIS!</span>
                            </div>
                            <p class="text-[11px] text-gray-500">SKU: SYP-VAN-004 • Botol Kaca 700ml • Supplier: PT Gourmet Prima</p>
                            <div class="flex gap-4 text-xs pt-1 font-semibold text-gray-700">
                                <span>Sisa Stok Fisik: <strong class="text-red-600 text-sm">1 Botol</strong> (Min: 4)</span>
                                <span class="text-red-600 font-bold">~ 1 Hari (Peak Rush)</span>
                                <span class="text-gray-500">Kadaluarsa: 10 Des 2026</span>
                            </div>
                        </div>
                        <div>
                            <button onclick="pesanCepatSupplier('Monin French Vanilla Syrup')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition shadow">
                                🛒 Pesan Cepat
                            </button>
                        </div>
                    </div>

                    <!-- Item 5: Aman -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 item-bahan" data-kategori="susu" data-status="aman">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-900">Greenfields Fresh Milk Whole (1L)</h4>
                                <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full">✔ Aman</span>
                            </div>
                            <p class="text-[11px] text-gray-400">SKU: MLK-FRE-001 • Pasteurized Chilled • Supplier: CV Sentosa Dairy</p>
                            <div class="flex gap-4 text-xs pt-1 font-semibold text-gray-700">
                                <span>Sisa Stok Fisik: <strong class="text-emerald-700">28 Karton</strong> (Min: 15)</span>
                                <span>Estimasi Bertahan: ~ 5.6 Hari</span>
                                <span class="text-gray-400">Kadaluarsa: 29 Okt 2025</span>
                            </div>
                        </div>
                        <div>
                            <button onclick="detailBahan('Greenfields Fresh Milk')" class="px-3.5 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl transition shadow-sm">
                                📋 Stok Cukup (6 Hari)
                            </button>
                        </div>
                    </div>

                </div>

            </div>

            <!-- KOLOM KANAN (1 Kolom): RESTOCK CEPAT SUPPLIER & LOG SHIFT -->
            <div class="space-y-6">
                
                <!-- Kotak Restock Cepat Supplier -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-900">Restock Cepat Supplier</h3>
                        <span class="bg-amber-50 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full">3 PO Butuh Aksi</span>
                    </div>

                    <div class="space-y-4 text-xs">
                        <!-- Supplier 1 -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900">Dago Roastery</span>
                                <span class="bg-red-100 text-red-700 text-[9px] font-bold px-2 py-0.5 rounded">Prio: Prioritas</span>
                            </div>
                            <p class="text-[10px] text-gray-500">Local Bean Uji & Custom Blend (Dago Roastery)</p>
                            <div class="p-2 bg-white rounded-lg border border-gray-200 text-[10px] font-mono text-gray-700">
                                Draft Order Otomatis: <br>• 5x 500g Arabica House Blend (Rp 5.840.000)
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button onclick="kirimPO('Dago Roastery')" class="flex-1 py-1.5 bg-[#6b5344] hover:bg-[#584437] text-white font-bold rounded-lg transition text-center">
                                    🚀 Kirim PO
                                </button>
                                <button onclick="editDraftPO('Dago Roastery')" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 font-bold rounded-lg text-gray-700">
                                    Format
                                </button>
                            </div>
                        </div>

                        <!-- Supplier 2 -->
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900">CV Sentosa Dairy</span>
                                <span class="bg-amber-100 text-amber-800 text-[9px] font-bold px-2 py-0.5 rounded">Stock Dikit</span>
                            </div>
                            <p class="text-[10px] text-gray-500">Local Fresh Milk, Susu & Dairy</p>
                            <div class="p-2 bg-white rounded-lg border border-gray-200 text-[10px] font-mono text-gray-700">
                                Draft Order Otomatis: <br>• 8x Carton Oatside Barista Edition
                            </div>
                            <button onclick="kirimPO('CV Sentosa Dairy')" class="w-full py-1.5 bg-gray-900 hover:bg-black text-white font-bold rounded-lg transition text-center">
                                Konfirmasi & Kirim PO (Rp 5.340.000)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kotak Log & Waste Shift Hari Ini -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-900">Log & Waste Shift Hari Ini</h3>
                        <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-full">Live</span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between font-bold text-gray-900 mb-0.5">
                                <span>13:45 • Barista Arka</span>
                                <span class="text-red-600 text-[10px]">−80gram</span>
                            </div>
                            <p class="text-[11px] text-gray-600">Kalibrasi Grinder & Espressomate yang (Ekstraksi Meleset @UID)</p>
                            <span class="inline-block mt-1 text-[9px] bg-red-100 text-red-700 font-semibold px-2 py-0.5 rounded">Kategori: Spillage / Quality Check</span>
                        </div>

                        <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between font-bold text-gray-900 mb-0.5">
                                <span>11:20 • Kasir Budi</span>
                                <span class="text-amber-700 text-[10px]">−1 Cup</span>
                            </div>
                            <p class="text-[11px] text-gray-600">Spilled / cancelled cup (Susu Full Cream basi saat steaming test)</p>
                            <span class="inline-block mt-1 text-[9px] bg-amber-100 text-amber-800 font-semibold px-2 py-0.5 rounded">Kategori: Waste Operasional</span>
                        </div>

                        <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between font-bold text-gray-900 mb-0.5">
                                <span>08:30 • SPV Dimas</span>
                                <span class="text-emerald-700 text-[10px]">100% Count</span>
                            </div>
                            <p class="text-[11px] text-gray-600">Opening audit fisik dus Kopi Arabica (Sesuai PO, Tidak ada pengurangan selisih fisik)</p>
                            <span class="inline-block mt-1 text-[9px] bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded">Kategori: Correct Count</span>
                        </div>
                    </div>

                    <!-- Tombol Laporan Bawah Log -->
                    <div class="mt-4 pt-3 border-t border-gray-100 space-y-2">
                        <button onclick="unduhLembarOpname()" class="w-full py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl text-xs transition shadow-sm">
                            📄 Unduh Lembar Stok Opname PDF
                        </button>
                        <button onclick="rekapBulanan()" class="w-full py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 font-bold rounded-xl text-xs transition shadow-sm">
                            📊 Rekap Pemakaian Bulanan (EXCEL/CSV)
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- JAVASCRIPT / INTERAKTIF FITUR PENUH -->
    <script>
        // 1. Filter Kategori Bahan
        function filterKategori(kategori, element) {
            document.getElementById('catSemua').className = "px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition whitespace-nowrap";
            document.getElementById('catKopi').className = "px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition whitespace-nowrap";
            document.getElementById('catSusu').className = "px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition whitespace-nowrap";
            document.getElementById('catSirup').className = "px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition whitespace-nowrap";

            element.className = "px-3.5 py-1.5 bg-[#6b5344] text-white rounded-xl shadow transition whitespace-nowrap font-bold";

            const items = document.querySelectorAll('.item-bahan');
            items.forEach(item => {
                let kat = item.getAttribute('data-kategori');
                if (kategori === 'semua' || kat === kategori) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        }

        // 2. Filter Status Bahan (Kritis, Menipis, Aman)
        function filterStatusBahan(status) {
            const items = document.querySelectorAll('.item-bahan');
            items.forEach(item => {
                let stat = item.getAttribute('data-status');
                if (status === 'semua' || stat === status) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        }

        // 3. Pencarian Inventaris
        function cariBahanInventaris() {
            let input = document.getElementById('inputCariBahan').value.toLowerCase();
            let items = document.querySelectorAll('.item-bahan');

            items.forEach(item => {
                let teks = item.textContent.toLowerCase();
                if (teks.includes(input)) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        }

        // 4. Tombol Aksi Header
        function bukaStokOpname() {
            alert("📋 Membuka panel interaktif Stok Opname Cepat untuk pencatatan fisik shift hari ini.");
        }

        function bukaBuatPO() {
            alert("📦 Membuka wizard pembuatan Purchase Order (PO) baru untuk dikirim ke berbagai vendor.");
        }

        function bukaTambahBahan() {
            alert("➕ Membuka form penambahan SKU inventaris / bahan baku baru ke sistem POS.");
        }

        // 5. Tombol Item & Supplier
        function detailBahan(nama) {
            alert(`🔍 Membuka detail riwayat dan grafik konsumsi bulanan untuk bahan: ${nama}`);
        }

        function pesanCepatSupplier(nama) {
            const konfirmasi = confirm(`🛒 Apakah Anda ingin membuat pesanan cepat untuk [ ${nama} ] ke supplier utama sekarang?`);
            if (konfirmasi) {
                alert(`✅ Pesanan cepat untuk [ ${nama} ] berhasil dibuat dan masuk ke daftar PO aktif!`);
            }
        }

        function kirimPO(supplier) {
            alert(`🚀 Berhasil mengirim Purchase Order otomatis ke vendor ${supplier}! Notifikasi terkirim ke WhatsApp supplier.`);
        }

        function editDraftPO(supplier) {
            alert(`📝 Membuka pengeditan format draft order untuk supplier ${supplier}.`);
        }

        // 6. Tombol Bawah Laporan & Rekap
        function unduhLembarOpname() {
            alert("📄 Mengunduh Lembar Dokumen Stok Opname berformat PDF...");
        }

        function rekapBulanan() {
            alert("📊 Mengekspor data rekap pemakaian bahan bulanan ke format Excel/CSV...");
        }
    </script>

</body>
</html>