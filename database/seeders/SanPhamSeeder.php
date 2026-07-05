<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SanPhamSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('san_pham')->insert([
            [
                'id_danh_muc' => 1,
                'ten_san_pham' => 'Sản phẩm 1',
                'hinh_anh' => null,
                'gia_ban' => 100000,
                'mo_ta' => 'Mô tả sản phẩm 1',
                'dinh_muc_toi_thieu' => 10,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_danh_muc' => 2,
                'ten_san_pham' => 'Sản phẩm 2',
                'hinh_anh' => null,
                'gia_ban' => 200000,
                'mo_ta' => 'Mô tả sản phẩm 2',
                'dinh_muc_toi_thieu' => 5,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}