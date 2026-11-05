<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir - Exito Cafe</title>
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
            <a href="{{ route('dashboard') }}"
               class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">
                Dashboard
            </a>

            <a href="{{ route('pesanan-kasir') }}"
               class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">
                pesenan
            </a>

            <a href="{{ route('detail-transaksi') }}"
               class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">
                Detail Transaksi
            </a>

            <a href="{{ route('absensi-kasir') }}"
               class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">
                Absensi
            </a>

            <a href="{{ route('profil-kasir') }}"
               class="py-2 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">
                Profil
            </a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-8">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-xl font-semibold text-gray-800">Dashboard Kasir</h1>
            
            <!-- Profil User -->
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-800">ucup</p>
                    <p class="text-xs text-gray-500">Kasir</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-300"></div>
            </div>
        </header>

        <!-- KOTAK STATISTIK (4 CARD) -->
        <div class="grid grid-cols-4 gap-4 mb-8">
            <div class="bg-[#6b5344] text-white p-4 rounded-lg shadow-sm">
                <p class="text-2xl font-bold mb-1">30</p>
                <p class="text-xs text-gray-200">Pesanan Baru</p>
            </div>
            <div class="bg-[#6b5344] text-white p-4 rounded-lg shadow-sm">
                <p class="text-2xl font-bold mb-1">30</p>
                <p class="text-xs text-gray-200">Menunggu Pembayaran</p>
            </div>
            <div class="bg-[#6b5344] text-white p-4 rounded-lg shadow-sm">
                <p class="text-2xl font-bold mb-1">30</p>
                <p class="text-xs text-gray-200">Pesanan Hari Ini</p>
            </div>
            <div class="bg-[#6b5344] text-white p-4 rounded-lg shadow-sm">
                <p class="text-2xl font-bold mb-1">30</p>
                <p class="text-xs text-gray-200">Total Pendapatan</p>
            </div>
        </div>

        <!-- BAGIAN BAWAH: DAFTAR PESANAN & VERIFIKASI PEMBAYARAN -->
        <div class="grid grid-cols-12 gap-6">
            
            <!-- DAFTAR PESANAN (KIRI) -->
            <div class="col-span-7 bg-[#6b5344] text-white p-6 rounded-xl shadow-sm">
                <h2 class="text-lg font-semibold mb-4">Daftar Pesanan</h2>
                
                <!-- Filter Tombol -->
                <div class="flex gap-2 mb-6 text-xs">
                    <button class="px-4 py-1.5 bg-gray-200 text-gray-800 rounded-full font-medium">Semua</button>
                    <button class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-full font-medium">Menunggu Bayar</button>
                    <button class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-full font-medium">Sudah Bayar</button>
                </div>

                <!-- Item Card Pesanan -->
                <div class="bg-[#584437] p-4 rounded-lg flex justify-between items-center">
                    <div>
                        <p class="font-bold text-base mb-1">#100</p>
                        <p class="text-xs text-gray-300 mb-2">Meja 4</p>
                        <span class="text-xs bg-black/20 px-2 py-1 rounded">1 item</span>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm mb-1">Rp 12.000</p>
                        <!-- Status Pesanan yang akan berubah dinamis -->
                        <p id="statusPesanan" class="text-xs text-amber-300 font-medium mb-3">Menunggu Bayar</p>
                        <button id="btnVerifList" class="px-4 py-1.5 bg-gray-200 text-gray-800 text-xs font-semibold rounded-md hover:bg-white transition">Verif</button>
                    </div>
                </div>
            </div>

            <!-- VERIFIKASI PEMBAYARAN (KANAN) -->
            <div class="col-span-5 bg-[#6b5344] text-white p-6 rounded-xl shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-semibold mb-4">Verifikasi Pembayaran</h2>
                    <p class="font-bold text-base mb-4">#100</p>

                    <!-- Detail Pesanan -->
                    <div class="flex justify-between items-center text-sm mb-6">
                        <span>kopi latee</span>
                        <span>x1</span>
                        <span>Rp 12.000</span>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between items-center font-bold text-sm mb-6 border-t border-white/20 pt-4">
                        <span>Total</span>
                        <span>Rp 12.000</span>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="mb-6">
                        <p class="text-xs mb-2 text-gray-200">Metode Pembayaran</p>
                        <div class="flex gap-2">
                            <button type="button" onclick="pilihMetode('QRIS')" id="btnQris" class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg shadow-sm hover:bg-white transition">
                                QRIS
                            </button>
                            <button type="button" onclick="pilihMetode('CASH')" id="btnCash" class="px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg shadow-sm hover:bg-white transition">
                                CASH
                            </button>
                        </div>
                        <p id="infoMetode" class="text-xs text-amber-300 mt-2 italic hidden">Metode terpilih: <span id="txtMetode" class="font-bold"></span></p>
                    </div>
                </div>

                <!-- Tombol Aksi Bawah -->
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <button type="button" onclick="aksiPembayaran('tolak')" class="py-2 bg-gray-300 text-gray-800 text-xs font-semibold rounded-lg hover:bg-red-600 hover:text-white transition">
                        Tolak
                    </button>
                    <button type="button" onclick="aksiPembayaran('verifikasi')" class="py-2 bg-gray-300 text-gray-800 text-xs font-semibold rounded-lg hover:bg-emerald-500 hover:text-white transition">
                        Verifikasi
                    </button>
                </div>
            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKSI TOMBOL & SINKRONISASI STATUS -->
    <script>
        let metodeTerpilih = '';

        function pilihMetode(metode) {
            metodeTerpilih = metode;
            const btnQris = document.getElementById('btnQris');
            const btnCash = document.getElementById('btnCash');
            const infoMetode = document.getElementById('infoMetode');
            const txtMetode = document.getElementById('txtMetode');

            if (metode === 'QRIS') {
                btnQris.className = "px-4 py-2 bg-amber-400 text-gray-900 font-bold text-xs rounded-lg shadow-sm border-2 border-white";
                btnCash.className = "px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg shadow-sm hover:bg-white transition";
            } else {
                btnCash.className = "px-4 py-2 bg-amber-400 text-gray-900 font-bold text-xs rounded-lg shadow-sm border-2 border-white";
                btnQris.className = "px-4 py-2 bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg shadow-sm hover:bg-white transition";
            }

            txtMetode.textContent = metode;
            infoMetode.classList.remove('hidden');
        }

        function aksiPembayaran(aksi) {
            const statusPesanan = document.getElementById('statusPesanan');
            const btnVerifList = document.getElementById('btnVerifList');

            if (aksi === 'tolak') {
                alert("⚠️ Pembayaran untuk pesanan #100 DITOLAK.");
                statusPesanan.textContent = "Ditolak";
                statusPesanan.className = "text-xs text-red-400 font-medium mb-3";
            } else if (aksi === 'verifikasi') {
                if (!metodeTerpilih) {
                    alert("Mohon pilih metode pembayaran (QRIS atau CASH) terlebih dahulu!");
                    return;
                }
                
                // Ubah status di daftar pesanan sebelah kiri menjadi Selesai
                statusPesanan.textContent = "Selesai";
                statusPesanan.className = "text-xs text-emerald-400 font-bold mb-3";
                
                // Ubah teks tombol verif di list jadi Selesai atau Nonaktifkan
                btnVerifList.textContent = "Selesai";
                btnVerifList.className = "px-4 py-1.5 bg-emerald-500 text-white text-xs font-semibold rounded-md cursor-default";

                alert(`✅ Pembayaran pesanan #100 sebesar Rp 12.000 via ${metodeTerpilih} berhasil DIVERIFIKASI! Status pesanan kini menjadi Selesai.`);
            }
        }
    </script>

</body>
</html>