<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - Exito Cafe</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Pengaturan khusus saat tombol cetak ditekan agar hanya area struk yang tercetak */
        @media print {
            body * {
                visibility: hidden;
            }
            #areaStruk, #areaStruk * {
                visibility: visible;
            }
            #areaStruk {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background-color: white !important;
                color: black !important;
                border: none !important;
                box-shadow: none !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- SIDEBAR (Disembunyikan saat cetak) -->
    <aside class="w-64 bg-[#6b5344] text-white flex flex-col p-6 min-h-screen no-print">
        <!-- Logo & Nama Cafe -->
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-800 font-serif italic font-bold shadow-sm">
                exito
            </div>
            <span class="text-lg font-semibold tracking-wide">cafe exito</span>
        </div>

        <!-- Menu Navigasi -->
        <nav class="flex flex-col gap-4 text-sm">
            <a href="{{ route('dashboard') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Dashboard</a>
            <a href="{{ route('pesanan-kasir') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">pesenan</a>
            <a href="{{ route('detail-transaksi') }}" class="py-2 px-4 rounded-md bg-[#584437] font-medium text-white">Detail Transaksi</a>
            <a href="{{ route('absensi-kasir') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Absensi</a>
            <a href="{{ route('profil-kasir') }}" class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Profil</a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-8">
        
        <!-- HEADER ATAS (Disembunyikan saat cetak) -->
        <header class="flex justify-between items-center mb-8 no-print">
            <h1 class="text-xl font-semibold text-gray-800">Detail Transaksi</h1>
            
            <!-- Profil User -->
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-800">ucup</p>
                    <p class="text-xs text-gray-500">Kasir</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-300"></div>
            </div>
        </header>

        <!-- KARTU DETAIL TRANSAKSI YANG AKAN DICETAK -->
        <div id="areaStruk" class="bg-[#6b5344] text-white p-8 rounded-2xl shadow-md max-w-lg mx-auto">
            
            <h2 class="text-lg font-bold text-center mb-1">Detail Transaksi</h2>
            <p class="text-xs text-center text-gray-200 mb-6">Cafe Exito</p>

            <!-- Informasi Transaksi -->
            <div class="space-y-2 text-xs mb-6 border-b border-white/20 pb-4">
                <div class="flex justify-between">
                    <span class="text-gray-200">No Transaksi</span>
                    <span class="font-semibold">: TRX001</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-200">No Pesanan</span>
                    <span class="font-semibold">: #EX001</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-200">Meja</span>
                    <span class="font-semibold">: 05</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-200">Tanggal</span>
                    <span class="font-semibold">: 02 Oktober 2026</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-200">Kasir</span>
                    <span class="font-semibold">: Andi</span>
                </div>
            </div>

            <!-- Daftar Item Pesanan -->
            <div class="space-y-3 text-xs mb-6 border-b border-white/20 pb-4">
                <div class="flex justify-between">
                    <span>Nasi Goreng × 2</span>
                    <span class="font-semibold">Rp40.000</span>
                </div>
                <div class="flex justify-between">
                    <span>Es Teh × 2</span>
                    <span class="font-semibold">Rp16.000</span>
                </div>
            </div>

            <!-- Total & Pembayaran -->
            <div class="flex justify-between items-center text-sm font-bold mb-6">
                <span>Total</span>
                <span class="text-base text-amber-300">Rp56.000</span>
            </div>
            <div class="flex justify-between items-center text-xs mb-8 text-gray-200 border-b border-white/20 pb-4">
                <span>Pembayaran</span>
                <span class="font-bold text-white">QRIS</span>
            </div>

            <!-- Tombol Cetak Struk (Disembunyikan saat proses cetak berjalan) -->
            <div class="text-center no-print">
                <button type="button" onclick="cetakStruk()" class="w-full py-2.5 bg-white/20 hover:bg-white hover:text-gray-900 font-bold rounded-xl text-xs transition shadow border border-white/30">
                    [ CETAK STRUK ]
                </button>
            </div>

        </div>

    </main>

    <!-- SCRIPT JAVASCRIPT UNTUK CETAK -->
    <script>
        function cetakStruk() {
            window.print();
        }
    </script>

</body>
</html>