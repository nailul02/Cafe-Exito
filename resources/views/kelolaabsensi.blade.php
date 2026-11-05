<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Absensi & Presensi Staf - Cafe Exito</title>
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
                <a href="{{ route('kelola-absensi') }}" class="py-2.5 px-4 rounded-xl bg-[#f97316] text-white shadow-sm flex items-center gap-3 cursor-pointer font-semibold">
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
            <a href="#" onclick="alert('Membuka panel pusat bantuan absensi...')" class="flex items-center gap-2 text-gray-200 hover:text-white px-2 py-1">
                <span>❓</span> Bantuan
            </a>
            <a href="#" onclick="alert('Membuka pengaturan absensi...')" class="flex items-center gap-2 text-gray-200 hover:text-white px-2 py-1">
                <span>⚙️</span> Pengaturan
            </a>
            <a href="{{ route('proses-login') }}" class="w-full py-2 px-3 bg-red-600/20 hover:bg-red-600/40 text-red-200 rounded-xl transition flex items-center justify-center gap-2 font-bold cursor-pointer text-center">
                🚪 Logout
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA KELOLA ABSENSI -->
    <main class="flex-1 p-6 overflow-y-auto">
        
        <!-- HEADER ATAS -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 bg-white p-3.5 rounded-2xl shadow-sm border border-gray-100 gap-3">
            <!-- Kolom Pencarian -->
            <div class="relative w-full sm:w-80">
                <input type="text" id="inputCariAbsensi" onkeyup="cariDataAbsensi()" placeholder="Cari nama staf, role, status..." class="w-full pl-9 pr-8 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                <span class="absolute left-3 top-2 text-gray-400 text-xs">🔍</span>
                <span class="absolute right-3 top-2 text-gray-400 text-xs cursor-pointer" onclick="document.getElementById('inputCariAbsensi').value=''; cariDataAbsensi();">✕</span>
            </div>

            <!-- Tombol Aksi Header Kanan -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <div class="flex items-center gap-2 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200 text-xs font-semibold text-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Shift Pagi (07:00 - 16:00 WIB) • LIVE</span>
                </div>
                <button onclick="bukaModalKoreksiManual()" class="px-3.5 py-1.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                    ⏱️ Koreksi / Clock-In Manual
                </button>
                <button onclick="eksporRekapAbsensi()" class="px-3.5 py-1.5 bg-[#261c14] hover:bg-black text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                    📥 Ekspor Rekap Absensi
                </button>
            </div>
        </header>

        <!-- SUB-HEADER / JUDUL HALAMAN -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-5 gap-2">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">OPERASIONAL STORE #01</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-[10px] font-bold text-[#f97316]">MANAJEMEN TIM & KEHADIRAN</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Kelola Absensi & Presensi Staf</h2>
                <p class="text-xs text-gray-400">Pantau kehadiran real-time staf shift, keterlambatan, selfie check-in, dan validasi riwayat absensi harian.</p>
            </div>
            
            <!-- Filter Tanggal -->
            <div class="flex items-center gap-2 bg-white px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 shadow-sm">
                <span>📅 Hari Ini: Kamis, 27 Maret 2025</span>
                <button onclick="alert('📅 Membuka kalender pemilih tanggal absensi masa lalu...')" class="text-amber-800 font-bold ml-1">Ubah</button>
            </div>
        </div>

        <!-- 4 KOTAK RINGKASAN STATISTIK ABSENSI -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kehadiran Hari Ini</span>
                    <h2 class="text-xl font-bold text-gray-900 mt-1">12 / 14 <span class="text-xs font-normal text-gray-500">Staf</span></h2>
                    <p class="text-[10px] text-emerald-600 font-semibold mt-1">86% Hadir (2 Cuti/Belum)</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center font-bold">👥</div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Tepat Waktu</span>
                    <h2 class="text-xl font-bold text-gray-900 mt-1">10 <span class="text-xs font-normal text-gray-500">Staf</span></h2>
                    <p class="text-[10px] text-emerald-600 font-semibold mt-1">Rata-rata 12m sebelum shift <span class="text-gray-400 font-normal">+98%</span></p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">✔</div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-wider">Terlambat / Izin</span>
                    <h2 class="text-xl font-bold text-red-600 mt-1">2 <span class="text-xs font-normal text-gray-500">Staf</span></h2>
                    <p class="text-[10px] text-red-500 font-semibold mt-1">1 Terlambat + 1 Izin <span class="text-amber-700 font-bold cursor-pointer" onclick="alert('Membuka rincian alasan izin dan keterlambatan staf.')">Butuh Tinjau</span></p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold">⚠</div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jam Kerja Terakumulasi</span>
                    <h2 class="text-xl font-bold text-gray-900 mt-1">92.5 <span class="text-xs font-normal text-gray-500">Jam</span></h2>
                    <p class="text-[10px] text-gray-500 mt-1">Shift Pagi & Tengah <span class="text-emerald-600 font-bold">96% Efisiensi</span></p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">⏱️</div>
            </div>
        </div>

        <!-- GRID UTAMA (KIRI: TABEL KEHADIRAN | KANAN: VERIFIKASI & KOREKSI MANUAL) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KOLOM KIRI (2 Kolom): TABEL KEHADIRAN & RIWAYAT -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- TAB NAVIGASI TABEL -->
                <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
                    <div class="flex gap-2 text-xs font-semibold">
                        <button onclick="gantiTabAbsensi('hari-ini', this)" id="tabHariIni" class="px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition">Kehadiran Hari Ini (Live)</button>
                        <button onclick="gantiTabAbsensi('mingguan', this)" id="tabMingguan" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition">Riwayat Absensi Mingguan</button>
                        <button onclick="gantiTabAbsensi('izin', this)" id="tabIzin" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition">Pengajuan Lembur & Izin</button>
                    </div>
                    <div class="flex gap-1">
                        <button onclick="alert('Menampilkan mode grid compact')" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs">🗂️</button>
                        <button onclick="alert('Menampilkan mode daftar detail')" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs">📋</button>
                    </div>
                </div>

                <!-- FILTER CEPAT STATUS -->
                <div class="bg-white p-3.5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-2 text-xs overflow-x-auto">
                    <span class="text-gray-400 font-bold">Filter Staf:</span>
                    <button onclick="filterStatusStaf('semua')" class="px-3 py-1 bg-gray-900 text-white font-semibold rounded-xl">Semua Staf (14)</button>
                    <button onclick="filterStatusStaf('hadir')" class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-xl">Hadir (12)</button>
                    <button onclick="filterStatusStaf('terlambat')" class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-xl">Terlambat (1)</button>
                    <button onclick="filterStatusStaf('izin')" class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold rounded-xl">Izin / Sakit (1)</button>
                </div>

                <!-- TABEL DATA STAF -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
                    <table class="w-full text-left text-xs" id="tabelAbsensiStaf">
                        <thead>
                            <tr class="bg-gray-50 text-gray-400 font-bold uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3 px-3">Staf & Peran</th>
                                <th class="py-3 px-3">Jadwal Shift</th>
                                <th class="py-3 px-3">Clock-In</th>
                                <th class="py-3 px-3">Validasi Presensi</th>
                                <th class="py-3 px-3">Stasiun</th>
                                <th class="py-3 px-3 text-center">Aksi Tinjau</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                            
                            <!-- Baris 1: Hadir Tepat Waktu -->
                            <tr class="hover:bg-gray-50/80 transition item-absen" data-status="hadir">
                                <td class="py-3.5 px-3 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gray-800 text-white font-bold flex items-center justify-center text-[10px]">AR</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Ahmad Ridwan</p>
                                        <p class="text-[10px] text-gray-400">Head Barista</p>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">Shift Pagi<br><span class="text-[10px] text-gray-400">07:00 - 15:00</span></td>
                                <td class="py-3.5 px-3">
                                    <span class="text-emerald-700 font-bold">🟢 06:48 WIB</span>
                                    <p class="text-[10px] text-emerald-600 font-semibold">Tepat Waktu</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[10px] font-bold">✔ Bar Area</span>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[10px] font-bold ml-1">✔ Foto</span>
                                </td>
                                <td class="py-3.5 px-3">Bar Utama</td>
                                <td class="py-3.5 px-3 text-center">
                                    <button onclick="detailAbsenStaf('Ahmad Ridwan')" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[10px] font-bold">Detail</button>
                                </td>
                            </tr>

                            <!-- Baris 2: Hadir Tepat Waktu -->
                            <tr class="hover:bg-gray-50/80 transition item-absen" data-status="hadir">
                                <td class="py-3.5 px-3 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-900 font-bold flex items-center justify-center text-[10px]">SR</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Siti Rahma</p>
                                        <p class="text-[10px] text-gray-400">Kasir & POS</p>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">Shift Pagi<br><span class="text-[10px] text-gray-400">07:00 - 15:00</span></td>
                                <td class="py-3.5 px-3">
                                    <span class="text-emerald-700 font-bold">🟢 06:52 WIB</span>
                                    <p class="text-[10px] text-emerald-600 font-semibold">Tepat Waktu</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[10px] font-bold">✔ Kasir Valid</span>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[10px] font-bold ml-1">✔ GPS</span>
                                </td>
                                <td class="py-3.5 px-3">Kasir Depan</td>
                                <td class="py-3.5 px-3 text-center">
                                    <button onclick="detailAbsenStaf('Siti Rahma')" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[10px] font-bold">Detail</button>
                                </td>
                            </tr>

                            <!-- Baris 3: Terlambat (Perlu Review) -->
                            <tr class="hover:bg-red-50/30 transition item-absen bg-red-50/20" data-status="terlambat">
                                <td class="py-3.5 px-3 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-900 font-bold flex items-center justify-center text-[10px]">DA</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Dimas Anggara</p>
                                        <p class="text-[10px] text-amber-700 font-semibold">Senior Barista</p>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">Shift Pagi<br><span class="text-[10px] text-gray-400">07:00 - 16:00</span></td>
                                <td class="py-3.5 px-3">
                                    <span class="text-red-600 font-bold">⏰ 07:18 WIB</span>
                                    <p class="text-[10px] text-red-600 font-semibold">Terlambat 18m</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[10px] font-bold">⚠ Kedua Izin (Alasan)</span>
                                </td>
                                <td class="py-3.5 px-3">Bar Utama</td>
                                <td class="py-3.5 px-3 text-center">
                                    <button onclick="bukaReviewKeterlambatan('Dimas Anggara')" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold rounded-xl text-[10px] transition shadow">
                                        Tinjau Keterlambatan
                                    </button>
                                </td>
                            </tr>

                            <!-- Baris 4: Hadir Tepat Waktu -->
                            <tr class="hover:bg-gray-50/80 transition item-absen" data-status="hadir">
                                <td class="py-3.5 px-3 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-800 font-bold flex items-center justify-center text-[10px]">NK</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Nina Kurnia</p>
                                        <p class="text-[10px] text-gray-400">Cashier & Floor</p>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">Shift Pagi<br><span class="text-[10px] text-gray-400">07:00 - 15:00</span></td>
                                <td class="py-3.5 px-3">
                                    <span class="text-emerald-700 font-bold">🟢 06:59 WIB</span>
                                    <p class="text-[10px] text-emerald-600 font-semibold">Tepat Waktu</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[10px] font-bold">✔ GPS & Foto OK</span>
                                </td>
                                <td class="py-3.5 px-3">Floor Service</td>
                                <td class="py-3.5 px-3 text-center">
                                    <button onclick="detailAbsenStaf('Nina Kurnia')" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[10px] font-bold">Detail</button>
                                </td>
                            </tr>

                            <!-- Baris 5: Hadir -->
                            <tr class="hover:bg-gray-50/80 transition item-absen" data-status="hadir">
                                <td class="py-3.5 px-3 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gray-800 text-white font-bold flex items-center justify-center text-[10px]">BP</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Bagus Prasetyo</p>
                                        <p class="text-[10px] text-gray-400">Junior Barista</p>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">Shift Siang<br><span class="text-[10px] text-gray-400">12:00 - 20:00</span></td>
                                <td class="py-3.5 px-3">
                                    <span class="text-emerald-700 font-bold">🟢 11:45 WIB</span>
                                    <p class="text-[10px] text-emerald-600 font-semibold">Tepat Waktu</p>
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[10px] font-bold">✔ GPS Valid</span>
                                </td>
                                <td class="py-3.5 px-3">Bar Pendukung</td>
                                <td class="py-3.5 px-3 text-center">
                                    <button onclick="detailAbsenStaf('Bagus Prasetyo')" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-[10px] font-bold">Detail</button>
                                </td>
                            </tr>

                            <!-- Baris 6: Izin / Sakit -->
                            <tr class="hover:bg-gray-50/80 transition item-absen bg-amber-50/20" data-status="izin">
                                <td class="py-3.5 px-3 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-red-100 text-red-800 font-bold flex items-center justify-center text-[10px]">MP</div>
                                    <div>
                                        <p class="font-bold text-gray-900">Maya Putri</p>
                                        <p class="text-[10px] text-gray-400">Kitchen & Pastry</p>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">Shift Pagi<br><span class="text-[10px] text-gray-400">07:00 - 15:00</span></td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 font-bold rounded text-[10px]">Izin Sakit</span>
                                </td>
                                <td class="py-3.5 px-3">Surat Dokter Terlampir</td>
                                <td class="py-3.5 px-3">Kitchen</td>
                                <td class="py-3.5 px-3 text-center">
                                    <button onclick="lihatDokumenIzin('Maya Putri')" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold rounded-lg text-[10px] transition shadow">
                                        Lihat Dokumen
                                    </button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

                <div class="text-center text-xs text-gray-400 pt-2">
                    Menampilkan 6 dari 14 staf terdaftar hari ini • <button onclick="alert('Memuat seluruh data kehadiran historis...')" class="text-amber-800 font-bold">Lihat Semua Staf</button>
                </div>
            </div>

            <!-- KOLOM KANAN (1 Kolom): VERIFIKASI TERAKHIR & KOREKSI MANUAL -->
            <div class="space-y-6">
                
                <!-- Kotak Verifikasi Presensi Terakhir (Dimas Anggara) -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-900">Verifikasi Presensi Terakhir</h3>
                        <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Perlu Review</span>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 mb-4 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gray-300 overflow-hidden flex items-center justify-center font-bold text-gray-700">
                            👤
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Dimas Anggara</p>
                            <p class="text-[10px] text-gray-500">Senior Barista • Shift Pagi</p>
                            <p class="text-[10px] text-red-600 font-semibold mt-0.5">Clock-In 07:18:04 WIB (Terlambat: 18 Menit)</p>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs text-gray-600 mb-4">
                        <div class="flex justify-between">
                            <span>GPS Geofence Store:</span>
                            <span class="text-emerald-700 font-bold">16m dari Cafe Exito (Verified ✔)</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Biometrik / Face Match:</span>
                            <span class="text-emerald-700 font-bold">99.4% Match</span>
                        </div>
                    </div>

                    <!-- FOTO SELFI CLOCK-IN -->
                    <div class="mb-4">
                        <p class="text-[11px] font-bold text-gray-700 mb-1.5">FOTO SELFI CLOCK-IN</p>
                        <div class="h-32 bg-gray-200 rounded-xl overflow-hidden relative border border-gray-300 flex items-center justify-center text-gray-500 font-bold text-xs">
                            📷 [ Pratinjau Foto Selfie Staf ]
                        </div>
                    </div>

                    <!-- CATATAN ALASAN KETERLAMBATAN -->
                    <div class="mb-4 p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px]">
                        <p class="font-bold text-amber-900 mb-0.5">CATATAN ALASAN KETERLAMBATAN:</p>
                        <p class="text-amber-800 italic">"Maaf bos/manajer, ada perbaikan genting / pasang rel flyover Sudirman, macet total 35 menit. Sudah info di grup kepo operasional."</p>
                    </div>

                    <!-- Tombol Aksi Verifikasi -->
                    <div class="flex gap-2">
                        <button onclick="terimaAlasanKeterlambatan('Dimas Anggara')" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow text-center">
                            ✔ Terima Alasan Keterlambatan
                        </button>
                        <button onclick="beriPeringatanPotongJam('Dimas Anggara')" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition">
                            ⚠ Beri Peringatan / Potong Jam
                        </button>
                    </div>
                </div>

                <!-- Kotak Koreksi / Clock-In Manual -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-900">Koreksi / Clock-In Manual</h3>
                        <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-full">Otoritas Shift</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mb-4">Gunakan fitur ini apabila staf mengalami kendala teknis atau lupa clock-in presensi.</p>

                    <form id="formKoreksiAbsen" onsubmit="simpanKoreksiAbsen(event)" class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Pilih Staf:</label>
                            <select id="pilihStafAbsen" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                                <option value="">-- Pilih Staf yang Bersangkutan --</option>
                                <option>Ahmad Ridwan (Head Barista)</option>
                                <option>Siti Rahma (Kasir & POS)</option>
                                <option>Dimas Anggara (Senior Barista)</option>
                                <option>Maya Putri (Kitchen & Pastry)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Jenis Tindakan Presensi:</label>
                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <label class="flex items-center gap-2 p-2 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                                    <input type="radio" name="jenisTindakan" value="Clock-In" checked class="text-[#f97316]"> Clock-In
                                </label>
                                <label class="flex items-center gap-2 p-2 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                                    <input type="radio" name="jenisTindakan" value="Clock-Out" class="text-[#f97316]"> Clock-Out
                                </label>
                                <label class="flex items-center gap-2 p-2 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                                    <input type="radio" name="jenisTindakan" value="Cuti/Lembur" class="text-[#f97316]"> Cuti/Lembur
                                </label>
                                <label class="flex items-center gap-2 p-2 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                                    <input type="radio" name="jenisTindakan" value="Catat Lain" class="text-[#f97316]"> Catat Lain
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-gray-600 mb-1">Jam Efektif:</label>
                                <input type="time" id="jamEfektifAbsen" value="07:00" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-600 mb-1">Stasiun Kerja:</label>
                                <select id="stasiunKerjaAbsen" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800">
                                    <option>Bar Utama</option>
                                    <option>Kasir Depan</option>
                                    <option>Floor Service</option>
                                    <option>Kitchen</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Alasan Over-ride Manajer:</label>
                            <textarea id="alasanOverride" rows="2" placeholder="Contoh: WiFi tablet down, sempat putus koneksi saat meja..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none"></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-[#261c14] hover:bg-black text-white font-bold rounded-xl shadow transition text-center text-xs">
                            💾 Simpan & Verifikasi Absensi
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        // 1. Tab Navigasi Absensi
        function gantiTabAbsensi(jenis, element) {
            document.getElementById('tabHariIni').className = "px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition";
            document.getElementById('tabMingguan').className = "px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition";
            document.getElementById('tabIzin').className = "px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition";

            element.className = "px-4 py-2 bg-[#6b5344] text-white rounded-xl shadow transition font-bold";
            alert(`📋 Memuat data untuk tab: [ ${jenis.toUpperCase()} ]`);
        }

        // 2. Filter Status Staf (Semua, Hadir, Terlambat, Izin)
        function filterStatusStaf(status) {
            const rows = document.querySelectorAll('.item-absen');
            rows.forEach(row => {
                let st = row.getAttribute('data-status');
                if (status === 'semua' || st === status) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // 3. Pencarian Tabel Absensi
        function cariDataAbsensi() {
            let input = document.getElementById('inputCariAbsensi').value.toLowerCase();
            let rows = document.querySelectorAll('#tabelAbsensiStaf tbody tr');

            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                if (text.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // 4. Aksi Baris Tabel
        function detailAbsenStaf(nama) {
            alert(`🔍 Membuka riwayat lengkap log GPS, foto presensi, dan jam kerja untuk staf: ${nama}`);
        }

        function bukaReviewKeterlambatan(nama) {
            alert(`⚠ Membuka lembar peninjauan keterlambatan khusus untuk staf: ${nama}`);
        }

        function lihatDokumenIzin(nama) {
            alert(`📄 Membuka surat keterangan dokter / dokumen izin cuti resmi untuk: ${nama}`);
        }

        // 5. Aksi Kotak Verifikasi Kanan
        function terimaAlasanKeterlambatan(nama) {
            alert(`✅ Alasan keterlambatan [ ${nama} ] telah DITERIMA dan dimaafkan tanpa pemotongan jam.`);
        }

        function beriPeringatanPotongJam(nama) {
            const konfirmasi = confirm(`⚠ Berikan sanksi potong jam kerja / peringatan resmi untuk [ ${nama} ] karena terlambat?`);
            if (konfirmasi) {
                alert(`⚠️ Sanksi pemotongan jam kerja untuk [ ${nama} ] telah dicatat di sistem HR.`);
            }
        }

        // 6. Form Koreksi Clock-In Manual
        function simpanKoreksiAbsen(e) {
            e.preventDefault();
            let staf = document.getElementById('pilihStafAbsen').value;
            if (!staf) {
                alert("❌ Harap pilih nama staf yang bersangkutan terlebih dahulu!");
                return;
            }
            alert(`✅ BERHASIL: Koreksi absensi manual untuk [ ${staf} ] telah disimpan dan disetujui oleh manajer shift!`);
            document.getElementById('formKoreksiAbsen').reset();
        }

        // 7. Tombol Header Atas
        function bukaModalKoreksiManual() {
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            alert("⏱️ Silakan isi formulir koreksi presensi manual di panel bawah kanan.");
        }

        function eksporRekapAbsensi() {
            alert("📥 Mengunduh laporan rekapitulasi presensi harian seluruh staf dalam format Excel / PDF...");
        }
    </script>

</body>
</html>