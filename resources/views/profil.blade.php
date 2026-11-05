<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Cafe Exito</title>
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
                    AB
                </div>
                <div>
                    <h1 class="text-sm font-bold leading-tight">Cafe Exito</h1>
                    <p class="text-[10px] text-amber-200">Manager System</p>
                </div>
            </div>

            <!-- Status Shift Aktif -->
            <div class="mb-5 px-3 py-2 bg-[#584437]/60 rounded-xl border border-white/10 flex items-center gap-2 text-xs font-semibold text-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Shift Manager • On Duty</span>
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
                <a href="{{ route('kelola-laporan') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    <span>📈</span> Kelola Laporan
                </a>
                <!-- Diubah menjadi Profil sesuai menu admin -->
                <a href="{{ route('profil') }}" class="py-2.5 px-4 rounded-xl bg-[#261c14] text-white shadow-sm flex items-center gap-3 cursor-pointer font-semibold border border-white/10">
                    <span>⚙️</span> Profil
                </a>
            </nav>
        </div>

        <!-- Bagian Bawah Sidebar (Bantuan & Profil User Bawah) -->
        <div class="space-y-4 pt-4 border-t border-white/10 text-xs">
            <a href="#" onclick="alert('Membuka pusat bantuan akun...')" class="flex items-center gap-2 text-gray-200 hover:text-white px-2 py-1">
                <span>❓</span> Bantuan
            </a>
            <a href="#" onclick="alert('Membuka pengaturan sistem...')" class="flex items-center gap-2 text-gray-200 hover:text-white px-2 py-1">
                <span>⚙️</span> Pengaturan
            </a>
            
            <div class="bg-[#584437] p-3 rounded-2xl flex items-center justify-between shadow-inner">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-900 font-bold flex items-center justify-center text-xs">AB</div>
                    <div>
                        <p class="text-xs font-bold leading-tight">Adrian Budi S.</p>
                        <p class="text-[9px] text-amber-200">Floor Supervisor</p>
                    </div>
                </div>
                <a href="{{ route('proses-login') }}" class="text-red-300 hover:text-red-100 p-1 rounded-lg transition" title="Logout">
                    🚪
                </a>
            </div>
        </div>
    </aside>

    <!-- KONTEN UTAMA PROFIL -->
    <main class="flex-1 p-6 overflow-y-auto">
        
        <!-- HEADER ATAS -->
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 bg-white p-3.5 rounded-2xl shadow-sm border border-gray-100 gap-3">
            <!-- Kolom Pencarian -->
            <div class="relative w-full sm:w-72">
                <input type="text" id="inputCariPesanan" placeholder="Cari pesanan..." class="w-full pl-9 pr-8 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                <span class="absolute left-3 top-2 text-gray-400 text-xs">🔍</span>
            </div>

            <!-- Tombol Aksi Header Kanan -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <div class="flex items-center gap-2 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200 text-xs font-semibold text-amber-900">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Shift Pagi • 07:00 - 16:00 WIB LIVE</span>
                </div>
                <button onclick="alert('➕ Membuka menu tambah stok barang...')" class="px-3.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                    📦 Tambah Stok
                </button>
                <button onclick="alert('🖨️ Menyiapkan laporan rekapitulasi akun dan operasional...')" class="px-3.5 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                    🖨️ Cetak Laporan
                </button>
                <button onclick="alert('🔄 Sinkronisasi data profil berhasil diperbarui.')" class="p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-gray-600 cursor-pointer" title="Refresh">
                    🔄
                </button>
                <button onclick="alert('🔔 Tidak ada notifikasi baru.')" class="p-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-gray-600 relative cursor-pointer" title="Notifikasi">
                    🔔 <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
                
                <!-- Profil Dropdown Header -->
                <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-200 cursor-pointer" onclick="alert('Menu profil manajer aktif.')">
                    <div class="w-7 h-7 rounded-full bg-[#261c14] text-white font-bold flex items-center justify-center text-xs">AB</div>
                    <div class="text-left leading-tight">
                        <p class="text-xs font-bold text-gray-900">Adrian B.</p>
                        <p class="text-[9px] text-gray-400">Manager</p>
                    </div>
                    <span class="text-xs text-gray-400">▾</span>
                </div>
            </div>
        </header>

        <!-- SUB-HEADER / JUDUL HALAMAN -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Profil</h2>
                <p class="text-xs text-gray-400">Kelola informasi identitas akun dan kata sandi Anda.</p>
            </div>
            
            <div class="flex items-center gap-2 bg-emerald-50 px-3.5 py-2 rounded-xl border border-emerald-200 text-xs font-semibold text-emerald-800 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Cloud Engine • Terenkripsi SSL</span>
            </div>
        </div>

        <!-- GRID UTAMA (KIRI: IDENTITAS MANAGER | KANAN: GANTI KATA SANDI) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-5xl">
            
            <!-- KOTAK KIRI: IDENTITAS MANAGER -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">IDENTITAS MANAGER</span>
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-full flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    </div>

                    <!-- Avatar Lingkaran Besar -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-20 h-20 rounded-full bg-[#261c14] text-white font-bold text-xl flex items-center justify-center shadow-md relative cursor-pointer group" onclick="alert('📷 Klik untuk mengganti foto profil manager.')" title="Ganti Foto Profil">
                            AB
                            <div class="absolute bottom-0 right-0 w-6 h-6 bg-[#f97316] text-white rounded-full flex items-center justify-center text-[10px] shadow border-2 border-white">
                                📷
                            </div>
                        </div>
                        <h3 class="font-bold text-gray-900 text-base mt-3">Adrian Budi Santoso</h3>
                        <p class="text-xs text-gray-400">@adrian.exito <span class="px-2 py-0.5 bg-amber-100 text-amber-900 text-[9px] font-bold rounded-md ml-1">Manager</span></p>
                    </div>

                    <!-- Form Input Identitas -->
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1.5">Nama Lengkap</label>
                            <div class="relative">
                                <input type="text" id="inputNamaLengkap" value="Adrian Budi Santoso" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                                <span class="absolute right-3.5 top-3 text-gray-400 cursor-pointer" onclick="alert('Edit nama lengkap diaktifkan.')" title="Edit">✏️</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-600 mb-1.5">Alamat Email</label>
                            <div class="relative">
                                <input type="email" id="inputEmail" value="adrian@cafeexito.id" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                                <span class="absolute right-3.5 top-3 text-gray-400">✉️</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Kotak Identitas -->
                <div class="mt-8 pt-4 border-t border-gray-100 flex justify-between items-center text-[11px] text-gray-400">
                    <span>Role: SHIFT & Floor Manager</span>
                    <span>ID: MGR-0001-0186</span>
                </div>
            </div>

            <!-- KOTAK KANAN: GANTI KATA SANDI -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="text-base">🔒</span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Ganti Kata Sandi</h3>
                            <p class="text-[11px] text-gray-400">Perbarui kata sandi untuk mengamankan akun.</p>
                        </div>
                    </div>

                    <form id="formGantiSandi" onsubmit="simpanKataSandiBaru(event)" class="space-y-4 text-xs mt-4">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1.5">Kata Sandi Saat Ini</label>
                            <div class="relative">
                                <input type="password" id="sandiLama" value="KopiExito2023!" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                                <span class="absolute right-3.5 top-3 text-gray-400 cursor-pointer" onclick="toggleTampilSandi('sandiLama')">👁️</span>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="font-semibold text-gray-600">Kata Sandi Baru</label>
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[9px] font-bold rounded">✔ Kuat</span>
                            </div>
                            <div class="relative">
                                <input type="password" id="sandiBaru" value="ExitoRoaster#2024" onkeyup="cekKekuatanSandi()" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                                <span class="absolute right-3.5 top-3 text-gray-400 cursor-pointer" onclick="toggleTampilSandi('sandiBaru')">👁️</span>
                            </div>
                            <!-- Indikator Bar Kekuatan Sandi -->
                            <div class="grid grid-cols-3 gap-1.5 mt-2">
                                <div class="h-1 bg-amber-400 rounded-full"></div>
                                <div class="h-1 bg-amber-400 rounded-full"></div>
                                <div class="h-1 bg-emerald-500 rounded-full"></div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-600 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                            <div class="relative">
                                <input type="password" id="sandiKonfirmasi" value="ExitoRoaster#2024" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#f97316]">
                                <span class="absolute right-3.5 top-3 text-gray-400 cursor-pointer" onclick="toggleTampilSandi('sandiKonfirmasi')">👁️</span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tombol Batal & Simpan Perubahan -->
                <div class="mt-8 pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="resetFormSandi()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" onclick="simpanKataSandiBaru(event)" class="px-5 py-2 bg-[#261c14] hover:bg-black text-white font-bold rounded-xl text-xs transition shadow cursor-pointer flex items-center gap-1.5">
                        💾 Simpan Perubahan
                    </button>
                </div>
            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        // 1. Fungsi Toggle Lihat/Sembunyikan Sandi
        function toggleTampilSandi(idInput) {
            let input = document.getElementById(idInput);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }

        // 2. Simulasi Indikator Kekuatan Sandi
        function cekKekuatanSandi() {
            let sandi = document.getElementById('sandiBaru').value;
            if (sandi.length < 6) {
                // Lemah
            } else if (sandi.length < 10) {
                // Sedang
            } else {
                // Kuat
            }
        }

        // 3. Tombol Reset Form
        function resetFormSandi() {
            document.getElementById('formGantiSandi').reset();
            alert("↺ Form kata sandi diatur ulang.");
        }

        // 4. Simpan Perubahan Sandi & Identitas
        function simpanKataSandiBaru(e) {
            e.preventDefault();
            let nama = document.getElementById('inputNamaLengkap').value;
            let sandiBaru = document.getElementById('sandiBaru').value;
            let konfirmasi = document.getElementById('sandiKonfirmasi').value;

            if (sandiBaru !== konfirmasi) {
                alert("❌ Konfirmasi kata sandi baru tidak cocok!");
                return;
            }

            alert(`✅ BERHASIL: Profil akun [ ${nama} ] dan kata sandi baru telah diperbarui dan disinkronkan ke server aman Cafe Exito!`);
        }
    </script>

</body>
</html>