<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - Cafe Exito</title>
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

    <!-- Kontainer Kartu Selamat Datang -->
    <div class="w-full max-w-sm bg-white/95 backdrop-blur-md min-h-[680px] rounded-3xl shadow-2xl overflow-hidden border border-amber-100 flex flex-col justify-between p-8 text-center relative">
        
        <!-- Ornamen Hiasan Background Abstrak -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-200/40 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-orange-100/50 rounded-full blur-2xl pointer-events-none"></div>

        <!-- BAGIAN ATAS: LOGO / IKON KAFE -->
        <div class="z-10 pt-6">
            <div class="w-20 h-20 bg-gradient-to-tr from-amber-700 to-[#6b5344] text-white rounded-3xl flex items-center justify-center mx-auto mb-6 text-3xl shadow-lg shadow-amber-900/20">
                ☕
            </div>
            <span class="text-xs font-bold text-amber-800 uppercase tracking-widest bg-amber-100/80 px-3 py-1 rounded-full">
                Eksklusif Kafe
            </span>
        </div>

        <!-- BAGIAN TENGAH: SAMBUTAN UTAMA -->
        <div class="z-10 my-auto">
            <h1 class="text-2xl font-bold text-gray-900 mb-3 tracking-tight">
                Selamat Datang di<br>
                <span class="text-amber-900">Cafe Exito</span>
            </h1>
            <p class="text-xs text-gray-500 max-w-[240px] mx-auto leading-relaxed">
                Nikmati berbagai pilihan kopi terbaik dan hidangan lezat langsung dari meja Anda.
            </p>
        </div>

        <!-- BAGIAN BAWAH: TOMBOL MULAI / LANJUT -->
        <div class="z-10 pb-6">
            <button type="button" onclick="mulaiPesan()" class="w-full py-4 bg-[#6b5344] hover:bg-[#584437] text-white font-bold text-xs rounded-2xl shadow-lg transition-all duration-200 cursor-pointer tracking-wide flex items-center justify-center gap-2">
                <span>Mulai Pesan Sekarang</span>
                <span>➔</span>
            </button>
            <p class="text-[10px] text-gray-400 mt-4">Powered by Exito System</p>
        </div>

    </div>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        function mulaiPesan() {
            // Diarahkan langsung ke halaman katalog menu, bukan ke scan QRIS
            window.location.href = "{{ route('katalog-menu') }}";
        }
    </script>

</body>
</html>