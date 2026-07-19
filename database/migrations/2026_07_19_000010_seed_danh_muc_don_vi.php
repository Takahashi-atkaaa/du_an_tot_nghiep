<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $data = [
            ['ten_don_vi' => 'Thùng', 'so_luong_san_pham_trong_don_vi' => 24, 'trang_thai' => true],
            ['ten_don_vi' => 'Thùng', 'so_luong_san_pham_trong_don_vi' => 12, 'trang_thai' => true],
            ['ten_don_vi' => 'Thùng', 'so_luong_san_pham_trong_don_vi' => 6,  'trang_thai' => true],
            ['ten_don_vi' => 'Bao',   'so_luong_san_pham_trong_don_vi' => 6,  'trang_thai' => true],
            ['ten_don_vi' => 'Bao',   'so_luong_san_pham_trong_don_vi' => 5,  'trang_thai' => true],
            ['ten_don_vi' => 'Lốc',   'so_luong_san_pham_trong_don_vi' => 6,  'trang_thai' => true],
            ['ten_don_vi' => 'Lốc',   'so_luong_san_pham_trong_don_vi' => 4,  'trang_thai' => true],
            ['ten_don_vi' => 'Bịch',  'so_luong_san_pham_trong_don_vi' => 4,  'trang_thai' => true],
            ['ten_don_vi' => 'Bịch',  'so_luong_san_pham_trong_don_vi' => 6,  'trang_thai' => true],
            ['ten_don_vi' => 'Hộp',   'so_luong_san_pham_trong_don_vi' => 6,  'trang_thai' => true],
            ['ten_don_vi' => 'Hộp',   'so_luong_san_pham_trong_don_vi' => 4,  'trang_thai' => true],
            ['ten_don_vi' => 'Túi',   'so_luong_san_pham_trong_don_vi' => 6,  'trang_thai' => true],
            ['ten_don_vi' => 'Túi',   'so_luong_san_pham_trong_don_vi' => 10, 'trang_thai' => true],
        ];

        $now = now();
        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('danh_muc_don_vi')->insert($data);
    }

    public function down(): void
    {
        DB::table('danh_muc_don_vi')->delete();
    }
};
