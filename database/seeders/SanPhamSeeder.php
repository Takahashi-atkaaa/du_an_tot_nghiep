<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SanPhamSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $danhMucs = [
            'Thực phẩm' => ['mau_sac' => '#198754', 'icon' => 'bi bi-bag-fill'],
            'Đồ uống' => ['mau_sac' => '#0d6efd', 'icon' => 'bi bi-cup-straw'],
            'Bánh kẹo' => ['mau_sac' => '#dc3545', 'icon' => 'bi bi-cupcake'],
            'Mì gói' => ['mau_sac' => '#fd7e14', 'icon' => 'bi bi-basket3-fill'],
        ];

        $danhMucIds = [];
        foreach ($danhMucs as $ten => $meta) {
            DB::table('danh_muc_san_pham')->updateOrInsert(
                ['ten_danh_muc' => $ten],
                [
                    'mau_sac' => $meta['mau_sac'],
                    'icon' => $meta['icon'],
                    'trang_thai' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $danhMucIds[$ten] = DB::table('danh_muc_san_pham')
                ->where('ten_danh_muc', $ten)
                ->value('id');
        }

        $donVis = [
            'Cái' => 1,
            'Gói' => 1,
            'Chai' => 1,
            'Hộp' => 1,
        ];

        $donViIds = [];
        foreach ($donVis as $ten => $soLuong) {
            DB::table('don_vi_san_pham')->updateOrInsert(
                ['ten_don_vi' => $ten],
                [
                    'so_luong_san_pham_trong_don_vi' => $soLuong,
                    'trang_thai' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $donViIds[$ten] = DB::table('don_vi_san_pham')
                ->where('ten_don_vi', $ten)
                ->value('id');
        }

        $products = [
            [
                'ten_san_pham' => 'Sữa tươi Vinamilk 180ml',
                'ma_vach' => '8934567890123',
                'id_danh_muc' => $danhMucIds['Thực phẩm'],
                'id_don_vi' => $donViIds['Chai'],
                'thuong_hieu' => 'Vinamilk',
                'hinh_anh' => 'https://via.placeholder.com/150',
                'gia_ban' => 8500.00,
                'so_luong_ton_kho' => 250,
                'mo_ta' => 'Sữa tươi tiệt trùng Vinamilk 180ml.',
                'dinh_muc_toi_thieu' => 20,
                'trang_thai' => true,
            ],
            [
                'ten_san_pham' => 'Bánh Oreo 133g',
                'ma_vach' => '8934567890124',
                'id_danh_muc' => $danhMucIds['Bánh kẹo'],
                'id_don_vi' => $donViIds['Gói'],
                'thuong_hieu' => 'Oreo',
                'hinh_anh' => 'https://via.placeholder.com/150',
                'gia_ban' => 22000.00,
                'so_luong_ton_kho' => 180,
                'mo_ta' => 'Bánh quy sô cô la Oreo 133g.',
                'dinh_muc_toi_thieu' => 15,
                'trang_thai' => true,
            ],
            [
                'ten_san_pham' => 'Mì Hảo Tấm gói',
                'ma_vach' => '8934567890125',
                'id_danh_muc' => $danhMucIds['Mì gói'],
                'id_don_vi' => $donViIds['Gói'],
                'thuong_hieu' => 'Hảo Tấm',
                'hinh_anh' => 'https://via.placeholder.com/150',
                'gia_ban' => 7000.00,
                'so_luong_ton_kho' => 45,
                'mo_ta' => 'Mì ăn liền Hảo Tấm hương vị truyền thống.',
                'dinh_muc_toi_thieu' => 30,
                'trang_thai' => true,
            ],
            [
                'ten_san_pham' => 'Nước ngọt Coca Cola 330ml',
                'ma_vach' => '8934567890126',
                'id_danh_muc' => $danhMucIds['Đồ uống'],
                'id_don_vi' => $donViIds['Chai'],
                'thuong_hieu' => 'Coca Cola',
                'hinh_anh' => 'https://via.placeholder.com/150',
                'gia_ban' => 12000.00,
                'so_luong_ton_kho' => 0,
                'mo_ta' => 'Nước ngọt Coca Cola lon 330ml.',
                'dinh_muc_toi_thieu' => 10,
                'trang_thai' => false,
            ],
            [
                'ten_san_pham' => 'Cà phê G7 3in1',
                'ma_vach' => '8934567890127',
                'id_danh_muc' => $danhMucIds['Đồ uống'],
                'id_don_vi' => $donViIds['Gói'],
                'thuong_hieu' => 'G7',
                'hinh_anh' => 'https://via.placeholder.com/150',
                'gia_ban' => 35000.00,
                'so_luong_ton_kho' => 120,
                'mo_ta' => 'Cà phê hòa tan G7 3in1 tiện lợi.',
                'dinh_muc_toi_thieu' => 30,
                'trang_thai' => true,
            ],
        ];

        foreach ($products as $product) {
            DB::table('san_pham')->updateOrInsert(
                ['ma_vach' => $product['ma_vach']],
                array_merge($product, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
