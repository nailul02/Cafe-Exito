<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data & Ketersediaan Menu - Cafe Exito</title>
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
                <a href="{{ route('kelola-menu') }}" class="py-2.5 px-4 rounded-xl bg-[#f97316] text-white shadow-sm flex items-center gap-3 cursor-pointer font-semibold">
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
            <a href="#" onclick="alert('Membuka pusat bantuan kelola menu...')" class="flex items-center gap-2 text-gray-200 hover:text-white px-2 py-1">
                <span>❓</span> Bantuan
            </a>
            <a href="{{ route('proses-login') }}" class="w-full py-2 px-3 bg-red-600/20 hover:bg-red-600/40 text-red-200 rounded-xl transition flex items-center justify-center gap-2 font-bold cursor-pointer text-center">
                🚪 Logout
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA KELOLA MENU -->
    <main class="flex-1 p-6 overflow-y-auto">
        
        <!-- HEADER ATAS -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 bg-white p-3.5 rounded-2xl shadow-sm border border-gray-100 gap-3">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700">
                    <span>📍 Operasional Store #01 • Katalog & Inventaris POS</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Kolom Pencarian Menu -->
                <div class="relative w-full sm:w-64">
                    <input type="text" id="inputCariMenu" onkeyup="cariDataMenu()" placeholder="Cari menu makanan/minuman..." class="w-full pl-8 pr-8 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                    <span class="absolute left-2.5 top-2 text-gray-400 text-xs">🔍</span>
                    <span class="absolute right-2.5 top-2 text-gray-400 text-xs cursor-pointer" onclick="document.getElementById('inputCariMenu').value=''; cariDataMenu();">✕</span>
                </div>
                <button onclick="bukaModalTambahMenu()" class="px-4 py-1.5 bg-[#261c14] hover:bg-black text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5 cursor-pointer">
                    ➕ Tambah Menu Baru
                </button>
            </div>
        </header>

        <!-- SUB-HEADER / JUDUL HALAMAN -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 gap-2">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Kelola Data & Ketersediaan Menu</h2>
                <p class="text-xs text-gray-400">Atur status ketersediaan (In-Stock / Sold-Out), modifikasi harga jual, komposisi bahan baku (resep), dan tambah menu baru.</p>
            </div>
            
            <div class="flex items-center gap-2 bg-emerald-50 px-3.5 py-2 rounded-xl border border-emerald-200 text-xs font-semibold text-emerald-800 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Terakhir Sinkron: 10:18 WIB</span>
            </div>
        </div>

        <!-- 4 KOTAK RINGKASAN STATISTIK MENU -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Menu Aktif</span>
                    <h2 class="text-xl font-bold text-gray-900 mt-1">42 <span class="text-xs font-normal text-gray-500">Menu</span></h2>
                    <p class="text-[10px] text-emerald-600 font-semibold mt-1">Tersebar di POS & QR</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center font-bold">📋</div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-wider">Menu Habis / Kasus!</span>
                    <h2 class="text-xl font-bold text-red-600 mt-1">3 <span class="text-xs font-normal text-gray-500">Menu</span></h2>
                    <p class="text-[10px] text-red-500 font-semibold mt-1">Butuh Restok Bahan</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold">⚠</div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kategori Populer</span>
                    <h2 class="text-sm font-bold text-gray-900 mt-1 leading-tight">Coffee & Espresso</h2>
                    <p class="text-[10px] text-gray-500 mt-1">58% Total Order Toko</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">☕</div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Margin Rata-rata</span>
                    <h2 class="text-xl font-bold text-emerald-600 mt-1">68.5%</h2>
                    <p class="text-[10px] text-gray-400 mt-1">Food cost: 31.5%</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">📈</div>
            </div>
        </div>

        <!-- TAB KATEGORI KONTROL & FILTER -->
        <div class="flex gap-2 text-xs font-semibold overflow-x-auto pb-2 mb-4">
            <button onclick="filterKategori('semua', this)" id="catSemua" class="px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition cursor-pointer">Semua Kategori (42)</button>
            <button onclick="filterKategori('espresso', this)" id="catEspresso" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer">Signature Espresso (14)</button>
            <button onclick="filterKategori('manual', this)" id="catManual" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer">Manual Brew (8)</button>
            <button onclick="filterKategori('noncoffee', this)" id="catNonCoffee" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer">Non-Coffee & Tea (12)</button>
            <button onclick="filterKategori('pastry', this)" id="catPastry" class="px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer">Pastry & Bakery (8)</button>
        </div>

        <!-- FILTER LANJUTAN STATUS STOK -->
        <div class="bg-white p-3.5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between gap-2 text-xs mb-6 overflow-x-auto">
            <div class="flex items-center gap-2">
                <span class="text-gray-400 font-bold">Filter Ketersediaan:</span>
                <button onclick="filterStatusStok('semua')" class="px-3 py-1 bg-gray-900 text-white font-semibold rounded-xl cursor-pointer">Semua</button>
                <button onclick="filterStatusStok('tersedia')" class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-xl cursor-pointer">Tersedia (In-Stock)</button>
                <button onclick="filterStatusStok('habis')" class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-xl cursor-pointer">Habis (Sold-Out)</button>
            </div>
            <div class="text-gray-400 text-[11px]">
                Menampilkan 6 dari 42 Menu
            </div>
        </div>

        <!-- GRID UTAMA (KIRI: DAFTAR MENU | KANAN: PANEL EDIT & MODIFIKASI DETAIL) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- KOLOM KIRI (7 Kolom): DAFTAR KARTU MENU (KLIK KARTU LANGSUNG AKTIF WARNA ORANYE) -->
            <div class="lg:col-span-7 space-y-3.5" id="daftarMenuContainer">
                
                <!-- Menu 1: Iced Spanish Latte -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between gap-4 hover:border-gray-300 transition item-menu cursor-pointer" id="card-COF-001" onclick="pilihMenuEdit('Iced Spanish Latte', 'COF-001', 'Rp 32.000', 'tersedia', 'card-COF-001')" data-nama="iced spanish latte" data-status="tersedia">
                    <div class="flex items-center gap-3.5">
                        <div class="w-16 h-16 rounded-xl bg-gray-200 bg-cover bg-center shrink-0 border" style="background-image: url('https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=200&q=80')"></div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-[10px] font-mono font-bold text-gray-400">COF-001</span>
                                <span class="text-[9px] px-2 py-0.5 bg-amber-50 text-amber-800 font-bold rounded-full">Signature Espresso</span>
                            </div>
                            <h3 class="font-bold text-gray-900 text-sm">Iced Spanish Latte</h3>
                            <div class="flex items-center gap-2 mt-1 text-xs">
                                <span class="font-bold text-gray-900">Rp 32.000</span>
                                <span class="text-gray-400 line-through text-[10px]">HPP: Rp 9.500</span>
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-bold rounded">Bahan: Fresh Milk (Aman)</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3" onclick="event.stopPropagation()">
                        <div class="flex flex-col items-end gap-1">
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[9px] font-bold">ON POS</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer" onchange="toggleStatusMenu(this, 'Iced Spanish Latte')">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                        <button onclick="pilihMenuEdit('Iced Spanish Latte', 'COF-001', 'Rp 32.000', 'tersedia', 'card-COF-001')" class="p-2.5 bg-amber-400 hover:bg-amber-500 rounded-xl text-gray-950 transition cursor-pointer shadow" title="Edit Menu">
                            ✏️
                        </button>
                    </div>
                </div>

                <!-- Menu 2: Caramel Macchiato (Default Terpilih) -->
                <div class="bg-white p-4 rounded-2xl shadow-md border-2 border-[#f97316] flex items-center justify-between gap-4 item-menu relative cursor-pointer" id="card-COF-004" onclick="pilihMenuEdit('Caramel Macchiato', 'COF-004', 'Rp 38.000', 'habis', 'card-COF-004')" data-nama="caramel macchiato" data-status="habis">
                    <span class="badge-diedit absolute -top-3 right-4 bg-[#f97316] text-white text-[9px] font-bold px-2.5 py-0.5 rounded-full shadow">Sedang Diedit di Panel Kanan</span>
                    <div class="flex items-center gap-3.5">
                        <div class="w-16 h-16 rounded-xl bg-gray-200 bg-cover bg-center shrink-0 border" style="background-image: url('https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=200&q=80')"></div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-[10px] font-mono font-bold text-gray-400">COF-004</span>
                                <span class="text-[9px] px-2 py-0.5 bg-amber-50 text-amber-800 font-bold rounded-full">Signature Espresso</span>
                            </div>
                            <h3 class="font-bold text-gray-900 text-sm">Caramel Macchiato</h3>
                            <div class="flex items-center gap-2 mt-1 text-xs">
                                <span class="font-bold text-gray-900">Rp 38.000</span>
                                <span class="text-gray-400 line-through text-[10px]">HPP: Rp 12.000</span>
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[9px] font-bold rounded">Bahan Habis: Sirup Gourmet Karamel</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3" onclick="event.stopPropagation()">
                        <div class="flex flex-col items-end gap-1">
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-[9px] font-bold">OFF POS</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" onchange="toggleStatusMenu(this, 'Caramel Macchiato')">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                        <button onclick="pilihMenuEdit('Caramel Macchiato', 'COF-004', 'Rp 38.000', 'habis', 'card-COF-004')" class="p-2.5 bg-amber-400 hover:bg-amber-500 rounded-xl text-gray-950 transition cursor-pointer shadow" title="Edit Menu">
                            ✏️
                        </button>
                    </div>
                </div>

                <!-- Menu 3: Manual Brew V60 -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between gap-4 hover:border-gray-300 transition item-menu cursor-pointer" id="card-MBR-001" onclick="pilihMenuEdit('Manual Brew V60 (Ethiopia Guji)', 'MBR-001', 'Rp 36.000', 'tersedia', 'card-MBR-001')" data-nama="manual brew v60" data-status="tersedia">
                    <div class="flex items-center gap-3.5">
                        <div class="w-16 h-16 rounded-xl bg-gray-200 bg-cover bg-center shrink-0 border" style="background-image: url('https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=200&q=80')"></div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-[10px] font-mono font-bold text-gray-400">MBR-001</span>
                                <span class="text-[9px] px-2 py-0.5 bg-blue-50 text-blue-800 font-bold rounded-full">Manual Brew</span>
                            </div>
                            <h3 class="font-bold text-gray-900 text-sm">Manual Brew V60 (Ethiopia Guji)</h3>
                            <div class="flex items-center gap-2 mt-1 text-xs">
                                <span class="font-bold text-gray-900">Rp 36.000</span>
                                <span class="text-gray-400 line-through text-[10px]">HPP: Rp 14.500</span>
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-bold rounded">Bahan: Ethiopia Giles (250g)</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3" onclick="event.stopPropagation()">
                        <div class="flex flex-col items-end gap-1">
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[9px] font-bold">ON POS</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer" onchange="toggleStatusMenu(this, 'Manual Brew V60')">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                        <button onclick="pilihMenuEdit('Manual Brew V60 (Ethiopia Guji)', 'MBR-001', 'Rp 36.000', 'tersedia', 'card-MBR-001')" class="p-2.5 bg-amber-400 hover:bg-amber-500 rounded-xl text-gray-950 transition cursor-pointer shadow" title="Edit Menu">
                            ✏️
                        </button>
                    </div>
                </div>

                <!-- Menu 4: Butter Croissant -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between gap-4 hover:border-gray-300 transition item-menu cursor-pointer" id="card-PST-002" onclick="pilihMenuEdit('Butter Croissant Frozen Bake', 'PST-002', 'Rp 28.000', 'tersedia', 'card-PST-002')" data-nama="butter croissant" data-status="tersedia">
                    <div class="flex items-center gap-3.5">
                        <div class="w-16 h-16 rounded-xl bg-gray-200 bg-cover bg-center shrink-0 border" style="background-image: url('https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=200&q=80')"></div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-[10px] font-mono font-bold text-gray-400">PST-002</span>
                                <span class="text-[9px] px-2 py-0.5 bg-purple-50 text-purple-800 font-bold rounded-full">Pastry & Bakery</span>
                            </div>
                            <h3 class="font-bold text-gray-900 text-sm">Butter Croissant Frozen Bake</h3>
                            <div class="flex items-center gap-2 mt-1 text-xs">
                                <span class="font-bold text-gray-900">Rp 28.000</span>
                                <span class="text-gray-400 line-through text-[10px]">HPP: Rp 11.000</span>
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-bold rounded">Stok Sisa: 14 Pcs</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3" onclick="event.stopPropagation()">
                        <div class="flex flex-col items-end gap-1">
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[9px] font-bold">ON POS</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer" onchange="toggleStatusMenu(this, 'Butter Croissant')">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                        <button onclick="pilihMenuEdit('Butter Croissant Frozen Bake', 'PST-002', 'Rp 28.000', 'tersedia', 'card-PST-002')" class="p-2.5 bg-amber-400 hover:bg-amber-500 rounded-xl text-gray-950 transition cursor-pointer shadow" title="Edit Menu">
                            ✏️
                        </button>
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN (5 Kolom): PANEL EDIT & MODIFIKASI DETAIL MENU -->
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">DETAIL & MODIFIKASI</span>
                            <h3 id="panelEditJudul" class="text-sm font-bold text-gray-900">Edit Menu: Caramel Macchiato</h3>
                        </div>
                        <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full">Terpilih</span>
                    </div>

                    <!-- THUMBNAIL FOTO MENU POS -->
                    <div class="mb-4">
                        <p class="text-[11px] font-bold text-gray-700 mb-1">Thumbnail Foto Menu POS</p>
                        <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="w-14 h-14 rounded-lg bg-cover bg-center border shrink-0" style="background-image: url('https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=150&q=80')"></div>
                            <div class="flex-1">
                                <p class="text-[10px] text-gray-500">Rasio 1:1, Max 2MB (JPG/PNG)</p>
                                <button onclick="gantiFotoProduk()" class="mt-1 px-3 py-1 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-[11px] font-bold rounded-lg shadow-sm transition cursor-pointer">
                                    📷 Ganti Foto Produk
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FORM INPUT NAMA & SKU -->
                    <div class="grid grid-cols-2 gap-3 mb-3 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Nama Menu:</label>
                            <input type="text" id="inputNamaMenu" value="Caramel Macchiato" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Kode SKU:</label>
                            <input type="text" id="inputSkuMenu" value="COF-004" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-mono text-gray-800">
                        </div>
                    </div>

                    <!-- KATEGORI & PAJAK POS -->
                    <div class="grid grid-cols-2 gap-3 mb-3 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Kategori POS:</label>
                            <select class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800">
                                <option>Signature Espresso</option>
                                <option>Manual Brew</option>
                                <option>Non-Coffee & Tea</option>
                                <option>Pastry & Bakery</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Pajak PBF Nyata:</label>
                            <div class="px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-xl font-semibold text-emerald-800 flex items-center justify-between">
                                <span>Termasuk PBI 10%</span>
                                <span>✔</span>
                            </div>
                        </div>
                    </div>

                    <!-- HARGA JUAL & HPP -->
                    <div class="grid grid-cols-2 gap-3 mb-3 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Harga Jual (POS):</label>
                            <input type="text" id="inputHargaJual" value="Rp 38.000" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-bold text-gray-900">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">HPP / Food Cost:</label>
                            <input type="text" value="Rp 12.000" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-600">
                        </div>
                    </div>
                    <div class="mb-4 text-[11px] text-emerald-700 font-semibold bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200 flex justify-between">
                        <span>Gross Profit Margin:</span>
                        <span>68.4% (Margin Sehat)</span>
                    </div>

                    <!-- SAKLAR TAMPILAN APLIKASI KASIR & QR TABLE -->
                    <div class="space-y-2 mb-4 text-xs border-t border-gray-100 pt-3">
                        <label class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                            <span class="font-semibold text-gray-800">Tampilkan di Aplikasi Kasir (POS)<br><span class="text-[9px] text-gray-400 font-normal">Muncul pada katalog tablet kasir bar depan</span></span>
                            <input type="checkbox" checked class="toggle-pos w-9 h-5 rounded-full cursor-pointer accent-[#f97316]">
                        </label>
                        <label class="flex items-center justify-between p-2 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                            <span class="font-semibold text-gray-800">Tampilkan di QR Table Order<br><span class="text-[9px] text-gray-400 font-normal">Dapat dipesan pelanggan via QR meja</span></span>
                            <input type="checkbox" checked class="toggle-qr w-9 h-5 rounded-full cursor-pointer accent-[#f97316]">
                        </label>
                    </div>

                    <!-- KOMPOSISI BAHAN RESEP (BOM) -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1.5">
                            <p class="text-[11px] font-bold text-gray-700">Komposisi Bahan Resep (BOM)</p>
                            <button onclick="tambahBahanBOM()" class="text-[10px] font-bold text-[#f97316] hover:underline cursor-pointer">+ Bahan</button>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded-xl border border-gray-200">
                                <div>
                                    <p class="font-bold text-gray-800">Espresso Blend (House Blend)</p>
                                    <p class="text-[9px] text-gray-400">18 gram</p>
                                </div>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">Stok: Baik</span>
                            </div>
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded-xl border border-gray-200">
                                <div>
                                    <p class="font-bold text-gray-800">Fresh Milk Pastikurasimi</p>
                                    <p class="text-[9px] text-gray-400">180 ml</p>
                                </div>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">Stok: Aman</span>
                            </div>
                            <div class="flex justify-between items-center p-2 bg-red-50 rounded-xl border border-red-200">
                                <div>
                                    <p class="font-bold text-red-900">Sirup Gourmet Karamel</p>
                                    <p class="text-[9px] text-red-600">30 ml</p>
                                </div>
                                <span class="px-2 py-0.5 bg-red-600 text-white text-[10px] font-bold rounded">STOK KOSONG</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-amber-700 mt-2 bg-amber-50 p-2 rounded-xl border border-amber-200">
                            ℹ️ Sistem otomatis menonaktifkan menu ini di kasir saat sirup habis.
                        </p>
                    </div>

                    <!-- OPSI TAMBAHAN KASIR (MODIFIERS) -->
                    <div class="mb-4">
                        <p class="text-[11px] font-bold text-gray-700 mb-1.5">Opsi Tambahan Kasir (Modifiers):</p>
                        <div class="flex flex-wrap gap-1.5 text-xs">
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-900 border border-amber-200 rounded-lg font-semibold flex items-center gap-1">Extra Espresso Shot (+Rp 6k) <button class="text-gray-400 hover:text-red-600">×</button></span>
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-900 border border-amber-200 rounded-lg font-semibold flex items-center gap-1">Oat Milk Swap (+Rp 8k) <button class="text-gray-400 hover:text-red-600">×</button></span>
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-900 border border-amber-200 rounded-lg font-semibold flex items-center gap-1">Less Sweet 50% <button class="text-gray-400 hover:text-red-600">×</button></span>
                            <button onclick="tambahOpsiModifier()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded-lg font-bold cursor-pointer">+ Opsi Baru</button>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI SIMPAN / RESET -->
                    <div class="flex gap-2">
                        <button onclick="resetFormMenu()" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition text-center cursor-pointer">
                            Batal / Reset
                        </button>
                        <button onclick="simpanPerubahanMenu()" class="flex-1 py-2.5 bg-[#261c14] hover:bg-black text-white font-bold rounded-xl text-xs transition shadow cursor-pointer text-center">
                            💾 Simpan Menu
                        </button>
                    </div>

                    <!-- AUDIT LOG PERUBAHAN TERAKHIR -->
                    <div class="mt-4 pt-3 border-t border-gray-100 text-[10px] text-gray-400">
                        <p class="font-bold text-gray-700">🕒 Audit Log Perubahan Terakhir:</p>
                        <p>Budi Santoso menonaktifkan Caramel Macchiato karena stok Karamel habis • Hari ini, 10:18 WIB via Tablet POS-01.</p>
                    </div>

                </div>
            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        // 1. Sinkronisasi POS Kasir
        function sinkronisasiPOS() {
            alert("🔄 Menyinkronkan ulang database menu, resep BOM, dan ketersediaan stok ke seluruh tablet POS kasir dan QR table...");
        }

        // 2. Tombol Tambah Menu Baru
        function bukaModalTambahMenu() {
            const namaBaru = prompt("Masukkan nama menu baru yang ingin ditambahkan:");
            if (namaBaru) {
                alert(`✅ Menu baru [ ${namaBaru} ] berhasil dibuat dan ditambahkan ke katalog POS!`);
            }
        }

        // 3. Filter Kategori Tab
        function filterKategori(kategori, element) {
            const tabs = ['catSemua', 'catEspresso', 'catManual', 'catNonCoffee', 'catPastry'];
            tabs.forEach(id => {
                let el = document.getElementById(id);
                if(el) el.className = "px-4 py-2 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl transition cursor-pointer";
            });
            element.className = "px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition cursor-pointer font-bold";
            alert(`📂 Memfilter katalog berdasarkan kategori: [ ${kategori.toUpperCase()} ]`);
        }

        // 4. Filter Ketersediaan Stok (Semua, Tersedia, Habis)
        function filterStatusStok(status) {
            const items = document.querySelectorAll('.item-menu');
            items.forEach(item => {
                let st = item.getAttribute('data-status');
                if (status === 'semua' || st === status) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        }

        // 5. Pencarian Menu
        function cariDataMenu() {
            let input = document.getElementById('inputCariMenu').value.toLowerCase();
            let items = document.querySelectorAll('.item-menu');

            items.forEach(item => {
                let nama = item.getAttribute('data-nama');
                if (nama.includes(input)) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        }

        // 6. Toggle Status Saklar On/Off POS per Menu
        function toggleStatusMenu(checkbox, namaMenu) {
            let statusStr = checkbox.checked ? "AKTIF (ON POS)" : "NONAKTIF (OFF POS)";
            alert(`⚡ Status ketersediaan POS untuk menu [ ${namaMenu} ] diubah menjadi: ${statusStr}`);
        }

        // 7. Pilih Menu untuk Diedit di Panel Kanan (Interaktif Ubah Warna Border & Badge Aktif)
        function pilihMenuEdit(nama, sku, harga, status, cardId) {
            // Reset semua kartu menu ke border normal & hapus badge edit
            const semuaKartu = document.querySelectorAll('.item-menu');
            semuaKartu.forEach(kartu => {
                kartu.className = "bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between gap-4 hover:border-gray-300 transition item-menu relative cursor-pointer";
                const badgeLama = kartu.querySelector('.badge-diedit');
                if (badgeLama) badgeLama.remove();
            });

            // Berikan border oranye dan badge pada kartu yang diklik
            const kartuAktif = document.getElementById(cardId);
            if (kartuAktif) {
                kartuAktif.className = "bg-white p-4 rounded-2xl shadow-md border-2 border-[#f97316] flex items-center justify-between gap-4 item-menu relative cursor-pointer";
                
                let badge = document.createElement('span');
                badge.className = "badge-diedit absolute -top-3 right-4 bg-[#f97316] text-white text-[9px] font-bold px-2.5 py-0.5 rounded-full shadow";
                badge.innerText = "Sedang Diedit di Panel Kanan";
                kartuAktif.appendChild(badge);
            }

            // Update isi form panel kanan
            document.getElementById('panelEditJudul').innerText = `Edit Menu: ${nama}`;
            document.getElementById('inputNamaMenu').value = nama;
            document.getElementById('inputSkuMenu').value = sku;
            document.getElementById('inputHargaJual').value = harga;
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // 8. Ganti Foto Produk
        function gantiFotoProduk() {
            alert("📷 Membuka galeri file manager untuk mengunggah thumbnail foto baru (Rasio 1:1)...");
        }

        // 9. Tambah Bahan BOM / Modifier
        function tambahBahanBOM() {
            alert("➕ Membuka daftar inventaris gudang untuk menambahkan komposisi bahan baku (BOM) baru.");
        }

        function tambahOpsiModifier() {
            alert("➕ Menambahkan opsi tambahan (modifier) baru untuk pilihan pelanggan.");
        }

        // 10. Simpan & Reset Form Menu
        function simpanPerubahanMenu() {
            let nama = document.getElementById('inputNamaMenu').value;
            alert(`✅ Perubahan pada menu [ ${nama} ] berhasil disimpan ke server cloud POS!`);
        }

        function resetFormMenu() {
            alert("↺ Mengatur ulang form modifikasi menu.");
        }
    </script>

</body>
</html>