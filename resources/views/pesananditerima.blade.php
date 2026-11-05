<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Diterima - Cafe Exito</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .animate-bounce-slow {
            animation: bounce-slow 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#fdfbf7] via-[#f4ebd0] to-[#e6d5bc] min-h-screen flex items-center justify-center p-4">

    <!-- Kontainer Kartu Pesanan Diterima -->
    <div class="w-full max-w-sm bg-white/90 backdrop-blur-md min-h-[650px] rounded-3xl shadow-2xl overflow-hidden border border-amber-100 flex flex-col justify-between p-8 text-center relative">
        
        <!-- Ornamen Hiasan Abstrak di Background Atas -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-200/40 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-orange-100/50 rounded-full blur-2xl pointer-events-none"></div>

        <!-- BAGIAN ATAS & TENGAH: INFORMASI SUKSES -->
        <div class="my-auto z-10">
            <!-- Ikon Centang Animasi dengan Lingkungan Bergradasi Hangat -->
            <div class="w-20 h-20 bg-gradient-to-tr from-amber-600 to-[#6b5344] text-white rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold shadow-lg shadow-amber-900/20 animate-bounce-slow">
                ✓
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-1 tracking-tight">
                Pesanan Diterima!
            </h1>
            <p class="text-xs text-gray-500 mb-6">
                Terima kasih, pesanan Anda sedang disiapkan oleh barista kami.
            </p>

            <!-- Kartu Rincian Meja / Token -->
            <div class="bg-amber-50/70 border border-amber-200/60 rounded-2xl p-4 max-w-[220px] mx-auto shadow-inner">
                <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider block mb-1">Nomor Meja & Pesanan</span>
                <p class="text-lg font-bold text-gray-900 tracking-wide">Meja 1 <span class="text-amber-700">•</span> #010</p>
            </div>
        </div>

        <!-- BAGIAN BAWAH: TOMBOL AKSI -->
        <div class="space-y-3 z-10 pt-4">
            <!-- Tombol Lihat Status Pesanan -->
            <button type="button" onclick="lihatStatusPesanan()" class="w-full py-3.5 bg-[#6b5344] hover:bg-[#584437] text-white font-bold text-xs rounded-2xl shadow-md transition-all duration-200 cursor-pointer tracking-wide flex items-center justify-center gap-2">
                <span>Lihat Status Pesanan</span>
                <span>➔</span>
            </button>

            <!-- Tombol Pesan Lagi -->
            <button type="button" onclick="pesanLagi()" class="w-full py-3.5 bg-white hover:bg-amber-50 text-[#6b5344] border border-[#6b5344]/30 font-bold text-xs rounded-2xl shadow-sm transition-all duration-200 cursor-pointer tracking-wide">
                Pesan Lagi
            </button>
        </div>

    </div>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        function lihatStatusPesanan() {
            alert("📦 Membuka halaman status progres pesanan Anda...");
            window.location.href = "{{ route('status-pesanan') }}";
        }

        function pesanLagi() {
            alert("🔄 Mengarahkan kembali ke katalog menu...");
            window.location.href = "{{ route('katalog-menu') }}";
        }
    </script>

</body>
</html>