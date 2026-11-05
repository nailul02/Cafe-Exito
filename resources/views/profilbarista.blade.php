<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Barista - Exito Cafe</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- SIDEBAR BARISTA -->
    <aside class="w-64 bg-[#6b5344] text-white flex flex-col p-6 min-h-screen shadow-md">
        <!-- Logo & Nama Cafe -->
        <div class="flex items-center gap-3 mb-12">
            <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-gray-800 font-serif italic font-bold shadow-md">
                exito
            </div>
            <span class="text-xl font-semibold tracking-wide">cafe exito</span>
        </div>

        <!-- Menu Navigasi Barista -->
        <nav class="flex flex-col gap-5 text-sm font-medium">
            <a href="{{ route('dashboard-barista') }}" class="py-2.5 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Dashboard</a>
            <a href="{{ route('pesanan-barista') }}" class="py-2.5 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">pesenan</a>
            <a href="{{ route('absensi-barista') }}" class="py-2.5 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Absensi</a>
            <a href="{{ route('profil-barista') }}" class="py-2.5 px-4 rounded-md bg-[#584437] text-white shadow-sm">Profil</a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-10 flex flex-col">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Profil</h1>
            
            <!-- Profil User Header -->
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p id="headerNama" class="text-sm font-bold text-gray-900">Nana</p>
                    <p class="text-xs text-gray-500 font-medium">Barista</p>
                </div>
                <div class="w-11 h-11 rounded-full bg-gray-300 overflow-hidden flex items-center justify-center font-bold text-gray-600 shadow-sm">
                    <img id="headerAvatar" src="" alt="Avatar" class="w-full h-full object-cover hidden">
                    <span id="headerAvatarFallback">👤</span>
                </div>
            </div>
        </header>

        <!-- KONTEN UTAMA DITENGAHKAN -->
        <div class="flex-1 flex items-center justify-center">
            
            <!-- KARTU PROFIL UTAMA (DI TENGAH) -->
            <div class="bg-[#6b5344] text-white p-8 rounded-2xl shadow-xl w-full max-w-md">
                
                <form id="formProfil" onsubmit="simpanProfil(event)">
                    
                    <!-- Foto Profil / Avatar -->
                    <div class="flex flex-col items-center mb-6 relative">
                        <div class="w-24 h-24 rounded-full bg-gray-300 overflow-hidden flex items-center justify-center text-gray-600 shadow-md relative group">
                            <img id="previewAvatar" src="" alt="Foto Profil" class="w-full h-full object-cover hidden">
                            <span id="placeholderAvatar" class="text-3xl font-bold">👤</span>
                            
                            <!-- Overlay Ganti Foto saat Mode Edit -->
                            <label id="labelGantiFoto" class="absolute inset-0 bg-black/60 text-white text-xs font-semibold flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer hidden">
                                Ganti Foto
                                <input type="file" id="inputFoto" accept="image/*" class="hidden" onchange="gantiFotoProfil(event)">
                            </label>
                        </div>
                    </div>

                    <!-- Informasi Data Profil / Kolom Input -->
                    <div class="space-y-4">
                        
                        <!-- Nama -->
                        <div>
                            <label class="block text-xs text-amber-200 font-semibold uppercase tracking-wider mb-1">Nama</label>
                            <div id="textNama" class="w-full px-3 py-2 bg-white/10 rounded-lg text-white font-medium text-sm">Nana</div>
                            <input type="text" id="inputNama" value="Nana" class="w-full px-3 py-2 bg-white/25 rounded-lg text-white font-medium text-sm border border-white/40 focus:outline-none focus:ring-2 focus:ring-white hidden">
                        </div>

                        <!-- Username -->
                        <div>
                            <label class="block text-xs text-amber-200 font-semibold uppercase tracking-wider mb-1">Username</label>
                            <div id="textUsername" class="w-full px-3 py-2 bg-white/10 rounded-lg text-white font-medium text-sm">Nana</div>
                            <input type="text" id="inputUsername" value="Nana" class="w-full px-3 py-2 bg-white/25 rounded-lg text-white font-medium text-sm border border-white/40 focus:outline-none focus:ring-2 focus:ring-white hidden">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs text-amber-200 font-semibold uppercase tracking-wider mb-1">Email</label>
                            <div id="textEmail" class="w-full px-3 py-2 bg-white/10 rounded-lg text-white font-medium text-sm">nana@cafeexito.com</div>
                            <input type="email" id="inputEmail" value="nana@cafeexito.com" class="w-full px-3 py-2 bg-white/25 rounded-lg text-white font-medium text-sm border border-white/40 focus:outline-none focus:ring-2 focus:ring-white hidden">
                        </div>

                        <!-- Jabatan -->
                        <div>
                            <label class="block text-xs text-amber-200 font-semibold uppercase tracking-wider mb-1">Jabatan</label>
                            <div class="w-full px-3 py-2 bg-white/10 rounded-lg text-white/80 font-medium text-sm">Barista</div>
                        </div>

                    </div>

                    <!-- Tombol Aksi (Edit / Simpan & Batal) -->
                    <div class="mt-8 text-center">
                        <!-- Tombol Masuk Mode Edit -->
                        <button type="button" id="btnEdit" onclick="aktifkanModeEdit()" class="px-6 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition shadow border border-white/30">
                            [ Edit Profil ]
                        </button>

                        <!-- Tombol Simpan & Batal (Hidden Awal) -->
                        <div id="actionButtons" class="flex gap-3 justify-center hidden">
                            <button type="button" onclick="batalEdit()" class="flex-1 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-bold rounded-lg transition shadow">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow">
                                Simpan
                            </button>
                        </div>
                    </div>

                </form>

            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF EDIT PROFIL -->
    <script>
        let fotoBaruSementara = null;

        function aktifkanModeEdit() {
            // Sembunyikan teks statis, tampilkan kolom input
            document.getElementById('textNama').classList.add('hidden');
            document.getElementById('inputNama').classList.remove('hidden');

            document.getElementById('textUsername').classList.add('hidden');
            document.getElementById('inputUsername').classList.remove('hidden');

            document.getElementById('textEmail').classList.add('hidden');
            document.getElementById('inputEmail').classList.remove('hidden');

            // Tampilkan tombol ganti foto pada avatar
            document.getElementById('labelGantiFoto').classList.remove('hidden');

            // Sembunyikan tombol edit, tampilkan tombol Simpan & Batal
            document.getElementById('btnEdit').classList.add('hidden');
            document.getElementById('actionButtons').classList.remove('hidden');
        }

        function batalEdit() {
            // Kembalikan nilai input ke teks semula
            document.getElementById('inputNama').value = document.getElementById('textNama').textContent;
            document.getElementById('inputUsername').value = document.getElementById('textUsername').textContent;
            document.getElementById('inputEmail').value = document.getElementById('textEmail').textContent;

            // Sembunyikan kolom input, tampilkan teks statis
            document.getElementById('textNama').classList.remove('hidden');
            document.getElementById('inputNama').classList.add('hidden');

            document.getElementById('textUsername').classList.remove('hidden');
            document.getElementById('inputUsername').classList.add('hidden');

            document.getElementById('textEmail').classList.remove('hidden');
            document.getElementById('inputEmail').classList.add('hidden');

            document.getElementById('labelGantiFoto').classList.add('hidden');

            document.getElementById('btnEdit').classList.remove('hidden');
            document.getElementById('actionButtons').classList.add('hidden');
        }

        function gantiFotoProfil(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoBaruSementara = e.target.result;
                    document.getElementById('previewAvatar').src = fotoBaruSementara;
                    document.getElementById('previewAvatar').classList.remove('hidden');
                    document.getElementById('placeholderAvatar').classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function simpanProfil(event) {
            event.preventDefault();

            // Ambil nilai dari input kolom
            const namaBaru = document.getElementById('inputNama').value;
            const usernameBaru = document.getElementById('inputUsername').value;
            const emailBaru = document.getElementById('inputEmail').value;

            // Perbarui tampilan teks statis
            document.getElementById('textNama').textContent = namaBaru;
            document.getElementById('textUsername').textContent = usernameBaru;
            document.getElementById('textEmail').textContent = emailBaru;

            // Perbarui header atas
            document.getElementById('headerNama').textContent = namaBaru;

            if (fotoBaruSementara) {
                document.getElementById('headerAvatar').src = fotoBaruSementara;
                document.getElementById('headerAvatar').classList.remove('hidden');
                document.getElementById('headerAvatarFallback').classList.add('hidden');
            }

            // Keluar dari mode edit
            batalEdit();
            alert("✅ Profil berhasil diperbarui!");
        }
    </script>

</body>
</html>