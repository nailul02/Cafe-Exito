<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Barista - Exito Cafe</title>
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
            <a href="{{ route('pesanan-barista') }}" class="py-2.5 px-4 rounded-md bg-[#584437] text-white shadow-sm">pesenan</a>
            <a href="{{ route('absensi-barista') }}" class="py-2.5 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Absensi</a>
            <a href="{{ route('profil-barista') }}" class="py-2.5 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Profil</a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-10 flex flex-col">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Pesanan</h1>
            
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

        <!-- KARTU UTAMA ANTREAN MINUMAN (PANJANG KE BAWAH) -->
        <div class="bg-[#6b5344] text-white p-8 rounded-2xl shadow-xl flex-1 flex flex-col">
            
            <!-- Judul & Deskripsi Kartu -->
            <div class="text-center mb-6">
                <h2 class="text-base font-bold tracking-wide">Antrean Minuman</h2>
                <p class="text-xs text-gray-200 mt-0.5">Kelola pesanan minuman yang masuk dari pelanggan</p>
            </div>

            <!-- Tab Filter Status di Dalam Kotak dengan Kurung Kurawal {} -->
            <div class="bg-white/10 border border-white/20 p-2.5 rounded-xl flex justify-center gap-6 text-xs font-semibold mb-6 text-amber-200 shadow-inner">
                <button type="button" onclick="filterPesanan('semua')" class="filter-btn hover:text-white transition">Semua</button>
                <button type="button" onclick="filterPesanan('menunggu')" class="filter-btn hover:text-white transition">Menunggu</button>
                <button type="button" onclick="filterPesanan('diproses')" class="filter-btn hover:text-white transition">Diproses</button>
                <button type="button" onclick="filterPesanan('selesai')" class="filter-btn hover:text-white transition">Selesai</button>
            </div>

            <hr class="border-white/20 mb-6">

            <!-- DAFTAR PESANAN -->
            <div id="daftarPesananContainer" class="space-y-6 text-sm flex-1">
                
                <!-- Pesanan 1: Menunggu -->
                <div class="item-pesanan menunggu pb-6 border-b border-white/20" data-status="menunggu">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-bold text-white">#EX001</p>
                            <p class="text-xs text-gray-200">Meja 05</p>
                        </div>
                        <div class="badge-status flex items-center gap-1.5 text-xs font-semibold bg-white/10 px-3 py-1 rounded-full">
                            <span class="dot-status w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                            <span class="text-status-text text-red-200">Menunggu</span>
                        </div>
                    </div>
                    <div class="space-y-1 text-xs font-medium text-gray-100 my-3 pl-2">
                        <p>☕ Es Kopi Susu × 2</p>
                        <p>☕ Cappuccino × 1</p>
                    </div>
                    <div class="flex justify-between items-center text-xs text-gray-300">
                        <span>16.25</span>
                        <div class="action-container">
                            <button onclick="ubahStatusPesanan(this, 'diproses')" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold rounded-lg transition shadow">
                                Proses Pesanan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pesanan 2: Diproses -->
                <div class="item-pesanan diproses" data-status="diproses">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-bold text-white">#EX002</p>
                            <p class="text-xs text-gray-200">Meja 02</p>
                        </div>
                        <div class="badge-status flex items-center gap-1.5 text-xs font-semibold bg-white/10 px-3 py-1 rounded-full">
                            <span class="dot-status w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="text-status-text text-amber-200">Diproses</span>
                        </div>
                    </div>
                    <div class="space-y-1 text-xs font-medium text-gray-100 my-3 pl-2">
                        <p>☕ Americano × 1</p>
                        <p>🧋 Matcha Latte × 2</p>
                    </div>
                    <div class="flex justify-between items-center text-xs text-gray-300">
                        <span>16.28</span>
                        <div class="action-container">
                            <button onclick="ubahStatusPesanan(this, 'selesai')" class="px-3 py-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-lg transition shadow">
                                Selesaikan
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- SCRIPT INTERAKTIF FILTER & STATUS -->
    <script>
        function filterPesanan(status) {
            const items = document.querySelectorAll('.item-pesanan');
            items.forEach(item => {
                if (status === 'semua') {
                    item.style.display = 'block';
                } else {
                    if (item.getAttribute('data-status') === status) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        }

        function ubahStatusPesanan(button, statusBaru) {
            const card = button.closest('.item-pesanan');
            const dotStatus = card.querySelector('.dot-status');
            const textStatus = card.querySelector('.text-status-text');
            const actionContainer = card.querySelector('.action-container');

            if (statusBaru === 'diproses') {
                card.setAttribute('data-status', 'diproses');
                card.classList.remove('menunggu');
                card.classList.add('diproses');

                dotStatus.className = "dot-status w-2.5 h-2.5 rounded-full bg-amber-400";
                textStatus.className = "text-status-text text-amber-200";
                textStatus.textContent = "Diproses";

                actionContainer.innerHTML = `
                    <button onclick="ubahStatusPesanan(this, 'selesai')" class="px-3 py-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-lg transition shadow">
                        Selesaikan
                    </button>
                `;
            } else if (statusBaru === 'selesai') {
                card.setAttribute('data-status', 'selesai');
                card.classList.remove('diproses');
                card.classList.add('selesai');

                dotStatus.className = "dot-status w-2.5 h-2.5 rounded-full bg-emerald-400";
                textStatus.className = "text-status-text text-emerald-200";
                textStatus.textContent = "Selesai";

                actionContainer.innerHTML = `
                    <span class="text-xs text-emerald-300 font-semibold">✔ Selesai</span>
                `;
            }
        }
    </script>

</body>
</html>