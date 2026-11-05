<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes - Exito Cafe
|--------------------------------------------------------------------------
*/

// ==========================================
// RUTE KASIR EXITO CAFE
// ==========================================

Route::match(['get', 'post'], '/loginkasir', function (Request $request) {
    if ($request->isMethod('post')) {
        $role = $request->input('role');

        if ($role === 'barista') {
            return redirect()->route('dashboard-barista');
        }
        
        return redirect()->route('dashboard');
    }

    return view('loginkasir');
})->name('proses-login');

Route::get('/dashboard', function () {
    return view('dashboardkasir');
})->name('dashboard');

Route::get('/pesanan-kasir', function () {
    return view('pesanankasir');
})->name('pesanan-kasir');

Route::get('/detail-transaksi', function () {
    return view('detailtransaksi');
})->name('detail-transaksi');

Route::get('/absensi-kasir', function () {
    return view('absensikasir');
})->name('absensi-kasir');

Route::get('/profil-kasir', function () {
    return view('profilkasir');
})->name('profil-kasir');


// ==========================================
// RUTE BARISTA EXITO CAFE
// ==========================================

Route::get('/dashboard-barista', function () {
    return view('dashboardbarista');
})->name('dashboard-barista');

Route::get('/pesanan-barista', function () {
    return view('pesananbarista');
})->name('pesanan-barista');

Route::get('/absensi-barista', function () {
    return view('absensibarista');
})->name('absensi-barista');

Route::get('/barista/detail-transaksi', function () {
    return view('detailtransaksi');
})->name('barista.detail-transaksi');

Route::get('/profil-barista', function () {
    return view('profilbarista');
})->name('profil-barista');


// ==========================================
// RUTE OWNER EXITO CAFE (Tanpa Data Karyawan)
// ==========================================

Route::get('/owner-dashboard', function () {
    return view('ownerdashboard');
})->name('owner-dashboard');

Route::get('/laporan-penjualan', function () {
    return view('laporanpenjualan');
})->name('laporan-penjualan');

Route::get('/stok-bahan', function () {
    return view('stokbahan');
})->name('stok-bahan');

Route::get('/profil-akun', function () {
    return view('profilakun');
})->name('profil-akun');


// ==========================================
// RUTE DASHBOARD ADMIN EXITO CAFE
// ==========================================

Route::get('/dashboard-admin', function () {
    return view('dashboardadmin');
})->name('dashboard-admin');

Route::get('/kelola-menu', function () {
    return view('kelolamenu'); 
})->name('kelola-menu');

Route::get('/kelola-stok', function () {
    return view('kelolastok'); 
})->name('kelola-stok');

Route::get('/kelola-jadwal-shift', function () {
    return view('kelolajadwalshift'); 
})->name('kelola-jadwal-shift');

Route::get('/kelola-absensi', function () {
    return view('kelolaabsensi'); 
})->name('kelola-absensi');

Route::get('/kelola-laporan', function () {
    return view('kelolaporan'); 
})->name('kelola-laporan');

//pelanggan
Route::get('/scan-menu', function () {
    return view('scanmenu');
})->name('scan-menu');

Route::get('/selamat-datang', function () {
    return view('selamatdatang');
})->name('selamat-datang');

Route::get('/katalog-menu', function () {
    return view('katalogmenu');
})->name('katalog-menu');

Route::get('/keranjang', function () {
    return view('keranjang');
})->name('keranjang');

Route::get('/pembayaran', function () {
    return view('pembayaran');
})->name('pembayaran');

Route::get('/pesanan-diterima', function () {
    return view('pesananditerima');
})->name('pesanan-diterima');

Route::get('/status-pesanan', function () {
    return view('statuspesanan');
})->name('status-pesanan');