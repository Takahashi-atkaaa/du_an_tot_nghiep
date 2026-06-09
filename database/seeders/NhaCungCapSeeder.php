<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NhaCungCapSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('nha_cung_cap')->insert([
            [
                'ten_nha_cung_cap' => 'Công ty TNHH Thực phẩm ABC',
                'email' => 'abc@supplier.vn',
                'so_dien_thoai' => '0241234567',
                'nguoi_dai_dien' => 'Nguyễn Văn X',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_nha_cung_cap' => 'Công ty CP Điện tử XYZ',
                'email' => 'xyz@supplier.vn',
                'so_dien_thoai' => '0287654321',
                'nguoi_dai_dien' => 'Trần Thị Y',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
