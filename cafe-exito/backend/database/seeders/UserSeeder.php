<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Satu akun per role. Kata sandi dari SEED_DEFAULT_PASSWORD (.env), tidak pernah tertanam di kode. */
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
            ['Budi Santoso', 'owner', 'owner@cafeexito.test', UserRole::Owner],
            ['Rian Baskoro', 'manager', 'manager@cafeexito.test', UserRole::Admin],
            ['Ucup', 'ucup', 'ucup@cafeexito.test', UserRole::Kasir],
            ['Nana', 'nana', 'nana@cafeexito.test', UserRole::Barista],
            ['Dewi Anggraini', 'dewi', 'dewi@cafeexito.test', UserRole::Kitchen],
        ];
        foreach ($accounts as [$name, $username, $email, $role]) {
            User::query()->updateOrCreate(['username' => $username], ['name' => $name, 'email' => $email, 'password' => $password, 'role' => $role->value, 'is_active' => true]);
        }
    }
}
