<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhoHangSeeder extends Seeder
{
    public function run(): void
    {
        // Danh muc san pham
        DB::table('danh_muc_san_pham')->insert([
            [
                'ten_danh_muc' => 'Điện tử',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_danh_muc' => 'Thực phẩm',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $danhMucIds = DB::table('danh_muc_san_pham')->pluck('id')->toArray();

        // Thuoc tinh san pham
        if (count($danhMucIds)) {
            DB::table('thuoc_tinh_san_pham')->insert([
                [
                    'ten_thuoc_tinh' => 'Màu sắc',
                    'trang_thai' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'ten_thuoc_tinh' => 'Kích thước',
                    'trang_thai' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Don vi san pham
        DB::table('don_vi_san_pham')->insert([
            [
                'ten_don_vi' => 'Cái',
                'so_luong_san_pham_trong_don_vi' => 1,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_don_vi' => 'Thùng',
                'so_luong_san_pham_trong_don_vi' => 24,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_don_vi' => 'Kilogram',
                'so_luong_san_pham_trong_don_vi' => 1,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $donViIds = DB::table('don_vi_san_pham')->pluck('id')->toArray();

        // San pham
        DB::table('san_pham')->insert([
            [
                'id_danh_muc' => $danhMucIds[0] ?? null,
                'ten_san_pham' => 'Điện thoại thông minh A',
                'ma_vach' => 'DT001',
                'hinh_anh' => null,
                'thuong_hieu' => 'SmartMart',
                'gia_ban' => 6990000,
                'so_luong_ton_kho' => 50,
                'mo_ta' => 'Điện thoại thông minh cao cấp',
                'id_thuoc_tinh' => null,
                'id_don_vi' => $donViIds[0] ?? null,
                'dinh_muc_toi_thieu' => 10,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_danh_muc' => $danhMucIds[1] ?? null,
                'ten_san_pham' => 'Gạo ST25 5kg',
                'ma_vach' => 'GAO001',
                'hinh_anh' => null,
                'thuong_hieu' => 'SmartMart Organic',
                'gia_ban' => 120000,
                'so_luong_ton_kho' => 200,
                'mo_ta' => 'Gạo ST25 hữu cơ',
                'id_thuoc_tinh' => null,
                'id_don_vi' => $donViIds[1] ?? null,
                'dinh_muc_toi_thieu' => 50,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $sanPhamIds = DB::table('san_pham')->pluck('id')->toArray();
        $nguoiDungs = DB::table('nguoi_dung')->pluck('id')->toArray();
        $nhaCungCaps = DB::table('nha_cung_cap')->pluck('id')->toArray();

        if (count($nguoiDungs) && count($sanPhamIds)) {
            // Phieu nhap kho
            DB::table('phieu')->insert([
                [
                    'id_nguoi_dung' => $nguoiDungs[2] ?? $nguoiDungs[0],
                    'id_nha_cung_cap' => $nhaCungCaps[0] ?? null,
                    'id_hoa_don' => null,
                    'loai_phieu' => 'Nhập hàng',
                    'ghi_chu' => 'Nhập hàng đầu tuần',
                    'created_at' => now()->subDays(5),
                    'updated_at' => now()->subDays(5),
                ],
            ]);

            $phieuIds = DB::table('phieu')->pluck('id')->toArray();

            if (count($phieuIds) && count($sanPhamIds)) {
                DB::table('chi_tiet_phieu')->insert([
                    [
                        'id_phieu' => $phieuIds[0],
                        'id_san_pham' => $sanPhamIds[0] ?? null,
                        'so_luong' => 20,
                        'gia_nhap' => 5000000,
                        'ma_lo' => 'LOT001',
                        'han_su_dung' => now()->addMonths(12)->toDateString(),
                        'so_luong_con_lai' => 20,
                        'ghi_chu' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'id_phieu' => $phieuIds[0],
                        'id_san_pham' => $sanPhamIds[1] ?? null,
                        'so_luong' => 100,
                        'gia_nhap' => 85000,
                        'ma_lo' => 'LOT002',
                        'han_su_dung' => now()->addMonths(6)->toDateString(),
                        'so_luong_con_lai' => 100,
                        'ghi_chu' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }
    }
}
