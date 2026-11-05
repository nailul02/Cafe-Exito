<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Exito Cafe</title>
    <!-- Menggunakan Tailwind CSS untuk styling yang presisi -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-white min-h-screen flex flex-col justify-center items-center">

    <div class="w-full max-w-md mx-auto p-6 flex flex-col items-center">
        
        <!-- Logo Lingkaran Exito -->
        <div class="w-24 h-24 rounded-full border border-gray-200 flex items-center justify-center mb-6 shadow-sm bg-white">
            <span class="text-2xl italic font-serif tracking-wider text-gray-800">exito</span>
        </div>

        <!-- Judul Login -->
        <h1 class="text-2xl font-semibold text-gray-900 mb-8">Login</h1>

        <!-- Card Form Login -->
        <div class="w-full border border-gray-200 rounded-lg p-8 bg-white shadow-sm">
            <form action="{{ route('proses-login') }}" method="POST">
                <!-- CSRF Token untuk Laravel -->
                @csrf

                <!-- Input Email -->
                <div class="mb-5">
                    <label class="block text-sm text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" placeholder="admin@gmail.com" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 placeholder-gray-400 text-gray-700">
                </div>

                <!-- Input Password -->
                <div class="mb-5">
                    <label class="block text-sm text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" placeholder="********" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 placeholder-gray-400 text-gray-700">
                </div>

                <!-- Pilihan Role / Masuk Sebagai -->
                <div class="mb-6">
                    <label class="block text-sm text-gray-700 mb-2">Masuk Sebagai</label>
                    <select name="role" required 
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 text-gray-700 bg-white">
                        <option value="kasir">Kasir</option>
                        <option value="barista">Barista</option>
                    </select>
                </div>

                <!-- Tombol Login -->
                <button type="submit" 
                    class="w-full py-2.5 bg-[#6b5344] hover:bg-[#584437] text-white font-medium rounded-md text-sm transition duration-200 shadow-sm">
                    Login
                </button>
            </form>
        </div>

    </div>

</body>
</html>