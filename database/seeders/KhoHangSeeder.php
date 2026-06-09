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
                'mo_ta' => 'Sản phẩm điện tử',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_danh_muc' => 'Thực phẩm',
                'mo_ta' => 'Sản phẩm thực phẩm',
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
                    'danh_muc_id' => $danhMucIds[0],
                    'ten_thuoc_tinh' => 'Màu sắc',
                    'kieu_du_lieu' => 'select',
                    'gia_tri_mac_dinh' => json_encode(['Đen', 'Trắng', 'Bạc']),
                    'bat_buoc' => true,
                    'trang_thai' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'danh_muc_id' => $danhMucIds[0],
                    'ten_thuoc_tinh' => 'Kích thước màn hình',
                    'kieu_du_lieu' => 'text',
                    'gia_tri_mac_dinh' => null,
                    'bat_buoc' => true,
                    'trang_thai' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'danh_muc_id' => $danhMucIds[1],
                    'ten_thuoc_tinh' => 'Hạn sử dụng',
                    'kieu_du_lieu' => 'date',
                    'gia_tri_mac_dinh' => null,
                    'bat_buoc' => false,
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
                'ky_hieu' => 'cai',
                'ty_le_quy_doi' => 1,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_don_vi' => 'Thùng',
                'ky_hieu' => 'thung',
                'ty_le_quy_doi' => 24,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_don_vi' => 'Kilogram',
                'ky_hieu' => 'kg',
                'ty_le_quy_doi' => 1,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $donViIds = DB::table('don_vi_san_pham')->pluck('id')->toArray();

        // San pham
        DB::table('san_pham')->insert([
            [
                'ten_san_pham' => 'Điện thoại thông minh A',
                'mo_ta' => 'Điện thoại thông minh cao cấp',
                'id_danh_muc' => $danhMucIds[0] ?? null,
                'id_thuoc_tinh' => null,
                'id_don_vi' => $donViIds[0] ?? null,
                'gia_ban' => 6990000,
                'gia_nhap' => 5000000,
                'so_luong_ton' => 50,
                'dinh_muc_toi_thieu' => 10,
                'hinh_anh' => null,
                'thuong_hieu' => 'SmartMart',
                'trang_thai' => 'dang_ban',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_san_pham' => 'Gạo ST25 5kg',
                'mo_ta' => 'Gạo ST25 hữu cơ',
                'id_danh_muc' => $danhMucIds[1] ?? null,
                'id_thuoc_tinh' => null,
                'id_don_vi' => $donViIds[1] ?? null,
                'gia_ban' => 120000,
                'gia_nhap' => 85000,
                'so_luong_ton' => 200,
                'dinh_muc_toi_thieu' => 50,
                'hinh_anh' => null,
                'thuong_hieu' => 'SmartMart Organic',
                'trang_thai' => 'dang_ban',
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
                    'nguoi_dung_id' => $nguoiDungs[2] ?? $nguoiDungs[0],
                    'nha_cung_cap_id' => $nhaCungCaps[0] ?? null,
                    'id_hoa_don' => null,
                    'loai_phieu' => 'nhap_kho',
                    'ghi_chu' => 'Nhập hàng đầu tuần',
                    'created_at' => now()->subDays(5),
                    'updated_at' => now()->subDays(5),
                ],
            ]);

            $phieuIds = DB::table('phieu')->pluck('id')->toArray();

            if (count($phieuIds) && count($sanPhamIds)) {
                DB::table('chi_tiet_phieu')->insert([
                    [
                        'phieu_id' => $phieuIds[0],
                        'san_pham_id' => $sanPhamIds[0] ?? null,
                        'so_luong' => 20,
                        'ma_lo' => 'LOT001',
                        'han_su_dung' => now()->addMonths(12)->toDateString(),
                        'so_luong_con_lai' => 20,
                        'ghi_chu' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'phieu_id' => $phieuIds[0],
                        'san_pham_id' => $sanPhamIds[1] ?? null,
                        'so_luong' => 100,
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
