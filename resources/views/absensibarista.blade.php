<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Barista - Exito Cafe</title>
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
    <aside class="w-64 bg-[#6b5344] text-white flex flex-col p-6 min-h-screen">
        <!-- Logo & Nama Cafe -->
        <div class="flex items-center gap-3 mb-12">
            <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-gray-800 font-serif italic font-bold shadow-md">
                exito
            </div>
            <span class="text-xl font-semibold tracking-wide">cafe exito</span>
        </div>

        <!-- Menu Navigasi Barista -->
       <nav class="flex flex-col gap-5 text-sm">
    <a href="{{ route('dashboard-barista') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Dashboard</a>
    <a href="{{ route('pesanan-barista') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">pesenan</a>
    
    <!-- PASTIKAN MENGARAH KE absensi-barista -->
    <a href="{{ route('absensi-barista') }}" class="py-2 px-4 rounded-md bg-[#584437] font-medium text-white shadow-sm">Absensi</a>
    
    <a href="{{ route('profil-barista') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Profil</a>
</nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-10">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Absensi Barista</h1>
            
            <!-- Profil User Barista -->
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-900">nana</p>
                    <p class="text-xs text-gray-500 font-medium">Barista</p>
                </div>
                <div class="w-11 h-11 rounded-full bg-gray-300 overflow-hidden flex items-center justify-center font-bold text-gray-600 shadow-sm">
                    👤
                </div>
            </div>
        </header>

        <!-- KONTEN ABSENSI -->
        <div class="max-w-xl">
            
            <!-- Tab Kategori Absensi (Hadir, Izin, Sakit) -->
            <div class="flex gap-8 mb-6 text-base font-semibold text-gray-800">
                <button type="button" onclick="pilihTabAbsensi('hadir', this)" class="tab-btn pb-1 border-b-2 border-gray-900 text-gray-900 transition">Hadir</button>
                <button type="button" onclick="pilihTabAbsensi('izin', this)" class="tab-btn pb-1 border-b-2 border-transparent text-gray-400 hover:text-gray-700 transition">Izin</button>
                <button type="button" onclick="pilihTabAbsensi('sakit', this)" class="tab-btn pb-1 border-b-2 border-transparent text-gray-400 hover:text-gray-700 transition">Sakit</button>
            </div>

            <!-- KARTU KOTAK COKELAT ABSENSI -->
            <div class="bg-[#6b5344] text-white p-6 rounded-2xl shadow-lg w-full max-w-md flex flex-col justify-between" style="min-height: 440px;">
                
                <div>
                    <!-- Keterangan Dinamis Berdasarkan Tab -->
                    <p id="teksKeterangan" class="text-xs font-semibold text-amber-200 mb-3 text-center">📸 Silakan ambil foto selfie untuk konfirmasi Kehadiran.</p>
                </div>

                <!-- Tombol Pilihan Mode Khusus Tab Izin & Sakit (Potret vs Kirim File Doc) -->
                <div id="opsiIzinSakit" class="hidden flex gap-2 mb-3">
                    <button type="button" onclick="aktifkanModeInput('potret')" id="btnOptPotret" class="flex-1 py-1.5 bg-white text-gray-950 font-bold rounded-lg text-xs transition shadow">
                        📷 Potret Kamera
                    </button>
                    <button type="button" onclick="aktifkanModeInput('file')" id="btnOptFile" class="flex-1 py-1.5 bg-white/20 hover:bg-white/35 text-white font-semibold rounded-lg text-xs transition">
                        📁 Kirim File Doc/Gambar
                    </button>
                </div>

                <!-- Area Kotak Pratinjau (Kamera / File) -->
                <div class="bg-[#f3efed] rounded-xl h-56 flex flex-col items-center justify-center border-2 border-dashed border-gray-400 text-gray-500 relative overflow-hidden shadow-inner">
                    
                    <!-- Video Kamera -->
                    <video id="videoElement" autoplay playsinline class="absolute inset-0 w-full h-full object-cover"></video>
                    <canvas id="canvasElement" class="hidden"></canvas>

                    <!-- Pratinjau Foto -->
                    <img id="previewGambar" src="" alt="Pratinjau" class="absolute inset-0 w-full h-full object-cover hidden">

                    <!-- Pratinjau File Doc -->
                    <div id="infoFileContainer" class="absolute inset-0 flex flex-col items-center justify-center bg-[#f3efed] hidden p-4 text-center">
                        <span class="text-3xl mb-1">📄</span>
                        <span id="namaFileTampil" class="text-xs font-bold text-gray-800 break-all"></span>
                    </div>

                    <!-- Tombol Ambil Foto -->
                    <button type="button" id="btnPotretAksi" onclick="ambilFotoKamera()" class="absolute bottom-3 px-4 py-1.5 bg-gray-900/80 hover:bg-gray-900 text-white font-semibold rounded-full text-xs shadow transition z-10">
                        📸 Potret Sekarang
                    </button>

                    <!-- Input File Tersembunyi -->
                    <input type="file" id="inputFileDoc" accept=".pdf,.doc,.docx,image/*" class="absolute inset-0 opacity-0 cursor-pointer hidden" onchange="unggahFileDokumen(event)">
                </div>

                <!-- Tombol Bawah (Hapus & Kirim) -->
                <div class="flex justify-between items-center pt-4">
                    <button type="button" onclick="resetFormAbsen()" class="text-sm font-semibold text-white/90 hover:text-white transition cursor-pointer">
                        hapus
                    </button>
                    <button type="button" onclick="kirimDataAbsen()" class="px-6 py-2 bg-[#584437] hover:bg-black text-white text-sm font-semibold rounded-xl shadow transition border border-white/20 cursor-pointer">
                        Kirim
                    </button>
                </div>

            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF -->
    <script>
        let statusTabAktif = 'hadir';
        let modeInputAktif = 'potret';
        let dataFileTerpilih = null;

        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('canvasElement');
        const previewGambar = document.getElementById('previewGambar');
        const btnPotretAksi = document.getElementById('btnPotretAksi');
        const opsiIzinSakit = document.getElementById('opsiIzinSakit');
        const inputFileDoc = document.getElementById('inputFileDoc');
        const infoFileContainer = document.getElementById('infoFileContainer');
        const namaFileTampil = document.getElementById('namaFileTampil');
        const teksKeterangan = document.getElementById('teksKeterangan');

        window.addEventListener('load', function () {
            mulaiStreamKamera();
        });

        function mulaiStreamKamera() {
            if (modeInputAktif === 'potret') {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false })
                    .then(function (stream) {
                        video.srcObject = stream;
                        video.style.display = 'block';
                        previewGambar.classList.add('hidden');
                        infoFileContainer.classList.add('hidden');
                        btnPotretAksi.style.display = 'block';
                    })
                    .catch(function (error) {
                        console.error("Akses kamera ditolak: ", error);
                    });
            }
        }

        function matikanStreamKamera() {
            if (video.srcObject) {
                let tracks = video.srcObject.getTracks();
                tracks.forEach(track => track.stop());
                video.srcObject = null;
            }
            video.style.display = 'none';
        }

        function pilihTabAbsensi(kategori, element) {
            statusTabAktif = kategori;

            document.querySelectorAll('.tab-btn').forEach(tab => {
                tab.className = "tab-btn pb-1 border-b-2 border-transparent text-gray-400 hover:text-gray-700 transition";
            });
            element.className = "tab-btn pb-1 border-b-2 border-gray-900 text-gray-900 transition";

            if (kategori === 'hadir') {
                opsiIzinSakit.classList.add('hidden');
                modeInputAktif = 'potret';
                inputFileDoc.classList.add('hidden');
                teksKeterangan.textContent = "📸 Silakan ambil foto selfie untuk konfirmasi Kehadiran.";
                mulaiStreamKamera();
            } else if (kategori === 'izin') {
                opsiIzinSakit.classList.remove('hidden');
                teksKeterangan.textContent = "📄 Pilih opsi potret langsung atau unggah dokumen surat izin.";
                aktifkanModeInput('potret');
            } else if (kategori === 'sakit') {
                opsiIzinSakit.classList.remove('hidden');
                teksKeterangan.textContent = "🏥 Pilih opsi potret langsung atau unggah surat keterangan dokter.";
                aktifkanModeInput('potret');
            }
            resetFormAbsen();
        }

        function aktifkanModeInput(mode) {
            modeInputAktif = mode;
            const btnPotret = document.getElementById('btnOptPotret');
            const btnFile = document.getElementById('btnOptFile');

            if (mode === 'potret') {
                btnPotret.className = "flex-1 py-1.5 bg-white text-gray-950 font-bold rounded-lg text-xs transition shadow";
                btnFile.className = "flex-1 py-1.5 bg-white/20 hover:bg-white/35 text-white font-semibold rounded-lg text-xs transition";
                inputFileDoc.classList.add('hidden');
                mulaiStreamKamera();
            } else {
                btnFile.className = "flex-1 py-1.5 bg-white text-gray-950 font-bold rounded-lg text-xs transition shadow";
                btnPotret.className = "flex-1 py-1.5 bg-white/20 hover:bg-white/35 text-white font-semibold rounded-lg text-xs transition";
                matikanStreamKamera();
                btnPotretAksi.style.display = 'none';
                inputFileDoc.classList.remove('hidden');
            }
        }

        function ambilFotoKamera() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            dataFileTerpilih = canvas.toDataURL('image/png');
            
            previewGambar.src = dataFileTerpilih;
            previewGambar.classList.remove('hidden');
            matikanStreamKamera();
            btnPotretAksi.style.display = 'none';
        }

        function unggahFileDokumen(event) {
            const file = event.target.files[0];
            if (file) {
                dataFileTerpilih = file;
                namaFileTampil.textContent = file.name;
                infoFileContainer.classList.remove('hidden');
                previewGambar.classList.add('hidden');
                matikanStreamKamera();
                btnPotretAksi.style.display = 'none';
            }
        }

        function resetFormAbsen() {
            dataFileTerpilih = null;
            previewGambar.src = '';
            previewGambar.classList.add('hidden');
            infoFileContainer.classList.add('hidden');
            inputFileDoc.value = '';

            if (modeInputAktif === 'potret') {
                mulaiStreamKamera();
            } else {
                btnPotretAksi.style.display = 'none';
            }
        }

        function kirimDataAbsen() {
            if (!dataFileTerpilih) {
                alert("Mohon ambil foto atau unggah lampiran bukti terlebih dahulu!");
                return;
            }
            alert(`✅ Absensi Barista [ ${statusTabAktif.toUpperCase()} ] berhasil dikirim!`);
        }
    </script>

</body>
</html>