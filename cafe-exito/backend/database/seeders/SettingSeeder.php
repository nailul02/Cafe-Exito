<?php

namespace Database\Seeders;

use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(SettingsService $settings): void
    {
        $settings->set('cafe_name', 'Cafe Exito');
        $settings->set('cafe_address', 'Koto Tangah, Padang');
        $settings->set('receipt_footer', 'Terima kasih sudah berkunjung');
        // Gambar QRIS statis: unggah lewat pengaturan nanti (K-T8); jangan menanam QR pembayaran asli di repository.
        $settings->set('qris_image_url', null);
    }
}
