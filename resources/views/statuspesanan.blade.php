<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan - Cafe Exito</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.08); }
        }
        .animate-pulse-slow {
            animation: pulse-slow 2s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#fdfbf7] via-[#f4ebd0] to-[#e6d5bc] min-h-screen flex items-center justify-center p-4">

    <!-- Kontainer Kartu Status Pesanan -->
    <div class="w-full max-w-sm bg-white/95 backdrop-blur-md min-h-[680px] rounded-3xl shadow-2xl overflow-hidden border border-amber-100 flex flex-col justify-between p-6 relative">
        
        <!-- Ornamen Hiasan Background Abstrak -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-200/40 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-orange-100/50 rounded-full blur-2xl pointer-events-none"></div>

        <!-- BAGIAN ATAS: INFORMASI NOMOR PESANAN -->
        <div class="z-10">
            <h1 class="text-lg font-bold text-gray-900 mb-4">
                Status Pesanan
            </h1>
            
            <!-- Kartu Detail Pesanan -->
            <div class="bg-amber-50/80 border border-amber-200/60 rounded-2xl p-4 mb-8 shadow-inner flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider block">Nomor Pesanan</span>
                    <span class="text-xl font-bold text-gray-900 tracking-tight">#010</span>
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider block">Lokasi Meja</span>
                    <span class="text-xs font-bold text-gray-700 bg-amber-200/60 px-2.5 py-1 rounded-lg">Meja 1 • 1 item</span>
                </div>
            </div>

            <!-- TIMELINE / INDIKATOR STATUS PROGRESS -->
            <div class="space-y-6 relative pl-3">
                <!-- Garis Vertikal Penghubung -->
                <div class="absolute left-[19px] top-[12px] bottom-[12px] w-0.5 bg-amber-200 z-0"></div>

                <!-- Status 1: Pesanan Diterima (Aktif - Hijau dengan Efek Gelombang) -->
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center shadow-md shadow-emerald-500/30 animate-pulse-slow shrink-0 text-white text-xs font-bold">✓</div>
                    <div>
                        <span class="text-sm font-bold text-gray-900 block">Pesanan Diterima</span>
                        <span class="text-[11px] text-gray-500">Pesanan telah masuk ke sistem</span>
                    </div>
                </div>

                <!-- Status 2: Sedang Diproses (Abu-abu / Menunggu) -->
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-7 h-7 rounded-full bg-gray-200 border-2 border-gray-300 flex items-center justify-center shrink-0 text-gray-500 text-xs font-semibold">2</div>
                    <div>
                        <span class="text-sm font-semibold text-gray-600 block">Sedang Diproses</span>
                        <span class="text-[11px] text-gray-400">Barista sedang menyiapkan pesanan</span>
                    </div>
                </div>

                <!-- Status 3: Selesai (Abu-abu / Menunggu) -->
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-7 h-7 rounded-full bg-gray-200 border-2 border-gray-300 flex items-center justify-center shrink-0 text-gray-500 text-xs font-semibold">3</div>
                    <div>
                        <span class="text-sm font-semibold text-gray-600 block">Selesai</span>
                        <span class="text-[11px] text-gray-400">Pesanan siap disajikan ke meja</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BAGIAN BAWAH: KOTAK PESAN PENUTUP -->
        <div class="z-10 pt-4">
            <div class="w-full py-4 px-6 bg-gradient-to-r from-[#6b5344] to-[#584437] text-white rounded-2xl shadow-lg text-center border border-amber-900/20">
                <p class="text-xs font-bold leading-relaxed tracking-wide">
                    ☕ Selamat Menikmati<br>
                    <span class="font-normal text-amber-100 text-[11px]">Terimakasih Ditunggu Pesanan Berikutnya</span>
                </p>
            </div>
        </div>

    </div>

</body>
</html>