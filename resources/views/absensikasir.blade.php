<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Kasir - Exito Cafe</title>
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
            <a href="{{ route('absensi-kasir') }}" class="py-2 px-4 rounded-md bg-[#584437] font-medium text-white">Absensi</a>
            <a href="{{ route('profil-kasir') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Profil</a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-8">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Absensi</h1>
            
            <!-- Profil User -->
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-800">ucup</p>
                    <p class="text-xs text-gray-500">Kasir</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-300"></div>
            </div>
        </header>

        <!-- KONTEN ABSENSI -->
        <div class="max-w-xl">
            
            <!-- Tab Kategori Absensi (Hadir, Izin, Sakit) -->
            <div class="flex gap-6 mb-6 text-sm font-semibold text-gray-800">
                <button type="button" onclick="pilihAbsensi('hadir', this)" class="tab-btn pb-1 border-b-2 border-gray-900 text-gray-900 transition">Hadir</button>
                <button type="button" onclick="pilihAbsensi('izin', this)" class="tab-btn pb-1 border-b-2 border-transparent text-gray-400 hover:text-gray-700 transition">Izin</button>
                <button type="button" onclick="pilihAbsensi('sakit', this)" class="tab-btn pb-1 border-b-2 border-transparent text-gray-400 hover:text-gray-700 transition">Sakit</button>
            </div>

            <!-- Kartu Kotak Cokelat Absensi -->
            <div class="bg-[#6b5344] text-white p-6 rounded-2xl shadow-md w-full max-w-md flex flex-col justify-between" style="min-height: 440px;">
                
                <div>
                    <!-- Keterangan Dinamis Berdasarkan Tab yang Dipilih -->
                    <p id="teksKeterangan" class="text-xs font-semibold text-amber-200 mb-3 text-center">📸 Silakan ambil foto selfie untuk konfirmasi Kehadiran.</p>
                </div>

                <!-- Bagian Tombol Pilihan Khusus Tab Izin & Sakit (Potret vs Kirim File) -->
                <div id="opsiIzinSakit" class="hidden flex gap-2 mb-3">
                    <button type="button" onclick="aktifkanMode('potret')" id="btnOptPotret" class="flex-1 py-1.5 bg-white text-gray-950 font-bold rounded-lg text-xs transition shadow">
                        📷 Potret Kamera
                    </button>
                    <button type="button" onclick="aktifkanMode('file')" id="btnOptFile" class="flex-1 py-1.5 bg-white/20 hover:bg-white/35 text-white font-semibold rounded-lg text-xs transition">
                        📁 Kirim File Doc/Gambar
                    </button>
                </div>

                <!-- Area Tampilan Utama (Kamera / File Upload) -->
                <div class="bg-[#e9e3de] rounded-xl h-52 flex flex-col items-center justify-center border-2 border-dashed border-gray-400 text-gray-500 relative overflow-hidden shadow-inner">
                    
                    <!-- Mode 1: Video Kamera Langsung -->
                    <video id="video" autoplay playsinline class="absolute inset-0 w-full h-full object-cover"></video>
                    <canvas id="canvas" class="hidden"></canvas>

                    <!-- Mode 2: Pratinjau Hasil Foto / File Dokumen -->
                    <img id="fotoHasil" src="" alt="Hasil Lampiran" class="absolute inset-0 w-full h-full object-cover hidden">
                    
                    <!-- Indikator teks jika file dokumen diunggah -->
                    <div id="fileInfoDisplay" class="absolute inset-0 flex flex-col items-center justify-center bg-[#e9e3de] hidden p-4 text-center">
                        <span class="text-3xl mb-1">📄</span>
                        <span id="namaFileTxt" class="text-xs font-bold text-gray-800 break-all"></span>
                    </div>

                    <!-- Tombol Ambil Foto (Hanya muncul saat mode potret aktif) -->
                    <button type="button" id="btnCapture" onclick="ambilFoto()" class="absolute bottom-3 px-4 py-1.5 bg-gray-900/80 hover:bg-gray-900 text-white font-semibold rounded-full text-xs shadow transition z-10">
                        📸 Potret Sekarang
                    </button>

                    <!-- Input File Tersembunyi (Aktif saat mode Kirim File dipilih) -->
                    <input type="file" id="fileInputDoc" accept=".pdf,.doc,.docx,image/*" class="absolute inset-0 opacity-0 cursor-pointer hidden" onchange="unggahFileDoc(event)">
                </div>

                <!-- Tombol Aksi (Hapus & Kirim) -->
                <div class="flex justify-between items-center pt-4">
                    <button type="button" onclick="ulangLampiran()" class="text-sm font-semibold text-white/90 hover:text-white transition">
                        hapus
                    </button>
                    <button type="button" onclick="kirimAbsen()" class="px-6 py-2 bg-[#584437] hover:bg-black text-white text-sm font-semibold rounded-xl shadow transition border border-white/20">
                        Kirim
                    </button>
                </div>

            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF -->
    <script>
        let statusAbsen = 'hadir'; // hadir, izin, sakit
        let modeInput = 'potret';  // potret atau file
        let fileDataTerpilih = null;

        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const fotoHasil = document.getElementById('fotoHasil');
        const btnCapture = document.getElementById('btnCapture');
        const opsiIzinSakit = document.getElementById('opsiIzinSakit');
        const fileInputDoc = document.getElementById('fileInputDoc');
        const fileInfoDisplay = document.getElementById('fileInfoDisplay');
        const namaFileTxt = document.getElementById('namaFileTxt');

        // Jalankan kamera saat halaman dimuat pertama kali
        window.addEventListener('load', function () {
            mulaiKamera();
        });

        function mulaiKamera() {
            if (modeInput === 'potret') {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false })
                    .then(function (stream) {
                        video.srcObject = stream;
                        video.style.display = 'block';
                        fotoHasil.classList.add('hidden');
                        fileInfoDisplay.classList.add('hidden');
                        btnCapture.style.display = 'block';
                    })
                    .catch(function (error) {
                        console.error("Gagal mengakses kamera: ", error);
                    });
            }
        }

        function matikanKamera() {
            if (video.srcObject) {
                let tracks = video.srcObject.getTracks();
                tracks.forEach(track => track.stop());
                video.srcObject = null;
            }
            video.style.display = 'none';
        }

        function pilihAbsensi(kategori, element) {
            statusAbsen = kategori;
            
            // Ubah garis bawah tab aktif
            const tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(tab => {
                tab.className = "tab-btn pb-1 border-b-2 border-transparent text-gray-400 hover:text-gray-700 transition";
            });
            element.className = "tab-btn pb-1 border-b-2 border-gray-900 text-gray-900 transition";

            const teksKeterangan = document.getElementById('teksKeterangan');

            if (kategori === 'hadir') {
                opsiIzinSakit.classList.add('hidden');
                modeInput = 'potret';
                fileInputDoc.classList.add('hidden');
                teksKeterangan.textContent = "📸 Silakan ambil foto selfie untuk konfirmasi Kehadiran.";
                mulaiKamera();
            } else if (kategori === 'izin') {
                opsiIzinSakit.classList.remove('hidden');
                teksKeterangan.textContent = "📄 Pilih opsi potret surat izin atau unggah file dokumen/gambar.";
                aktifkanMode('potret');
            } else if (kategori === 'sakit') {
                opsiIzinSakit.classList.remove('hidden');
                teksKeterangan.textContent = "🏥 Pilih opsi potret surat dokter atau unggah file dokumen/gambar.";
                aktifkanMode('potret');
            }
            ulangLampiran();
        }

        // Mengatur perpindahan antara mode 'potret' dan 'file' untuk Izin/Sakit
        function aktifkanMode(mode) {
            modeInput = mode;
            const btnPotret = document.getElementById('btnOptPotret');
            const btnFile = document.getElementById('btnOptFile');

            if (mode === 'potret') {
                btnPotret.className = "flex-1 py-1.5 bg-white text-gray-950 font-bold rounded-lg text-xs transition shadow";
                btnFile.className = "flex-1 py-1.5 bg-white/20 hover:bg-white/35 text-white font-semibold rounded-lg text-xs transition";
                fileInputDoc.classList.add('hidden');
                mulaiKamera();
            } else {
                btnFile.className = "flex-1 py-1.5 bg-white text-gray-950 font-bold rounded-lg text-xs transition shadow";
                btnPotret.className = "flex-1 py-1.5 bg-white/20 hover:bg-white/35 text-white font-semibold rounded-lg text-xs transition";
                matikanKamera();
                btnCapture.style.display = 'none';
                fileInputDoc.classList.remove('hidden'); // Buka dialog pilih file
            }
        }

        // Proses Ambil Foto dari Kamera
        function ambilFoto() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            fileDataTerpilih = canvas.toDataURL('image/png');
            
            fotoHasil.src = fileDataTerpilih;
            fotoHasil.classList.remove('hidden');
            matikanKamera();
            btnCapture.style.display = 'none';
        }

        // Proses Unggah File Doc / Gambar
        function unggahFileDoc(event) {
            const file = event.target.files[0];
            if (file) {
                fileDataTerpilih = file;
                namaFileTxt.textContent = file.name;
                fileInfoDisplay.classList.remove('hidden');
                fotoHasil.classList.add('hidden');
                matikanKamera();
                btnCapture.style.display = 'none';
            }
        }

        // Tombol Hapus / Reset Lampiran
        function ulangLampiran() {
            fileDataTerpilih = null;
            fotoHasil.src = '';
            fotoHasil.classList.add('hidden');
            fileInfoDisplay.classList.add('hidden');
            fileInputDoc.value = '';

            if (modeInput === 'potret') {
                mulaiKamera();
            } else {
                btnCapture.style.display = 'none';
            }
        }

        // Tombol Kirim Absen
        function kirimAbsen() {
            if (!fileDataTerpilih) {
                alert("Mohon ambil foto atau unggah dokumen bukti terlebih dahulu!");
                return;
            }
            alert(`✅ Absensi [ ${statusAbsen.toUpperCase()} ] menggunakan metode (${modeInput.toUpperCase()}) berhasil dikirim!`);
        }
    </script>

</body>
</html>