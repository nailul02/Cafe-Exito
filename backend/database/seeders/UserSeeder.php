<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Menambahkan akun demo yang belum ada. Akun existing tidak pernah diubah oleh seeder ini. */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = (string) env('SEED_DEFAULT_PASSWORD', '');
        if (strlen($password) < 8) {
            $this->command?->warn('SEED_DEFAULT_PASSWORD kosong/pendek: isi di .env sebelum db:seed.');

            return;
        }
        $accounts = [
            ['Budi Santoso', 'owner', 'owner@cafeexito.test', UserRole::Owner, null],
            ['Rian Baskoro', 'manager', 'manager@cafeexito.test', UserRole::Manager, null],
            ['Ucup', 'ucup', 'ucup@cafeexito.test', UserRole::Kasir, null],
            ['Nana', 'nana', 'nana@cafeexito.test', UserRole::BaristaKitchen, 'barista'],
            ['Dewi Anggraini', 'dewi', 'dewi@cafeexito.test', UserRole::BaristaKitchen, 'kitchen'],
        ];
        foreach ($accounts as [$name, $username, $email, $role, $station]) {
            $existing = User::query()->where('username', $username)->orWhere('email', $email)->first();
            if ($existing) {
                $this->command?->warn("Akun {$username} sudah ada; tidak ada data atau password yang diubah.");

                continue;
            }

            User::query()->create([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'station' => $station,
                'is_active' => true,
            ]);
        }
    }
}
