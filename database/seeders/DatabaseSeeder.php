<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            NhanSuSeeder::class,
            KhachHangSeeder::class,
            NhaCungCapSeeder::class,
            KhoHangSeeder::class,
            SanPhamSeeder::class,
            BanHangSeeder::class,
        ]);
    }
}
