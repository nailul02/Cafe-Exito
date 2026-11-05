<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun Owner - Exito Cafe</title>
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

            <!-- Menu Navigasi Owner -->
            <nav class="flex flex-col gap-2 text-sm font-medium">
                <a href="{{ route('owner-dashboard') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    📊 Dashboard
                </a>
                <a href="{{ route('laporan-penjualan') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center gap-3 cursor-pointer">
                    📈 Laporan Penjualan
                </a>
                <a href="{{ route('stok-bahan') }}" class="py-2.5 px-4 rounded-xl hover:bg-[#584437]/50 text-gray-200 transition flex items-center justify-between cursor-pointer">
                    <span class="flex items-center gap-3">📦 Stok Bahan</span>
                    <span class="bg-red-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">3 Kritis</span>
                </a>
                <a href="{{ route('profil-akun') }}" class="py-2.5 px-4 rounded-xl bg-[#584437] text-white shadow-sm flex items-center gap-3 cursor-pointer">
                    ⚙️ Profil Akun
                </a>
            </nav>
        </div>

        <!-- Bagian Bawah Sidebar (Tombol Kasir POS & Profil User) -->
        <div class="space-y-4 pt-6 border-t border-white/10">
            <a href="{{ route('dashboard') }}" class="w-full py-2.5 px-4 bg-[#8b6f5e] hover:bg-[#7a5f4e] text-white text-xs font-bold rounded-xl shadow transition flex items-center justify-center gap-2 cursor-pointer text-center">
                ☕ Buka Kasir POS
            </a>
            
            <!-- Profil User di Bawah Sidebar -->
            <div class="flex items-center justify-between bg-[#584437] p-3 rounded-xl">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-900 font-bold flex items-center justify-center text-xs shadow">
                        AB
                    </div>
                    <div>
                        <p class="text-xs font-bold leading-tight">Adrian Budi S.</p>
                        <p class="text-[10px] text-amber-200">Owner / Roaster</p>
                    </div>
                </div>
                <a href="{{ route('proses-login') }}" class="text-red-200 hover:text-white transition text-sm" title="Logout">
                    🚪
                </a>
            </div>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-8 overflow-y-auto relative z-10">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Profil Akun</h2>
                <p class="text-xs text-gray-400 mt-0.5">Kelola informasi identitas akun dan kata sandi Anda.</p>
            </div>
        </header>

        <!-- GRID DUA KOLOM (IDENTITAS OWNER & GANTI KATA SANDI) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl">
            
            <!-- KOTAK 1: IDENTITAS OWNER -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <!-- Judul & Badge Aktif -->
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Identitas Owner</span>
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full">✔ Aktif</span>
                    </div>

                    <!-- Avatar & Nama Utama -->
                    <div class="flex flex-col items-center text-center mb-6">
                        <div class="w-20 h-20 rounded-full bg-[#584437] text-amber-100 font-bold text-2xl flex items-center justify-center shadow-md mb-3 relative group">
                            AB
                            <span class="absolute bottom-0 right-0 w-5 h-5 bg-amber-500 rounded-full text-white text-[10px] flex items-center justify-center border-2 border-white">⭐</span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Adrian Budi Santoso</h3>
                        <p class="text-xs text-amber-800 font-medium mt-0.5">@adrian.exito • <span class="bg-amber-100 text-amber-900 px-2 py-0.5 rounded text-[10px] font-bold">Owner</span></p>
                    </div>

                    <!-- Form Identitas -->
                    <form id="formIdentitas" onsubmit="simpanIdentitas(event)" class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Nama Lengkap</label>
                            <div class="relative">
                                <input type="text" id="inputNamaLengkap" value="Adrian Budi Santoso" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#6b5344]">
                                <span class="absolute right-3 top-2.5 text-gray-400">✏️</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Alamat Email</label>
                            <div class="relative">
                                <input type="email" id="inputEmail" value="adrian@cafeexito.id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#6b5344]">
                                <span class="absolute right-3 top-2.5 text-gray-400">✉️</span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer Kotak Identitas -->
                <div class="mt-8 pt-4 border-t border-gray-100 flex justify-between items-center text-[11px] text-gray-400">
                    <span>Role: Full Access (Owner)</span>
                    <span class="font-mono font-semibold text-gray-600">ID: #OWN-001</span>
                </div>
            </div>

            <!-- KOTAK 2: GANTI KATA SANDI -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <!-- Judul -->
                    <div class="flex items-center gap-2.5 mb-6">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-700 text-sm">🔒</div>
                        <div>
                            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Ganti Kata Sandi</h3>
                            <p class="text-[11px] text-gray-400">Perbarui kata sandi untuk mengamankan akun.</p>
                        </div>
                    </div>

                    <!-- Form Ganti Sandi -->
                    <form id="formGantiSandi" onsubmit="simpanSandiBaru(event)" class="space-y-4 text-xs">
                        
                        <!-- Sandi Saat Ini -->
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Kata Sandi Saat Ini</label>
                            <div class="relative">
                                <input type="password" id="sandiLama" value="KopiExito2023!" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#6b5344]">
                                <button type="button" onclick="togglePassword('sandiLama', this)" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-700">👁️</button>
                            </div>
                        </div>

                        <!-- Sandi Baru -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="font-semibold text-gray-600">Kata Sandi Baru</label>
                                <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full">✔ Kuat</span>
                            </div>
                            <div class="relative">
                                <input type="password" id="sandiBaru" value="ExitoRoaster#2024" onkeyup="cekKekuatanSandi()" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#6b5344]">
                                <button type="button" onclick="togglePassword('sandiBaru', this)" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-700">👁️</button>
                            </div>
                            <!-- Indikator Bar Kekuatan Sandi -->
                            <div class="grid grid-cols-3 gap-1.5 mt-2">
                                <div id="bar1" class="h-1.5 bg-amber-500 rounded-full transition-all"></div>
                                <div id="bar2" class="h-1.5 bg-emerald-500 rounded-full transition-all"></div>
                                <div id="bar3" class="h-1.5 bg-emerald-500 rounded-full transition-all"></div>
                            </div>
                        </div>

                        <!-- Konfirmasi Sandi Baru -->
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Konfirmasi Kata Sandi Baru</label>
                            <div class="relative">
                                <input type="password" id="sandiKonfirmasi" value="ExitoRoaster#2024" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#6b5344]">
                                <button type="button" onclick="togglePassword('sandiKonfirmasi', this)" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-700">👁️</button>
                            </div>
                        </div>

                        <!-- Tombol Aksi Bawah -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" onclick="resetForm()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-gray-900 hover:bg-black text-white font-bold rounded-xl shadow transition">
                                💾 Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        // 1. Fungsi Toggle Lihat/Sembunyikan Password
        function togglePassword(idInput, btn) {
            const input = document.getElementById(idInput);
            if (input.type === "password") {
                input.type = "text";
                btn.textContent = "🙈";
            } else {
                input.type = "password";
                btn.textContent = "👁️";
            }
        }

        // 2. Cek Kekuatan Sandi Secara Real-time
        function cekKekuatanSandi() {
            const val = document.getElementById('sandiBaru').value;
            const bar1 = document.getElementById('bar1');
            const bar2 = document.getElementById('bar2');
            const bar3 = document.getElementById('bar3');

            if (val.length < 6) {
                bar1.className = "h-1.5 bg-red-500 rounded-full transition-all";
                bar2.className = "h-1.5 bg-gray-200 rounded-full transition-all";
                bar3.className = "h-1.5 bg-gray-200 rounded-full transition-all";
            } else if (val.length < 10) {
                bar1.className = "h-1.5 bg-amber-500 rounded-full transition-all";
                bar2.className = "h-1.5 bg-amber-500 rounded-full transition-all";
                bar3.className = "h-1.5 bg-gray-200 rounded-full transition-all";
            } else {
                bar1.className = "h-1.5 bg-emerald-500 rounded-full transition-all";
                bar2.className = "h-1.5 bg-emerald-500 rounded-full transition-all";
                bar3.className = "h-1.5 bg-emerald-500 rounded-full transition-all";
            }
        }

        // 3. Simpan Identitas / Profil
        function simpanIdentitas(e) {
            e.preventDefault();
            alert("✅ Identitas owner berhasil diperbarui!");
        }

        // 4. Simpan Sandi Baru
        function simpanSandiBaru(e) {
            e.preventDefault();
            const baru = document.getElementById('sandiBaru').value;
            const konf = document.getElementById('sandiKonfirmasi').value;

            if (baru !== konf) {
                alert("❌ Konfirmasi kata sandi baru tidak cocok!");
                return;
            }

            alert("🔒 Kata sandi akun berhasil diperbarui! Silakan gunakan sandi baru untuk login selanjutnya.");
        }

        // 5. Reset Form
        function resetForm() {
            document.getElementById('formGantiSandi').reset();
            alert("🔄 Perubahan dibatalkan.");
        }
    </script>

</body>
</html>