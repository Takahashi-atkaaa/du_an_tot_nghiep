<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DanhMucSanPhamSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('danh_muc_san_pham')->insert([
            [
                'id' => 1,
                'ten_danh_muc' => 'Đồ uống',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'ten_danh_muc' => 'Bánh kẹo',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}