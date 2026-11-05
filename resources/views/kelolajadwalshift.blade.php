<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal Shift Karyawan - Cafe Exito</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <!-- SIDEBAR KIRI -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between p-6 fixed inset-y-0 left-0 z-20">
        <div>
            <!-- Header Profil Manager / Admin di Sidebar -->
            <div class="flex items-center gap-3 bg-[#fdfaf7] border border-[#f3ece6] p-3 rounded-2xl mb-8 shadow-sm">
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" alt="Avatar" class="w-10 h-10 rounded-full object-cover">
                <div class="overflow-hidden">
                    <h4 class="text-xs font-bold text-gray-800 truncate">Cafe Éxito</h4>
                    <p class="text-[11px] text-gray-500 truncate">Shift Manager • <span class="text-emerald-600 font-medium">On Duty</span></p>
                    <p class="text-[10px] text-gray-400">● Budi Santoso</p>
                </div>
            </div>

            <!-- Menu Navigasi Sesuai Desain -->
            <nav class="flex flex-col gap-1.5 text-sm">
                <a href="{{ route('dashboard-admin') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('kelola-menu') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Kelola Menu
                </a>
                <a href="{{ route('kelola-stok') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Kelola Stok
                </a>
                <a href="{{ route('kelola-jadwal-shift') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-500 text-white font-medium shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Kelola Jadwal Shift
                </a>
                <a href="{{ route('kelola-absensi') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-6 9l2 2 4-4"></path></svg>
                    Kelola Absensi
                </a>
                <a href="{{ route('kelola-laporan') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Kelola Laporan
                </a>
            </nav>
        </div>

        <!-- Bagian Bawah Sidebar -->
        <div class="border-t border-gray-100 pt-4 flex flex-col gap-1 text-sm">
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Bantuan</a>
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg">Pengaturan</a>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-xl font-medium mt-2 transition">Logout</a>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 ml-64 p-8">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-6">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">OPERASIONAL STORE #01 &nbsp;•&nbsp; MANAJEMEN TIM & ROSTER</p>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Jadwal Shift Karyawan</h1>
                <p class="text-xs text-gray-500 mt-0.5">Atur rotasi staf harian & mingguan, alokasi jam kerja, dan kelola penukaran shift.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white border border-gray-200 px-4 py-2.5 rounded-xl text-xs font-semibold shadow-sm text-gray-700 flex items-center gap-2">
                    <button class="text-gray-400 hover:text-gray-700">❮</button>
                    <span>📅 &nbsp; Minggu Ini : 24 - 30 Mar 2025</span>
                    <button class="text-gray-400 hover:text-gray-700">❯</button>
                </div>
                <button class="flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-800 text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                    🔄 Tukar Shift
                </button>
                <button class="flex items-center gap-2 bg-[#241c17] hover:bg-black text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                    + Buat Jadwal Baru
                </button>
            </div>
        </header>

        <!-- 4 KARTU STATISTIK RINGKASAN -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Staf Terjadwal</span>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">14 Staf</h3>
                    <p class="text-[10px] text-emerald-600 mt-0.5 font-medium">Kapasitas Penuh</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl text-gray-500">👥</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Shift Hari Ini</span>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">14 Orang</h3>
                    <p class="text-[10px] text-gray-400 mt-0.5">P (6) • S (5) • M (4)</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl text-amber-600">⏱️</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Tukar & Off</span>
                    <h3 class="text-2xl font-bold text-amber-600 mt-1">2 Menunggu</h3>
                    <p class="text-[10px] text-red-500 mt-0.5 font-medium">Perlu Tindakan</p>
                </div>
                <span class="px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-md">⚠️</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-center">
                <div>
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Jam Kerja</span>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1">480 Jam</h3>
                    <p class="text-[10px] text-emerald-600 mt-0.5 font-medium">Efisiensi 96%</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">📊</div>
            </div>
        </div>

        <!-- GRID UTAMA (Kiri: Tabel Roster Kalender Mingguan | Kanan: Edit Shift & Permintaan) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KOLOM KIRI (2 Kolom): TABEL KALENDER MINGGUAN -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col">
                
                <!-- Tab Menu Tampilan -->
                <div class="flex items-center justify-between mb-5 bg-gray-100 p-1 rounded-xl text-xs font-semibold">
                    <button class="flex-1 py-2 bg-white text-gray-900 rounded-lg shadow-sm">Tampilan Kalender Mingguan (Roster)</button>
                    <button class="flex-1 py-2 text-gray-500 hover:text-gray-900">Tampilan Harian (Timeline Jam)</button>
                    <button class="flex-1 py-2 text-gray-500 hover:text-gray-900">Rekap Jam Kerja Staf</button>
                    <div class="flex items-center gap-2 px-3 text-[10px] text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Pagi</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-500"></span> Siang</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-stone-900"></span> Malam</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-300"></span> OFF</span>
                    </div>
                </div>

                <!-- TABEL ROSTER UTAMA -->
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-[11px] text-gray-400 uppercase tracking-wider">
                                <th class="pb-3 font-semibold w-40">Staf & Peran</th>
                                <th class="pb-3 font-semibold text-center">Senin <br><span class="text-[10px] text-gray-400 font-normal">24 Mar</span></th>
                                <th class="pb-3 font-semibold text-center">Selasa <br><span class="text-[10px] text-gray-400 font-normal">25 Mar</span></th>
                                <th class="pb-3 font-semibold text-center">Rabu <br><span class="text-[10px] text-gray-400 font-normal">26 Mar</span></th>
                                <th class="pb-3 font-semibold text-center bg-amber-50/60 rounded-t-lg">Kamis (Hari Ini) <br><span class="text-[10px] text-amber-700 font-bold">27 Mar</span></th>
                                <th class="pb-3 font-semibold text-center">Jumat <br><span class="text-[10px] text-gray-400 font-normal">28 Mar</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            
                            <!-- Baris 1 -->
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-stone-800 text-white font-bold text-[10px] flex items-center justify-center">AR</div>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-tight">Ahmad Ridwan <span class="text-amber-600">★</span></p>
                                            <p class="text-[10px] text-gray-400 leading-tight">Head Barista • 40 Jam</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">☕ Pagi</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">☕ Pagi</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-gray-100 text-gray-500 font-semibold rounded-md text-[11px]">OFF</span></td>
                                <td class="py-3 text-center bg-amber-50/60"><span class="px-2.5 py-1 bg-amber-500 text-white font-bold rounded-md text-[11px] shadow-sm">Pagi (Bar)</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-orange-50 text-orange-700 font-semibold rounded-md text-[11px]">Siang</span></td>
                            </tr>

                            <!-- Baris 2 -->
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-amber-700 text-white font-bold text-[10px] flex items-center justify-center">SR</div>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-tight">Siti Rahma</p>
                                            <p class="text-[10px] text-gray-400 leading-tight">Kasir & POS • 38 Jam</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">☕ Pagi</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">☕ Pagi</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">☕ Pagi</span></td>
                                <td class="py-3 text-center bg-amber-50/60"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">☕ Pagi</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-orange-50 text-orange-700 font-semibold rounded-md text-[11px]">Siang</span></td>
                            </tr>

                            <!-- Baris 3 -->
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-stone-600 text-white font-bold text-[10px] flex items-center justify-center">DA</div>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-tight">Dimas Anggara</p>
                                            <p class="text-[10px] text-gray-400 leading-tight">Senior Barista • 40 Jam</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-gray-100 text-gray-500 font-semibold rounded-md text-[11px]">OFF</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-orange-50 text-orange-700 font-semibold rounded-md text-[11px]">Siang</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-orange-50 text-orange-700 font-semibold rounded-md text-[11px]">Siang</span></td>
                                <td class="py-3 text-center bg-amber-50/60"><span class="px-2.5 py-1 bg-orange-50 text-orange-700 font-semibold rounded-md text-[11px]">Siang</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-stone-900 text-white font-semibold rounded-md text-[11px]">Malam</span></td>
                            </tr>

                            <!-- Baris 4 -->
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-amber-800 text-white font-bold text-[10px] flex items-center justify-center">MP</div>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-tight">Maya Putri <span class="bg-amber-100 text-amber-800 px-1 py-0.2 rounded text-[9px]">Tukar?</span></p>
                                            <p class="text-[10px] text-gray-400 leading-tight">Kitchen & Pastry • 35 Jam</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">☕ Pagi</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-gray-100 text-gray-500 font-semibold rounded-md text-[11px]">OFF</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">☕ Pagi</span></td>
                                <td class="py-3 text-center bg-amber-50/60"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">☕ Pagi</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-stone-900 text-white font-semibold rounded-md text-[11px]">Malam</span></td>
                            </tr>

                            <!-- Baris 5 -->
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-zinc-800 text-white font-bold text-[10px] flex items-center justify-center">BP</div>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-tight">Bagus Prasetyo</p>
                                            <p class="text-[10px] text-gray-400 leading-tight">Junior Barista • 40 Jam</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-stone-900 text-white font-semibold rounded-md text-[11px]">Malam</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-stone-900 text-white font-semibold rounded-md text-[11px]">Malam</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-stone-900 text-white font-semibold rounded-md text-[11px]">Malam</span></td>
                                <td class="py-3 text-center bg-amber-50/60"><span class="px-2.5 py-1 bg-stone-900 text-white font-semibold rounded-md text-[11px]">Malam</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-gray-100 text-gray-500 font-semibold rounded-md text-[11px]">OFF</span></td>
                            </tr>

                            <!-- Baris 6 -->
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-orange-700 text-white font-bold text-[10px] flex items-center justify-center">NK</div>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-tight">Nina Kurnia</p>
                                            <p class="text-[10px] text-gray-400 leading-tight">Cashier & Floor • 36 Jam</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-orange-50 text-orange-700 font-semibold rounded-md text-[11px]">Siang</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-orange-50 text-orange-700 font-semibold rounded-md text-[11px]">Siang</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-gray-100 text-gray-500 font-semibold rounded-md text-[11px]">OFF</span></td>
                                <td class="py-3 text-center bg-amber-50/60"><span class="px-2.5 py-1 bg-orange-50 text-orange-700 font-semibold rounded-md text-[11px]">Siang</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold rounded-md text-[11px]">Pagi</span></td>
                            </tr>

                            <!-- Baris 7 -->
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-stone-700 text-white font-bold text-[10px] flex items-center justify-center">KS</div>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-tight">Kevin Senjaya</p>
                                            <p class="text-[10px] text-gray-400 leading-tight">Bar Support • 32 Jam</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-stone-900 text-white font-semibold rounded-md text-[11px]">Malam</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-gray-100 text-gray-500 font-semibold rounded-md text-[11px]">OFF</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-gray-100 text-gray-500 font-semibold rounded-md text-[11px]">OFF</span></td>
                                <td class="py-3 text-center bg-amber-50/60"><span class="px-2.5 py-1 bg-stone-900 text-white font-semibold rounded-md text-[11px]">Malam</span></td>
                                <td class="py-3 text-center"><span class="px-2.5 py-1 bg-stone-900 text-white font-semibold rounded-md text-[11px]">Malam</span></td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <!-- Footer Tabel -->
                <div class="flex justify-between items-center pt-4 mt-4 border-t border-gray-100 text-xs text-gray-500">
                    <span>💡 Klik pada kotak shift mana saja untuk membuka editor shift di panel kanan.</span>
                    <div class="flex items-center gap-2">
                        <span>Tampilkan 7 dari 14 Staf</span>
                        <button class="px-3 py-1 bg-gray-100 hover:bg-gray-200 font-semibold rounded-lg text-gray-800">Muat Seluruh Staf ↓</button>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (1 Kolom): EDIT ALOKASI SHIFT & PERMINTAAN TUKAR -->
            <div class="flex flex-col gap-6">
                
                <!-- CARD: EDIT / ALOKASI SHIFT -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 text-xs space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Edit / Alokasi Shift</h3>
                            <p class="text-[10px] text-gray-400">Modifikasi jadwal & stasiun staf terpilih.</p>
                        </div>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold text-[10px] rounded">Aktif</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pilih Staf</label>
                        <select class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-gray-800 font-medium focus:outline-none">
                            <option>Ahmad Ridwan (Head Barista)</option>
                            <option>Siti Rahma (Kasir & POS)</option>
                            <option>Dimas Anggara (Senior Barista)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Tanggal Berlaku</label>
                        <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-gray-800 font-medium">
                            <span>📅 Kamis, 27 Maret 2025</span>
                            <span class="text-[10px] text-gray-400">(Hari Ini)</span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pilihan Jam Shift</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2.5 p-2.5 bg-amber-50 border-2 border-amber-500 rounded-xl cursor-pointer">
                                <input type="radio" name="shift" checked class="text-amber-600">
                                <div>
                                    <strong class="block text-gray-900 text-[11px]">Shift Pagi</strong>
                                    <span class="text-[10px] text-gray-500">07:00 - 15:00</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-2.5 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
                                <input type="radio" name="shift" class="text-amber-600">
                                <div>
                                    <strong class="block text-gray-900 text-[11px]">Middle</strong>
                                    <span class="text-[10px] text-gray-500">11:00 - 19:00</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-2.5 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
                                <input type="radio" name="shift" class="text-amber-600">
                                <div>
                                    <strong class="block text-gray-900 text-[11px]">Shift Malam</strong>
                                    <span class="text-[10px] text-gray-500">15:00 - 23:00</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-2.5 bg-gray-50 border border-gray-200 rounded-xl cursor-pointer">
                                <input type="radio" name="shift" class="text-amber-600">
                                <div>
                                    <strong class="block text-gray-900 text-[11px]">Libur (Day Off)</strong>
                                    <span class="text-[10px] text-gray-500">0 Jam Alokasi</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Posisi / Stasiun Kerja</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" class="px-3 py-1.5 bg-[#241c17] text-white font-semibold rounded-lg text-[11px]">✓ Bar Utama / Espresso Bar</button>
                            <button type="button" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[11px]">Kasir Depan</button>
                            <button type="button" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[11px]">Kitchen/Pastry</button>
                            <button type="button" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[11px]">Drive-thru</button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Catatan Khusus Shift</label>
                        <input type="text" value="Standby saat peak hour 12:00 - 14:00" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-gray-800 focus:outline-none">
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="button" class="flex-1 py-2.5 bg-[#241c17] hover:bg-black text-white font-semibold rounded-xl shadow-sm transition">
                            Simpan Perubahan Shift
                        </button>
                        <button type="button" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition">
                            Batal
                        </button>
                    </div>
                </div>

                <!-- CARD: PERMINTAAN TUKAR & CUTI -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 text-xs space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="font-bold text-gray-900 text-sm">Permintaan Tukar & Cuti</h3>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold text-[10px] rounded-full">2 Menunggu</span>
                    </div>

                    <!-- Item 1 -->
                    <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <strong class="text-gray-900 block text-xs">Maya Putri | Siti Rahma</strong>
                                <span class="text-[10px] text-gray-400">Jumat, 28 Mar • Tukar Shift Malam ke Siang</span>
                            </div>
                            <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 font-bold text-[9px] rounded">Tukar Shift</span>
                        </div>
                        <p class="text-[11px] text-gray-600 bg-white p-2 rounded-lg border border-gray-200 italic">
                            "Ada perlunya acara pengganti jam 14.30. Siti sudah bersedia gantikan malam."
                        </p>
                        <div class="flex gap-2">
                            <button class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition">Setuju</button>
                            <button class="flex-1 py-1.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold rounded-lg transition">Tolak</button>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <strong class="text-gray-900 block text-xs">Bagus Prasetyo</strong>
                                <span class="text-[10px] text-gray-400">Sabtu, 29 Mar • Ubah Pagi ke Siang</span>
                            </div>
                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 font-bold text-[9px] rounded">Ubah Jam</span>
                        </div>
                        <p class="text-[11px] text-gray-600 bg-white p-2 rounded-lg border border-gray-200 italic">
                            "Jadwal perkuliahan diundur jam 09.30 pagi, siap lembur di shift siang."
                        </p>
                        <div class="flex gap-2">
                            <button class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition">Setuju</button>
                            <button class="flex-1 py-1.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold rounded-lg transition">Tolak</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

</body>
</html>