<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DanhMucDonViSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('danh_muc_don_vi')->count() > 0) {
            $this->command->info('[DanhMucDonViSeeder] Bang da co du lieu. Bo qua.');
            return;
        }

        $now = now();
        $donVis = [
            ['ten_don_vi' => 'Chai', 'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
            ['ten_don_vi' => 'Lon',  'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
            ['ten_don_vi' => 'Goi',  'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
            ['ten_don_vi' => 'Hop',  'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
            ['ten_don_vi' => 'Tui',  'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
            ['ten_don_vi' => 'Kg',   'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
            ['ten_don_vi' => 'Gram', 'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
            ['ten_don_vi' => 'Lit',  'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
            ['ten_don_vi' => 'ml',   'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
            ['ten_don_vi' => 'Cai',  'so_luong_san_pham_trong_don_vi' => 1,  'trang_thai' => true],
        ];

        foreach ($donVis as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('danh_muc_don_vi')->insert($donVis);

        $this->command->info('[DanhMucDonViSeeder] Da tao '.count($donVis).' don vi co ban.');
    }
}
