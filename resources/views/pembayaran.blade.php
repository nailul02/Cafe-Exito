<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode Pembayaran - Cafe Exito</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#fdfbf7] via-[#f4ebd0] to-[#e6d5bc] min-h-screen flex items-center justify-center p-4">

    <!-- Kontainer Kartu Pemilihan Metode Pembayaran -->
    <div class="w-full max-w-sm bg-white/95 backdrop-blur-md min-h-[650px] rounded-3xl shadow-2xl overflow-hidden border border-amber-100 flex flex-col justify-between p-6 relative">
        
        <!-- Ornamen Latar Belakang -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-200/40 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-orange-100/50 rounded-full blur-2xl pointer-events-none"></div>

        <!-- BAGIAN ATAS: JUDUL & RINGKASAN TAGIHAN -->
        <div class="z-10">
            <!-- Judul Halaman -->
            <h1 class="text-lg font-bold text-gray-900 mb-1">
                Metode Pembayaran
            </h1>
            <p class="text-xs text-gray-500 mb-6">Silakan pilih cara pembayaran yang Anda inginkan.</p>

            <!-- Ringkasan Total Tagihan -->
            <div class="bg-amber-50/80 border border-amber-200/60 rounded-2xl p-4 mb-6 flex items-center justify-between shadow-inner">
                <div>
                    <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider block">Total Tagihan</span>
                    <span class="text-xs text-gray-600">Meja 1 • 1 Item</span>
                </div>
                <span class="text-base font-bold text-amber-900">Rp 12.000</span>
            </div>

            <!-- Pilihan Metode (QRIS & Tunai) dengan Kartu Interaktif -->
            <div class="space-y-3">
                <!-- Tombol QRIS -->
                <button type="button" onclick="pilihQRIS()" id="btnQRIS" class="w-full p-4 bg-white hover:bg-amber-50/60 text-gray-800 font-semibold rounded-2xl shadow-sm border-2 border-amber-200/80 transition-all duration-200 cursor-pointer flex items-center justify-between group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-sm shadow-sm group-hover:bg-[#6b5344] group-hover:text-white transition">
                            📱
                        </div>
                        <div class="text-left">
                            <h3 class="font-bold text-sm text-gray-900">QRIS</h3>
                            <p class="text-[11px] text-gray-500">Scan & bayar via e-Wallet / M-Banking</p>
                        </div>
                    </div>
                    <span class="text-amber-700 font-bold">➔</span>
                </button>

                <!-- Tombol Tunai -->
                <button type="button" onclick="pilihTunai('Tunai', this)" id="btnTunai" class="w-full p-4 bg-white hover:bg-amber-50/60 text-gray-800 font-semibold rounded-2xl shadow-sm border-2 border-gray-200/80 transition-all duration-200 cursor-pointer flex items-center justify-between group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center font-bold text-sm shadow-sm group-hover:bg-[#6b5344] group-hover:text-white transition">
                            💵
                        </div>
                        <div class="text-left">
                            <h3 class="font-bold text-sm text-gray-900">Tunai (Cash)</h3>
                            <p class="text-[11px] text-gray-500">Bayar langsung di kasir</p>
                        </div>
                    </div>
                    <span class="text-gray-400 font-bold group-hover:text-amber-700">➔</span>
                </button>
            </div>
        </div>

        <!-- BAGIAN BAWAH: TOMBOL PESAN SEKARANG -->
        <div class="z-10 pt-4">
            <button type="button" onclick="prosesPesanSekarang()" class="w-full py-4 bg-[#6b5344] hover:bg-[#584437] text-white font-bold text-xs rounded-2xl shadow-lg transition-all duration-200 cursor-pointer flex items-center justify-between px-6">
                <span>Pesan Sekarang</span>
                <span class="bg-black/20 px-3 py-1 rounded-lg">Rp 12.000</span>
            </button>
        </div>

    </div>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        let metodeTerpilih = 'QRIS';

        function pilihQRIS() {
            metodeTerpilih = 'QRIS';
            document.getElementById('btnQRIS').className = "w-full p-4 bg-amber-50/80 text-gray-800 font-semibold rounded-2xl shadow-md border-2 border-[#6b5344] transition-all duration-200 cursor-pointer flex items-center justify-between";
            document.getElementById('btnTunai').className = "w-full p-4 bg-white text-gray-800 font-semibold rounded-2xl shadow-sm border-2 border-gray-200/80 transition-all duration-200 cursor-pointer flex items-center justify-between";
            
            alert("📲 Mengalihkan ke tampilan scan menu / QRIS...");
            window.location.href = "{{ route('scan-menu') }}";
        }

        function pilihTunai(metode, element) {
            metodeTerpilih = metode;
            
            document.getElementById('btnTunai').className = "w-full p-4 bg-amber-50/80 text-gray-800 font-semibold rounded-2xl shadow-md border-2 border-[#6b5344] transition-all duration-200 cursor-pointer flex items-center justify-between";
            document.getElementById('btnQRIS').className = "w-full p-4 bg-white text-gray-800 font-semibold rounded-2xl shadow-sm border-2 border-gray-200/80 transition-all duration-200 cursor-pointer flex items-center justify-between";

            alert(`Metode pembayaran dipilih: ${metode}`);
        }

        function prosesPesanSekarang() {
            alert(`✅ Pesanan berhasil dibuat menggunakan metode pembayaran: ${metodeTerpilih} (Total: Rp 12.000). Terima kasih telah memesan di Cafe Exito!`);
            window.location.href = "{{ route('pesanan-diterima') }}";
        }
    </script>

</body>
</html>