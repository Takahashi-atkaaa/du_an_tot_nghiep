<?php

namespace Database\Seeders;

use App\Models\VaiTro;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VaiTroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VaiTro::insert([
            [
                'ten_vai_tro' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_vai_tro' => 'Trưởng ca',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_vai_tro' => 'Nhân viên',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
