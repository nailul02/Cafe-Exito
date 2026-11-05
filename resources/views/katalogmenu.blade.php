<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Menu - Cafe Exito</title>
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

    <!-- Kontainer Kartu Katalog Menu -->
    <div class="w-full max-w-sm bg-white/95 backdrop-blur-md min-h-[700px] rounded-3xl shadow-2xl overflow-hidden border border-amber-100 flex flex-col justify-between p-6 relative">
        
        <!-- Ornamen Latar Belakang Abstrak -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-200/40 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-orange-100/50 rounded-full blur-2xl pointer-events-none"></div>

        <!-- BAGIAN ATAS: KATEGORI TAB -->
        <div class="z-10">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-base font-bold text-gray-900">Katalog Menu</h1>
                <span class="text-xs font-semibold text-amber-800 bg-amber-100/80 px-3 py-1 rounded-full">Meja 1</span>
            </div>

            <!-- Tab Kategori (Minuman, Makanan, Cemilan) -->
            <div class="grid grid-cols-3 gap-2 mb-6">
                <button onclick="pilihKategori('minuman', this)" id="tabMinuman" class="py-2 px-3 bg-[#6b5344] text-white text-xs font-bold rounded-xl shadow-md transition text-center cursor-pointer">
                    Minuman
                </button>
                <button onclick="pilihKategori('makanan', this)" id="tabMakanan" class="py-2 px-3 bg-amber-100/70 text-gray-700 text-xs font-semibold rounded-xl hover:bg-amber-200 transition text-center cursor-pointer">
                    Makanan
                </button>
                <button onclick="pilihKategori('cemilan', this)" id="tabCemilan" class="py-2 px-3 bg-amber-100/70 text-gray-700 text-xs font-semibold rounded-xl hover:bg-amber-200 transition text-center cursor-pointer">
                    Cemilan
                </button>
            </div>

            <!-- DAFTAR ITEM MENU -->
            <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                
                <!-- Item 1 -->
                <div class="bg-amber-50/60 border border-amber-200/60 rounded-2xl p-3 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-200/80 flex items-center justify-center text-xl shadow-inner shrink-0">☕</div>
                        <div>
                            <h3 class="font-bold text-xs text-gray-900">Kopi susu</h3>
                            <p class="text-xs text-amber-900 font-semibold mt-0.5">Rp 12.000</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-white px-2 py-1 rounded-xl shadow-inner border border-amber-100">
                        <button onclick="ubahQty(1, -1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">-</button>
                        <span id="qty-1" class="text-xs font-bold text-gray-800 w-4 text-center">1</span>
                        <button onclick="ubahQty(1, 1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">+</button>
                    </div>
                </div>

                <!-- Item 2 -->
                <div class="bg-amber-50/60 border border-amber-200/60 rounded-2xl p-3 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-200/80 flex items-center justify-center text-xl shadow-inner shrink-0">☕</div>
                        <div>
                            <h3 class="font-bold text-xs text-gray-900">Kopi susu</h3>
                            <p class="text-xs text-amber-900 font-semibold mt-0.5">Rp 12.000</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-white px-2 py-1 rounded-xl shadow-inner border border-amber-100">
                        <button onclick="ubahQty(2, -1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">-</button>
                        <span id="qty-2" class="text-xs font-bold text-gray-800 w-4 text-center">1</span>
                        <button onclick="ubahQty(2, 1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">+</button>
                    </div>
                </div>

                <!-- Item 3 -->
                <div class="bg-amber-50/60 border border-amber-200/60 rounded-2xl p-3 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-200/80 flex items-center justify-center text-xl shadow-inner shrink-0">☕</div>
                        <div>
                            <h3 class="font-bold text-xs text-gray-900">Kopi susu</h3>
                            <p class="text-xs text-amber-900 font-semibold mt-0.5">Rp 12.000</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-white px-2 py-1 rounded-xl shadow-inner border border-amber-100">
                        <button onclick="ubahQty(3, -1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">-</button>
                        <span id="qty-3" class="text-xs font-bold text-gray-800 w-4 text-center">1</span>
                        <button onclick="ubahQty(3, 1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-amber-900 font-bold text-xs bg-gray-100 rounded-lg">+</button>
                    </div>
                </div>

            </div>
        </div>

        <!-- BAGIAN BAWAH: TOMBOL LIHAT KERANJANG -->
        <div class="z-10 pt-4 border-t border-amber-100">
            <button type="button" onclick="keKeranjang()" class="w-full py-4 bg-[#6b5344] hover:bg-[#584437] text-white font-bold text-xs rounded-2xl shadow-lg transition-all duration-200 cursor-pointer flex items-center justify-between px-6">
                <div class="text-left">
                    <span id="totalItemCount" class="block font-bold">2 item</span>
                    <span id="totalHargaSummary" class="text-[11px] font-normal text-amber-200">Rp. 30.000</span>
                </div>
                <div class="flex items-center gap-2">
                    <span>Lihat Keranjang</span>
                    <span>➔</span>
                </div>
            </button>
        </div>

    </div>

    <!-- SCRIPT INTERAKTIF JAVASCRIPT -->
    <script>
        let quantities = { 1: 1, 2: 1, 3: 1 };

        function ubahQty(id, change) {
            if (quantities[id] + change >= 0) {
                quantities[id] += change;
                document.getElementById(`qty-${id}`).innerText = quantities[id];
                hitungTotal();
            }
        }

        function hitungTotal() {
            let totalItems = 0;
            let totalPrice = 0;
            for (let id in quantities) {
                totalItems += quantities[id];
                totalPrice += quantities[id] * 12000;
            }
            document.getElementById('totalItemCount').innerText = totalItems + " item";
            document.getElementById('totalHargaSummary').innerText = "Rp. " + totalPrice.toLocaleString('id-ID');
        }

        function pilihKategori(kategori, element) {
            ['tabMinuman', 'tabMakanan', 'tabCemilan'].forEach(id => {
                let btn = document.getElementById(id);
                btn.className = "py-2 px-3 bg-amber-100/70 text-gray-700 text-xs font-semibold rounded-xl hover:bg-amber-200 transition text-center cursor-pointer";
            });
            element.className = "py-2 px-3 bg-[#6b5344] text-white text-xs font-bold rounded-xl shadow-md transition text-center cursor-pointer";
        }

        function keKeranjang() {
            alert("🛒 Mengarahkan ke halaman keranjang belanja...");
            window.location.href = "{{ route('keranjang') }}";
        }
    </script>

</body>
</html>