<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tim & Jadwal Shift - Cafe Exito</title>
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

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col p-6 min-h-screen justify-between">
        <div>
            <!-- Logo & Nama Cafe -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-xl bg-[#6b5344] flex items-center justify-center text-white font-bold shadow-sm">
                    ☕
                </div>
                <div>
                    <h1 class="font-bold text-gray-900 tracking-tight">Cafe Exito</h1>
                    <p class="text-[11px] text-gray-400">Owner Dashboard</p>
                </div>
            </div>

            <!-- Profil User -->
            <div class="flex items-center gap-3 bg-[#fcf8f5] p-3 rounded-xl mb-6 border border-amber-900/10">
                <div class="w-9 h-9 rounded-full bg-[#6b5344] text-white flex items-center justify-center font-semibold text-sm">
                    RB
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-900">Rian Baskoro</p>
                    <p class="text-[10px] text-amber-800 font-medium">General Manager</p>
                </div>
            </div>

            <!-- Menu Navigasi -->
            <nav class="flex flex-col gap-1 text-xs font-medium">
                <a href="/owner-dashboard" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">📊 Dashboard</a>
                <a href="/data-karyawan" class="flex items-center gap-3 py-2.5 px-3 rounded-lg bg-[#6b5344] text-white shadow-sm">👥 Data Karyawan & Shift</a>
                <a href="/laporan-penjualan" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">📈 Laporan Penjualan</a>
                <a href="#" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-gray-600 hover:bg-gray-50 transition justify-between">
                    <span>📦 Stok Bahan</span>
                    <span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">3</span>
                </a>
                <a href="#" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">👤 Profil Akun</a>
            </nav>
        </div>

        <!-- Tombol Aksi Bawah Sidebar -->
        <div class="space-y-3 pt-6 border-t border-gray-100">
            <button class="w-full py-2.5 px-4 bg-[#8b6548] hover:bg-[#74533a] text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                🛒 Buka Kasir POS
            </button>
            <button class="w-full py-2 px-4 text-red-600 hover:bg-red-50 text-xs font-semibold rounded-xl transition flex items-center justify-center gap-2">
                🚪 Logout
            </button>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-8 flex flex-col gap-6">
        
        <!-- HEADER ATAS (FILTER & AKSI) -->
        <header class="flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-amber-50/60 px-3 py-1.5 rounded-xl border border-amber-200/60 text-xs font-semibold text-amber-900">
                    <span>🔥 Shift: Pagi & Sore</span>
                </div>
                <div class="flex items-center gap-2 bg-emerald-50/60 px-3 py-1.5 rounded-xl border border-emerald-200/60 text-xs font-semibold text-emerald-900">
                    <span>📍 Outlet: Utama (Buka)</span>
                </div>
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-medium text-gray-700">
                    <span>📅 Pilih Tanggal: Hari ini, 24 Okt</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                    🖨️ Cetak & Ekspor
                </button>
                <button class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs hover:bg-gray-100 transition">🔔</button>
            </div>
        </header>

        <!-- BANNER JUDUL & TOMBOL AKSI UTAMA -->
        <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Manajemen Tim & Jadwal Shift</h1>
                <p class="text-xs text-gray-400 mt-1">Monitoring absensi real-time, rotasi barista, dan integritas jam kerja cabang utama.</p>
            </div>

            <div class="flex items-center gap-3">
                <button class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-xl transition flex items-center gap-2">
                    🔄 Lihat Rekap Absensi Bulanan
                </button>
                <button class="px-4 py-2.5 bg-[#2a211c] hover:bg-[#3d312a] text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                    ➕ Tambah Jadwal / Shift
                </button>
            </div>
        </div>

        <!-- 4 KARTU STATISTIK RINGKASAN -->
        <div class="grid grid-cols-4 gap-5">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs text-gray-400 font-medium">TOTAL STAF AKTIF</span>
                        <span class="text-gray-400">👥</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">14 <span class="text-xs font-normal text-gray-500">Orang</span></h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-50">Barista (8) • Kitchen (4) • Kasir (2)</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs text-gray-400 font-medium">BERTUGAS HARI INI</span>
                        <span class="text-[10px] bg-amber-50 text-amber-800 font-bold px-2 py-0.5 rounded-full">2 Shift</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">8 <span class="text-xs font-normal text-gray-500">Orang</span></h2>
                </div>
                <div class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-50 flex justify-between">
                    <span>Shift Pagi: 4</span>
                    <span>Shift Sore: 4</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs text-gray-400 font-medium">TINGKAT KEHADIRAN</span>
                        <span class="text-[10px] bg-emerald-50 text-emerald-600 font-bold px-2 py-0.5 rounded-full">+1.4%</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">98% <span class="text-xs font-semibold text-emerald-600">On-Time</span></h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-50">Rata-rata check-in: 12 mnt lebih awal</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs text-gray-400 font-medium">CUTI & LIBUR</span>
                        <span class="text-gray-400">📅</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">2 <span class="text-xs font-normal text-gray-500">Orang</span></h2>
                </div>
                <p class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-50">1 Cuti Tahunan • 1 Libur Reguler</p>
            </div>
        </div>

        <!-- BARIS AKSI CEPAT & STATUS MESIN -->
        <div class="bg-white px-5 py-3 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center text-xs">
            <div class="flex items-center gap-3">
                <span class="font-semibold text-gray-700">AKSI CEPAT TIM:</span>
                <button class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl font-medium transition">🔀 Tukar Shift Mandiri</button>
                <button class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl font-medium transition">💬 Broadcast Info Tim via WhatsApp</button>
            </div>
            <div class="flex items-center gap-2 text-gray-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span>Mesin Espresso #1 & #2 Siap</span> • <span>Waktu Sistem: <strong>09:42 WIB</strong></span>
            </div>
        </div>

        <!-- SECTION DUA KOLOM: SHIFT PAGI & SHIFT SORE/MALAM -->
        <div class="grid grid-cols-2 gap-6">
            
            <!-- Kolom 1: Shift Pagi -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-amber-900/10 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-amber-100 text-amber-800 rounded-xl text-sm">🌅</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-gray-900 text-sm">Shift Pagi</h3>
                                    <span class="text-[10px] bg-emerald-50 text-emerald-700 font-semibold px-2 py-0.5 rounded-full">Aktif Berjalan</span>
                                </div>
                                <p class="text-[11px] text-gray-400">Jam Operasional: 07:30 - 15:30 • Kuota: 4 / 4 Hadir</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Staf Pagi 1 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-semibold text-gray-900">Rian Ardiansyah</p>
                                        <span class="text-[9px] bg-gray-900 text-white px-1.5 py-0.5 rounded font-bold">Head Barista</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400">⏱️ Check-in: 07:46</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1 rounded-full">🟢 Hadir Tepat Waktu</span>
                                <button class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100">💬</button>
                                <button onclick="openModal('Rian Ardiansyah', 'Head Barista', '🟢 Hadir (07:46 WIB)', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80', 'ID #STF-014')" class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer" title="Edit Jadwal">⚙️</button>
                            </div>
                        </div>

                        <!-- Staf Pagi 2 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=100&q=80" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-semibold text-gray-900">Siti Rahma</p>
                                        <span class="text-[9px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-bold">Cashier</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400">⏱️ Check-in: 07:22</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1 rounded-full">🟢 Hadir 07:22</span>
                                <button class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100">💬</button>
                                <button onclick="openModal('Siti Rahma', 'Cashier', '🟢 Hadir (07:22 WIB)', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=100&q=80', 'ID #STF-015')" class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer" title="Edit Jadwal">⚙️</button>
                            </div>
                        </div>

                        <!-- Staf Pagi 3 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=100&q=80" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-semibold text-gray-900">Fajar Yulianto</p>
                                        <span class="text-[9px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-bold">Junior Barista</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400">⏱️ Check-in: 07:55</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-amber-100 text-amber-800 font-semibold px-2.5 py-1 rounded-full">🟡 Sedang Istirahat (15m)</span>
                                <button class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100">💬</button>
                                <button onclick="openModal('Fajar Yulianto', 'Junior Barista', '🟡 Sedang Istirahat (15m)', 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=100&q=80', 'ID #STF-016')" class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer" title="Edit Jadwal">⚙️</button>
                            </div>
                        </div>

                        <!-- Staf Pagi 4 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-semibold text-gray-900">Ahmad Fauzi</p>
                                        <span class="text-[9px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-bold">Kitchen Staff</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400">⏱️ Check-in: 07:30</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1 rounded-full">🟢 Hadir Tepat Waktu</span>
                                <button class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100">💬</button>
                                <button onclick="openModal('Ahmad Fauzi', 'Kitchen Staff', '🟢 Hadir Tepat Waktu', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80', 'ID #STF-017')" class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer" title="Edit Jadwal">⚙️</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-3 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                    <span>Estimasi Handover Shift: <strong>15:00 - 15:30</strong></span>
                    <a href="#" class="font-semibold text-amber-800 hover:underline">Catatan Shift Pagi ></a>
                </div>
            </div>

            <!-- Kolom 2: Shift Sore / Malam -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-gray-900 text-white rounded-xl text-sm">🌙</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-gray-900 text-sm">Shift Sore / Malam</h3>
                                    <span class="text-[10px] bg-gray-100 text-gray-600 font-medium px-2 py-0.5 rounded-full">Mulai 5j 18m Lagi</span>
                                </div>
                                <p class="text-[11px] text-gray-400">Jam Operasional: 15:00 - 23:00 • Kuota: 4 Staf Terjadwal</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Staf Sore 1 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-semibold text-gray-900">Dimas Anggara</p>
                                        <span class="text-[9px] bg-gray-900 text-white px-1.5 py-0.5 rounded font-bold">Senior Barista</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400">🕒 Jadwal Check-in</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-gray-200 text-gray-700 font-medium px-2.5 py-1 rounded-full">⚪ Belum Check-in</span>
                                <button class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100">💬</button>
                                <button onclick="openModal('Dimas Anggara', 'Senior Barista', '⚪ Belum Check-in', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80', 'ID #STF-020')" class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer" title="Edit Jadwal">⚙️</button>
                            </div>
                        </div>

                        <!-- Staf Sore 2 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-semibold text-gray-900">Nadira Maharani</p>
                                        <span class="text-[9px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-bold">Junior Barista</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400">🕒 Jadwal Check-in</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-gray-200 text-gray-700 font-medium px-2.5 py-1 rounded-full">⚪ Belum Check-in</span>
                                <button class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100">💬</button>
                                <button onclick="openModal('Nadira Maharani', 'Junior Barista', '⚪ Belum Check-in', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80', 'ID #STF-021')" class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer" title="Edit Jadwal">⚙️</button>
                            </div>
                        </div>

                        <!-- Staf Sore 3 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=100&q=80" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-semibold text-gray-900">Kevin Sanjaya</p>
                                        <span class="text-[9px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-bold">Cashier</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400">🕒 Jadwal Check-in</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-gray-200 text-gray-700 font-medium px-2.5 py-1 rounded-full">⚪ Belum Check-in</span>
                                <button class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100">💬</button>
                                <button onclick="openModal('Kevin Sanjaya', 'Cashier', '⚪ Belum Check-in', 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=100&q=80', 'ID #STF-022')" class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer" title="Edit Jadwal">⚙️</button>
                            </div>
                        </div>

                        <!-- Staf Sore 4 -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-300 overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=100&q=80" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-semibold text-gray-900">Dewi Anggraini</p>
                                        <span class="text-[9px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-bold">Kitchen Staff</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400">🕒 Jadwal Check-in</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] bg-gray-200 text-gray-700 font-medium px-2.5 py-1 rounded-full">⚪ Belum Check-in</span>
                                <button class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100">💬</button>
                                <button onclick="openModal('Dewi Anggraini', 'Kitchen Staff', '⚪ Belum Check-in', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=100&q=80', 'ID #STF-023')" class="p-1.5 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer" title="Edit Jadwal">⚙️</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-3 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                    <span>Closing Procedure & Settlement: <strong>22:30 - 23:00</strong></span>
                    <a href="#" class="font-semibold text-amber-800 hover:underline">Checklist Penutupan ></a>
                </div>
            </div>

        </div>

        <!-- SECTION BAWAH: TIMELINE ALUR PENUGASAN & STAF IZIN & LIBUR -->
        <div class="grid grid-cols-3 gap-6">
            
            <!-- Timeline Alur Penugasan Staf -->
            <div class="col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Timeline Alur Penugasan Staf (Hari Ini)</h3>
                            <p class="text-xs text-gray-400">Distribusi kepadatan barista & kasir per jam sibuk (07:00 - 23:00)</p>
                        </div>
                        <span class="text-[10px] bg-amber-50 text-amber-800 font-bold px-2.5 py-1 rounded-full">Rush Hour: 08:30 & 19:00</span>
                    </div>

                    <div class="grid grid-cols-8 text-center text-[10px] text-gray-400 font-medium mb-3 pt-2 border-t border-gray-100">
                        <span>07:00</span>
                        <span>09:00</span>
                        <span>11:00</span>
                        <span>13:00</span>
                        <span>15:00</span>
                        <span>17:00</span>
                        <span>19:00</span>
                        <span>21:00</span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-24 text-gray-500 font-medium">Barista Bar</span>
                            <div class="flex-1 bg-gray-100 h-7 rounded-xl relative overflow-hidden flex items-center">
                                <div class="absolute left-0 w-3/5 bg-amber-200/80 h-full rounded-xl flex items-center px-3 text-[11px] font-semibold text-amber-900">
                                    Shift Pagi (Arka, Reza)
                                </div>
                                <div class="absolute right-0 w-2/5 bg-gray-900 text-white h-full rounded-xl flex items-center px-3 text-[11px] font-semibold">
                                    Shift Malam (Dimas, Nadira)
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-24 text-gray-500 font-medium">Kasir POS</span>
                            <div class="flex-1 bg-gray-100 h-7 rounded-xl relative overflow-hidden flex items-center">
                                <div class="absolute left-0 w-3/5 bg-amber-200/80 h-full rounded-xl flex items-center px-3 text-[11px] font-semibold text-amber-900">
                                    Siti Rahmawati
                                </div>
                                <div class="absolute right-0 w-2/5 bg-gray-900 text-white h-full rounded-xl flex items-center px-3 text-[11px] font-semibold">
                                    Kevin Wijaya
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-24 text-gray-500 font-medium">Kitchen & Food</span>
                            <div class="flex-1 bg-gray-100 h-7 rounded-xl relative overflow-hidden flex items-center">
                                <div class="absolute left-0 w-3/5 bg-amber-200/80 h-full rounded-xl flex items-center px-3 text-[11px] font-semibold text-amber-900">
                                    Budi Santoso
                                </div>
                                <div class="absolute right-0 w-2/5 bg-gray-900 text-white h-full rounded-xl flex items-center px-3 text-[11px] font-semibold">
                                    Dewi Anggraini
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center text-xs text-gray-400 mt-6 pt-3 border-t border-gray-100">
                    <div class="flex gap-4">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-300 inline-block"></span> Shift Pagi Aktif</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-900 inline-block"></span> Shift Sore Menunggu</span>
                    </div>
                    <span>Jadwal sinkron dengan Google Workspace Calendar</span>
                </div>
            </div>

            <!-- Staf Izin & Libur -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900 text-sm">Staf Izin & Libur</h3>
                        <span class="text-[10px] bg-gray-100 text-gray-600 font-bold px-2 py-0.5 rounded-full">2 Karyawan</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-4">Pengajuan terverifikasi oleh Owner.</p>

                    <div class="space-y-3">
                        <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-800 text-white text-xs flex items-center justify-center font-bold">FA</div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900">Farhan Aditya</p>
                                    <p class="text-[10px] text-gray-400">Junior Barista • Cuti Tahunan (Hari ke-2)</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-amber-100 text-amber-800 font-semibold px-2.5 py-1 rounded-full">Cuti</span>
                        </div>

                        <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-800 text-white text-xs flex items-center justify-center font-bold">ML</div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900">Melisa Lestari</p>
                                    <p class="text-[10px] text-gray-400">Cashier • Off Reguler Mingguan</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-gray-200 text-gray-700 font-semibold px-2.5 py-1 rounded-full">Libur</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    <button class="w-full py-2.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-800 rounded-xl transition flex items-center justify-center gap-2">
                        📋 Kelola Permohonan Cuti Tim
                    </button>
                </div>
            </div>

        </div>

    </main>

    <!-- ========================================== -->
    <!-- MODAL EDIT JADWAL & SHIFT (DINAMIS)       -->
    <!-- ========================================== -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden border border-gray-100 flex flex-col max-h-[90vh]">
            
            <!-- Header Modal -->
            <div class="flex justify-between items-start p-6 pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">Edit Jadwal & Shift Staf</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Sesuaikan alokasi station, jam kerja, atau tukar shift karyawan.</p>
                </div>
                <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center font-bold text-sm transition cursor-pointer">✕</button>
            </div>

            <!-- Isi Konten Modal -->
            <div class="p-6 overflow-y-auto space-y-5">
                
                <!-- Info Karyawan Terpilih (Dinamik) -->
                <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-2xl border border-gray-200/60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200">
                            <img id="modal-img" src="" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p id="modal-name" class="text-xs font-bold text-gray-900">-</p>
                                <span id="modal-role" class="text-[9px] bg-gray-900 text-white px-1.5 py-0.5 rounded font-bold">-</span>
                            </div>
                            <p id="modal-id" class="text-[10px] text-gray-400 mt-0.5">Cabang Utama Dago • ID #STF-000</p>
                        </div>
                    </div>
                    <span id="modal-status" class="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1 rounded-full">-</span>
                </div>

                <!-- Pilih Shift Kerja -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Pilih Shift Kerja (24 Okt 2024)</label>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3.5 rounded-2xl border-2 border-gray-900 bg-amber-50/40 cursor-pointer flex flex-col justify-between">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-base">🌅</span>
                                <span class="w-3 h-3 rounded-full bg-gray-900 flex items-center justify-center text-white text-[8px]">✓</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-900">Shift Pagi</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">07:30 - 15:30</p>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-2xl border border-gray-200 hover:border-gray-400 bg-white cursor-pointer flex flex-col justify-between transition">
                            <div class="flex justify-between items-center mb-2"><span class="text-base">🌙</span></div>
                            <div>
                                <p class="text-xs font-semibold text-gray-700">Shift Sore</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">15:00 - 23:00</p>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-2xl border border-gray-200 hover:border-gray-400 bg-white cursor-pointer flex flex-col justify-between transition">
                            <div class="flex justify-between items-center mb-2"><span class="text-base">🏖️</span></div>
                            <div>
                                <p class="text-xs font-semibold text-gray-700">Libur / Off</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Hari Bebas Tugas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Penugasan Stasiun Kerja -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Penugasan Stasiun Kerja (Station Role)</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border-2 border-amber-800 bg-amber-50/30 cursor-pointer text-xs font-semibold text-gray-900">
                            <input type="radio" name="station" checked class="accent-amber-800">
                            ☕ Espresso Bar (Utama)
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer text-xs font-medium text-gray-700">
                            <input type="radio" name="station" class="accent-amber-800">
                            🫖 Manual Brew & Filter
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer text-xs font-medium text-gray-700">
                            <input type="radio" name="station" class="accent-amber-800">
                            💳 Kasir POS
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer text-xs font-medium text-gray-700">
                            <input type="radio" name="station" class="accent-amber-800">
                            🥐 Food & Pastry Prep
                        </label>
                    </div>
                </div>

            </div>

            <!-- Footer Modal -->
            <div class="p-6 pt-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
                <button class="px-4 py-2.5 bg-white hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-amber-900 rounded-xl transition shadow-sm">
                    🔄 Tukar dengan Karyawan Lain...
                </button>
                <div class="flex items-center gap-2">
                    <button onclick="closeModal()" class="px-4 py-2.5 bg-white hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button onclick="closeModal()" class="px-5 py-2.5 bg-[#2a211c] hover:bg-[#3d312a] text-white text-xs font-semibold rounded-xl shadow-sm transition cursor-pointer">
                        ✓ Simpan Perubahan Jadwal
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================== -->
    <!-- SCRIPT JAVASCRIPT DINAMIS                 -->
    <!-- ========================================== -->
    <script>
        const editModal = document.getElementById('editModal');

        // Fungsi membuka modal dan mengisi data berdasarkan staf yang ditekan
        function openModal(name, role, status, imgUrl, staffId) {
            document.getElementById('modal-name').innerText = name;
            document.getElementById('modal-role').innerText = role;
            document.getElementById('modal-status').innerText = status;
            document.getElementById('modal-img').src = imgUrl;
            document.getElementById('modal-id').innerText = "Cabang Utama Dago • " + staffId;
            
            editModal.classList.remove('hidden');
        }

        // Fungsi menutup modal
        function closeModal() {
            editModal.classList.add('hidden');
        }
    </script>
</body>
</html>