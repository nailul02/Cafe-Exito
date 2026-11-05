<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Barista - Exito Cafe</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex">

    <!-- SIDEBAR BARISTA -->
    <aside class="w-64 bg-[#6b5344] text-white flex flex-col p-6 min-h-screen shadow-md">
        <!-- Logo & Nama Cafe -->
        <div class="flex items-center gap-3 mb-12">
            <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-gray-800 font-serif italic font-bold shadow-sm">
                exito
            </div>
            <span class="text-xl font-semibold tracking-wide">cafe exito</span>
        </div>

        <!-- Menu Navigasi Barista (Menggunakan Rute Barista) -->
        <nav class="flex flex-col gap-5 text-sm font-medium">
            <a href="{{ route('dashboard-barista') }}" class="py-2.5 px-4 rounded-md bg-[#584437] text-white shadow-sm">Dashboard</a>
            <a href="{{ route('pesanan-barista') }}" class="py-2.5 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">pesenan</a>
            <a href="{{ route('absensi-barista') }}" class="py-2.5 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Absensi</a>
            <a href="{{ route('profil-barista') }}" class="py-2.5 px-4 rounded-md hover:bg-[#584437] text-gray-200 transition">Profil</a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 p-10">
        
        <!-- HEADER ATAS -->
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
            
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

        <!-- KARTU STATISTIK (3 KOTAK KECIL) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-3xl mb-10">
            
            <!-- Kotak 1: Pesanan -->
            <div class="bg-[#6b5344] text-white p-5 rounded-2xl shadow-md flex flex-col justify-between h-28">
                <span class="text-2xl font-bold">30</span>
                <span class="text-xs font-semibold tracking-wide uppercase text-gray-200">Pesanan</span>
            </div>

            <!-- Kotak 2: Diproses -->
            <div class="bg-[#6b5344] text-white p-5 rounded-2xl shadow-md flex flex-col justify-between h-28">
                <span class="text-2xl font-bold">30</span>
                <span class="text-xs font-semibold tracking-wide uppercase text-gray-200">Diproses</span>
            </div>

            <!-- Kotak 3: Selesai -->
            <div class="bg-[#6b5344] text-white p-5 rounded-2xl shadow-md flex flex-col justify-between h-28">
                <span class="text-2xl font-bold">30</span>
                <span class="text-xs font-semibold tracking-wide uppercase text-gray-200">Selesai</span>
            </div>

        </div>

        <!-- DETAIL RINGKASAN PESANAN MASUK -->
        <div class="max-w-xl bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
            <div class="space-y-2 text-gray-800">
                <h2 class="text-base font-bold text-gray-900 border-b pb-3">#EX001 | Meja 05</h2>
                <ul class="py-2 space-y-1 text-sm font-medium text-gray-700">
                    <li>2 × Es Kopi Susu</li>
                    <li>1 × Cappuccino</li>
                </ul>
                <div class="pt-2 border-t flex items-center gap-2 text-sm font-semibold">
                    <span>Status:</span>
                    <span class="inline-flex items-center gap-1.5 text-red-600 bg-red-50 px-2.5 py-0.5 rounded-full text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-red-600"></span> Menunggu
                    </span>
                </div>
            </div>
        </div>

    </main>

</body>
</html>