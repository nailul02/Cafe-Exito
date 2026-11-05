<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Kasir - Exito Cafe</title>
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

        <!-- Menu Navigasi -->
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
            <h1 class="text-xl font-semibold text-gray-800">Pesanan</h1>
            
            <!-- Profil User -->
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-800">ucup</p>
                    <p class="text-xs text-gray-500">Kasir</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-300"></div>
            </div>
        </header>

        <!-- KONTEN UTAMA PESANAN -->
        <div class="bg-[#6b5344] text-white p-6 rounded-xl shadow-sm">
            <h2 class="text-lg font-semibold mb-6 text-center">Semua Pesanan</h2>
            
           <!-- Filter Tombol dengan Jarak (Padding Kiri & Kanan) yang Rapi -->
<div class="flex justify-center mb-6">
    <div class="flex gap-2 text-xs">
        <button onclick="filterPesanan('semua', this)" class="px-4 py-1.5 bg-amber-400 text-gray-900 rounded-full font-bold transition shadow-sm filter-btn">Semua</button>
        <button onclick="filterPesanan('menunggu', this)" class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-full font-medium transition shadow-sm filter-btn">Menunggu Bayar</button>
        <button onclick="filterPesanan('dibayar', this)" class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-full font-medium transition shadow-sm filter-btn">Sudah Bayar</button>
        <button onclick="filterPesanan('selesai', this)" class="px-4 py-1.5 bg-gray-300 text-gray-700 rounded-full font-medium transition shadow-sm filter-btn">Selesai</button>
    </div>
</div>

            <!-- TABEL PESANAN -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-white/20 text-gray-200">
                            <th class="py-3 px-4 font-semibold">No</th>
                            <th class="py-3 px-4 font-semibold">Meja</th>
                            <th class="py-3 px-4 font-semibold">Total</th>
                            <th class="py-3 px-4 font-semibold">Pembayaran</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                            <th class="py-3 px-4 font-semibold text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10" id="tabelPesanan">
                        
                        <!-- Baris 1: Menunggu -->
                        <tr class="pesanan-row" data-status="menunggu">
                            <td class="py-3.5 px-4 font-bold">#001</td>
                            <td class="py-3.5 px-4">05</td>
                            <td class="py-3.5 px-4 font-semibold">45.000</td>
                            <td class="py-3.5 px-4">QRIS</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-amber-500/30 text-amber-300 font-bold rounded">Menunggu</span></td>
                            <td class="py-3.5 px-4 text-right"><a href="#" class="text-amber-300 underline hover:text-white">Detail</a></td>
                        </tr>

                        <!-- Baris 2: Sudah Bayar / Dibayar -->
                        <tr class="pesanan-row" data-status="dibayar">
                            <td class="py-3.5 px-4 font-bold">#002</td>
                            <td class="py-3.5 px-4">02</td>
                            <td class="py-3.5 px-4 font-semibold">60.000</td>
                            <td class="py-3.5 px-4">Cash</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-blue-500/30 text-blue-300 font-bold rounded">Dibayar</span></td>
                            <td class="py-3.5 px-4 text-right"><a href="#" class="text-amber-300 underline hover:text-white">Detail</a></td>
                        </tr>

                        <!-- Baris 3: Sudah Bayar / Dibayar -->
                        <tr class="pesanan-row" data-status="dibayar">
                            <td class="py-3.5 px-4 font-bold">#003</td>
                            <td class="py-3.5 px-4">07</td>
                            <td class="py-3.5 px-4 font-semibold">35.000</td>
                            <td class="py-3.5 px-4">QRIS</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-blue-500/30 text-blue-300 font-bold rounded">Dibayar</span></td>
                            <td class="py-3.5 px-4 text-right"><a href="#" class="text-amber-300 underline hover:text-white">Detail</a></td>
                        </tr>

                        <!-- Baris 4: Selesai -->
                        <tr class="pesanan-row" data-status="selesai">
                            <td class="py-3.5 px-4 font-bold">#004</td>
                            <td class="py-3.5 px-4">01</td>
                            <td class="py-3.5 px-4 font-semibold">50.000</td>
                            <td class="py-3.5 px-4">QRIS</td>
                            <td class="py-3.5 px-4"><span class="px-2.5 py-1 bg-emerald-500/30 text-emerald-300 font-bold rounded">Selesai</span></td>
                            <td class="py-3.5 px-4 text-right"><a href="#" class="text-amber-300 underline hover:text-white">Detail</a></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- SCRIPT FILTER INTERAKTIF -->
    <script>
        function filterPesanan(status, button) {
            // Ubah warna tombol aktif
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => {
                btn.className = "px-4 py-1.5 bg-gray-300 text-gray-700 rounded-full font-medium transition shadow-sm filter-btn";
            });
            button.className = "px-4 py-1.5 bg-amber-400 text-gray-900 rounded-full font-bold transition shadow-sm filter-btn";

            // Saring baris tabel sesuai status yang dipilih
            const rows = document.querySelectorAll('.pesanan-row');
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (status === 'semua' || rowStatus === status) {
                    row.style.display = ""; // Tampilkan
                } else {
                    row.style.display = "none"; // Sembunyikan
                }
            });
        }
    </script>

</body>
</html>