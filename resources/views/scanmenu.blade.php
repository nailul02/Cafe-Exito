<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan untuk Memesan - Cafe Exito</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <!-- Kartu QR Code Pemesanan -->
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        
        <!-- Bagian Atas: Logo, Label QRIS, dan QR Code -->
        <div class="p-6 flex flex-col items-center justify-center bg-white text-center">
            
            <!-- Logo Cafe Exito -->
            <div class="mb-4">
                <h1 class="text-xl font-serif tracking-widest text-gray-800 italic font-bold">exito</h1>
            </div>

            <!-- Logo / Label Metode (Opsional kecil di atas QR) -->
            <div class="mb-4 px-3 py-1 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                <span class="text-xs font-bold tracking-wider text-gray-700">QRIS / ORDER</span>
            </div>

            <!-- Gambar QR Code -->
            <div class="w-56 h-56 bg-white p-2 border border-gray-100 flex items-center justify-center">
                <!-- Ganti link src dengan QR Code asli kafe Anda -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=https://cafeexito.id/menu" alt="QR Code Pemesanan Cafe Exito" class="w-full h-full object-contain">
            </div>

        </div>

        <!-- Bagian Bawah: Teks Ajakan Scan -->
        <div class="bg-white border-t border-gray-100 py-5 px-6 text-center">
            <h2 class="text-sm font-bold tracking-widest text-gray-900 uppercase">
                SCAN UNTUK MEMESAN
            </h2>
        </div>

    </div>

</body>
</html>