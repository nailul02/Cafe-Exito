<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Pesanan - Cafe Exito</title>
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

    <!-- Kontainer Kartu Keranjang Pesanan -->
    <div class="w-full max-w-sm bg-white/95 backdrop-blur-md min-h-[700px] rounded-3xl shadow-2xl overflow-hidden border border-amber-100 flex flex-col justify-between p-6 relative">
        
        <!-- Ornamen Latar Belakang Abstrak -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-200/40 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-orange-100/50 rounded-full blur-2xl pointer-events-none"></div>

        <!-- BAGIAN ATAS: JUDUL & DAFTAR ITEM -->
        <div class="z-10">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-base font-bold text-gray-900">Keranjang Pesanan</h1>
                <span class="text-xs font-semibold text-amber-800 bg-amber-100/80 px-3 py-1 rounded-full">Meja 1</span>
            </div>

            <!-- Daftar Item Di Dalam Keranjang -->
            <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                
                <!-- Item Produk 1 -->
                <div class="bg-amber-50/60 border border-amber-200/60 rounded-2xl p-3 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-amber-200/80 flex items-center justify-center text-xl shadow-inner shrink-0">☕</div>
                            <div>
                                <h3 class="font-bold text-xs text-gray-900">Kopi Susu</h3>
                                <p class="text-xs text-amber-900 font-semibold mt-0.5">Rp 12.000</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-amber-200/40">
                        <div class="flex items-center gap-2 bg-white px-2.5 py-1 rounded-xl shadow-inner border border-amber-100">
                            <button onclick="kurangQty(1)" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">-</button>
                            <span id="qty-1" class="text-xs font-bold text-gray-800 w-4 text-center">1</span>
                            <button onclick="tambahQty(1)" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">+</button>
                        </div>
                        <span id="subtotal-1" class="text-xs font-bold text-gray-900">Rp 12.000</span>
                    </div>
                </div>

                <!-- Item Produk 2 -->
                <div class="bg-amber-50/60 border border-amber-200/60 rounded-2xl p-3 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-amber-200/80 flex items-center justify-center text-xl shadow-inner shrink-0">☕</div>
                            <div>
                                <h3 class="font-bold text-xs text-gray-900">Kopi Susu</h3>
                                <p class="text-xs text-amber-900 font-semibold mt-0.5">Rp 12.000</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-amber-200/40">
                        <div class="flex items-center gap-2 bg-white px-2.5 py-1 rounded-xl shadow-inner border border-amber-100">
                            <button onclick="kurangQty(2)" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">-</button>
                            <span id="qty-2" class="text-xs font-bold text-gray-800 w-4 text-center">1</span>
                            <button onclick="tambahQty(2)" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">+</button>
                        </div>
                        <span id="subtotal-2" class="text-xs font-bold text-gray-900">Rp 12.000</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- BAGIAN BAWAH: RINGKASAN TOTAL & TOMBOL PEMBAYARAN -->
        <div class="z-10 pt-4 border-t border-amber-100">
            <div class="bg-amber-50/80 border border-amber-200/60 rounded-2xl p-3 mb-3 flex items-center justify-between shadow-inner">
                <span class="text-xs font-semibold text-gray-600">Total Pembayaran</span>
                <span id="totalHarga" class="text-sm font-bold text-amber-900">Rp 24.000</span>
            </div>

            <button type="button" onclick="lanjutPembayaran()" class="w-full py-4 bg-[#6b5344] hover:bg-[#584437] text-white font-bold text-xs rounded-2xl shadow-lg transition-all duration-200 cursor-pointer flex items-center justify-between px-6">
                <span>Pilih Metode Pembayaran</span>
                <span>➔</span>
            </button>
        </div>

    </div>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        let items = {
            1: { qty: 1, harga: 12000 },
            2: { qty: 1, harga: 12000 }
        };

        function tambahQty(id) {
            items[id].qty++;
            updateTampilan(id);
        }

        function kurangQty(id) {
            if (items[id].qty > 1) {
                items[id].qty--;
                updateTampilan(id);
            }
        }

        function updateTampilan(id) {
            document.getElementById(`qty-${id}`).innerText = items[id].qty;
            let subtotal = items[id].qty * items[id].harga;
            document.getElementById(`subtotal-${id}`).innerText = "Rp " + subtotal.toLocaleString('id-ID');
            
            hitungTotalKeseluruhan();
        }

        function hitungTotalKeseluruhan() {
            let total = 0;
            for (let id in items) {
                total += items[id].qty * items[id].harga;
            }
            document.getElementById('totalHarga').innerText = "Rp " + total.toLocaleString('id-ID');
        }

        function lanjutPembayaran() {
            alert("💳 Mengarahkan ke halaman metode pembayaran...");
            window.location.href = "{{ route('pembayaran') }}";
        }
    </script>

</body>
</html>