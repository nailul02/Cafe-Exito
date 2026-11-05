<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Kasir - Exito Cafe</title>
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

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#6b5344] text-white flex flex-col p-6 min-h-screen">
        <!-- Logo & Nama Cafe -->
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-800 font-serif italic font-bold shadow-sm">
                exito
            </div>
            <span class="text-lg font-semibold tracking-wide">cafe exito</span>
        </div>

        <!-- Menu Navigasi Sesuai Desain -->
        <nav class="flex flex-col gap-4 text-sm">
            <a href="{{ route('dashboard') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Dashboard</a>
            <a href="{{ route('pesanan-kasir') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">pesenan</a>
            <a href="{{ route('detail-transaksi') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Detail Transaksi</a>
            <a href="{{ route('absensi-kasir') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Absensi</a>
            <a href="{{ route('profil-kasir') }}" class="py-2 px-4 rounded-md bg-[#584437] font-medium text-white">Profil</a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-8">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Profil</h1>
            
            <!-- Profil User Header -->
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p id="headerNama" class="text-sm font-semibold text-gray-800">ucup</p>
                    <p class="text-xs text-gray-500">Kasir</p>
                </div>
                <div id="headerAvatar" class="w-10 h-10 rounded-full bg-gray-300 overflow-hidden flex items-center justify-center font-bold text-gray-600">
                    👤
                </div>
            </div>
        </header>

        <!-- KARTU PROFIL -->
        <div class="bg-[#6b5344] text-white p-8 rounded-2xl shadow-md max-w-lg mx-auto flex flex-col items-center">
            
            <!-- Foto Profil Avatar (Bisa Diganti saat Edit) -->
            <div class="relative mb-6">
                <div id="avatarContainer" class="w-24 h-24 rounded-full bg-gray-300 border-4 border-white/20 shadow-inner flex items-center justify-center text-gray-600 text-3xl font-bold overflow-hidden">
                    <span id="avatarIcon">👤</span>
                    <img id="avatarImage" src="" alt="Foto Profil" class="w-full h-full object-cover hidden">
                </div>
                
                <!-- Tombol Ganti Foto (Hanya muncul saat mode edit) -->
                <label id="labelGantiFoto" for="inputFoto" class="absolute bottom-0 right-0 bg-amber-400 hover:bg-amber-500 text-gray-900 p-1.5 rounded-full shadow cursor-pointer hidden transition" title="Ganti Foto">
                    📷
                </label>
                <input type="file" id="inputFoto" accept="image/*" class="hidden" onchange="gantiFotoProfil(event)">
            </div>

            <!-- FORM / TAMPILAN PROFIL -->
            <div class="w-full space-y-4 text-center">
                
                <!-- Nama -->
                <div>
                    <p class="text-xs text-gray-200 uppercase tracking-wider font-medium">Nama</p>
                    <p id="viewNama" class="text-base font-bold text-white mt-0.5">ucup</p>
                    <input type="text" id="inputNama" value="ucup" class="w-full max-w-xs mt-1 px-3 py-1.5 bg-white text-gray-900 text-sm font-semibold rounded-lg shadow-inner hidden text-center">
                </div>

                <!-- Username -->
                <div>
                    <p class="text-xs text-gray-200 uppercase tracking-wider font-medium">Username</p>
                    <p id="viewUsername" class="text-base font-bold text-white mt-0.5">ucup</p>
                    <input type="text" id="inputUsername" value="ucup" class="w-full max-w-xs mt-1 px-3 py-1.5 bg-white text-gray-900 text-sm font-semibold rounded-lg shadow-inner hidden text-center">
                </div>

                <!-- Email -->
                <div>
                    <p class="text-xs text-gray-200 uppercase tracking-wider font-medium">Email</p>
                    <p id="viewEmail" class="text-base font-bold text-white mt-0.5">ucup@cafeexito.com</p>
                    <input type="email" id="inputEmail" value="ucup@cafeexito.com" class="w-full max-w-xs mt-1 px-3 py-1.5 bg-white text-gray-900 text-sm font-semibold rounded-lg shadow-inner hidden text-center">
                </div>

                <!-- Jabatan (Readonly) -->
                <div>
                    <p class="text-xs text-gray-200 uppercase tracking-wider font-medium">Jabatan</p>
                    <p class="text-base font-bold text-amber-300 mt-0.5">Kasir</p>
                </div>

            </div>

            <!-- TOMBOL AKSI EDIT / SIMPAN -->
            <div class="mt-8 w-full max-w-xs">
                <!-- Tombol Edit Awal -->
                <button type="button" id="btnEdit" onclick="mulaiEdit()" class="w-full py-2.5 bg-white/20 hover:bg-white hover:text-gray-900 font-bold rounded-xl text-xs transition shadow border border-white/30">
                    [ EDIT PROFIL ]
                </button>

                <!-- Tombol Simpan & Batal (Sembunyikan default) -->
                <div id="actionSimpanBatal" class="flex gap-3 hidden">
                    <button type="button" onclick="batalEdit()" class="flex-1 py-2.5 bg-red-600/80 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition shadow">
                        Batal
                    </button>
                    <button type="button" onclick="simpanProfil()" class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-xs transition shadow">
                        Simpan
                    </button>
                </div>
            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF EDIT PROFIL & GANTI FOTO -->
    <script>
        let fotoBaruUrl = null;

        function mulaiEdit() {
            // Tampilkan input teks
            document.getElementById('viewNama').classList.add('hidden');
            document.getElementById('inputNama').classList.remove('hidden');

            document.getElementById('viewUsername').classList.add('hidden');
            document.getElementById('inputUsername').classList.remove('hidden');

            document.getElementById('viewEmail').classList.add('hidden');
            document.getElementById('inputEmail').classList.remove('hidden');

            // Tampilkan tombol ganti foto
            document.getElementById('labelGantiFoto').classList.remove('hidden');

            // Atur tombol aksi
            document.getElementById('btnEdit').classList.add('hidden');
            document.getElementById('actionSimpanBatal').classList.remove('hidden');
        }

        function gantiFotoProfil(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoBaruUrl = e.target.result;
                    const avatarImage = document.getElementById('avatarImage');
                    const avatarIcon = document.getElementById('avatarIcon');

                    avatarImage.src = fotoBaruUrl;
                    avatarImage.classList.remove('hidden');
                    avatarIcon.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function batalEdit() {
            document.getElementById('inputNama').value = document.getElementById('viewNama').textContent;
            document.getElementById('inputUsername').value = document.getElementById('viewUsername').textContent;
            document.getElementById('inputEmail').value = document.getElementById('viewEmail').textContent;
            fotoBaruUrl = null;

            tutupModeEdit();
        }

        function simpanProfil() {
            const namaBaru = document.getElementById('inputNama').value;
            const usernameBaru = document.getElementById('inputUsername').value;
            const emailBaru = document.getElementById('inputEmail').value;

            if (!namaBaru || !usernameBaru || !emailBaru) {
                alert("Semua kolom profil wajib diisi!");
                return;
            }

            // Perbarui teks profil
            document.getElementById('viewNama').textContent = namaBaru;
            document.getElementById('viewUsername').textContent = usernameBaru;
            document.getElementById('viewEmail').textContent = emailBaru;
            document.getElementById('headerNama').textContent = namaBaru;

            // Jika foto baru diunggah, perbarui avatar di header juga
            if (fotoBaruUrl) {
                const headerAvatar = document.getElementById('headerAvatar');
                headerAvatar.innerHTML = `<img src="${fotoBaruUrl}" class="w-full h-full object-cover">`;
            }

            tutupModeEdit();
            alert("✅ Profil dan foto berhasil diperbarui!");
        }

        function tutupModeEdit() {
            document.getElementById('viewNama').classList.remove('hidden');
            document.getElementById('inputNama').classList.add('hidden');

            document.getElementById('viewUsername').classList.remove('hidden');
            document.getElementById('inputUsername').classList.add('hidden');

            document.getElementById('viewEmail').classList.remove('hidden');
            document.getElementById('inputEmail').classList.add('hidden');

            document.getElementById('labelGantiFoto').classList.add('hidden');

            document.getElementById('btnEdit').classList.remove('hidden');
            document.getElementById('actionSimpanBatal').classList.add('hidden');
        }
    </script>

</body>
</html>