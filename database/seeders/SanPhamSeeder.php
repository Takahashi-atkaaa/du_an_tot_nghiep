<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SanPhamSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================================
        // BUOC 1: XOA VINH VIEN DU LIEU SAN PHAM CU
        // (uu tien xoa cac bang con truoc de tranh FK constraint)
        // ==========================================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('chi_tiet_phieu')->truncate();
        DB::table('chi_tiet_lo_hang')->truncate();
        DB::table('lo_hang')->truncate();
        DB::table('phieu_xuat')->truncate();
        DB::table('phieu_nhap')->truncate();
        DB::table('phieu')->truncate();
        DB::table('don_vi_quy_doi')->truncate();
        DB::table('bien_the_san_pham')->truncate();
        DB::table('san_pham')->truncate();
        DB::table('thuoc_tinh_san_pham')->truncate();
        DB::table('danh_muc_san_pham')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();

        // ==========================================================
        // BUOC 2: TAO DANH MUC SAN PHAM
        // ==========================================================
        $danhMucs = [
            'Thực phẩm' => ['mau_sac' => '#198754', 'icon' => 'bi bi-bag-fill'],
            'Đồ uống' => ['mau_sac' => '#0d6efd', 'icon' => 'bi bi-cup-straw'],
            'Bánh kẹo' => ['mau_sac' => '#dc3545', 'icon' => 'bi bi-cupcake'],
            'Mì gói' => ['mau_sac' => '#fd7e14', 'icon' => 'bi bi-basket3-fill'],
            'Chăm sóc cá nhân' => ['mau_sac' => '#6f42c1', 'icon' => 'bi bi-droplet-fill'],
            'Thời trang' => ['mau_sac' => '#d63384', 'icon' => 'bi bi-bag-heart'],
        ];

        $danhMucIds = [];
        foreach ($danhMucs as $ten => $meta) {
            $id = DB::table('danh_muc_san_pham')->insertGetId([
                'ten_danh_muc' => $ten,
                'mau_sac' => $meta['mau_sac'],
                'icon' => $meta['icon'],
                'trang_thai' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $danhMucIds[$ten] = $id;
        }

        // ==========================================================
        // BUOC 3: TAO THUOC TINH (CHA + CON)
        // ==========================================================
        $kichThuocId = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh' => 'Kích thước',
            'trang_thai' => true,
            'thuoc_tinh_cha_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $mauSacId = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh' => 'Màu sắc',
            'trang_thai' => true,
            'thuoc_tinh_cha_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Con cua Kich thuoc
        $sizeIds = [];
        foreach (['M', 'L', 'XL', '38', '39', '40'] as $size) {
            $sizeIds[$size] = DB::table('thuoc_tinh_san_pham')->insertGetId([
                'ten_thuoc_tinh' => $size,
                'trang_thai' => true,
                'thuoc_tinh_cha_id' => $kichThuocId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Con cua Mau sac
        $mauIds = [];
        foreach (['Đen', 'Trắng', 'Hồng', 'Xanh'] as $mau) {
            $mauIds[$mau] = DB::table('thuoc_tinh_san_pham')->insertGetId([
                'ten_thuoc_tinh' => $mau,
                'trang_thai' => true,
                'thuoc_tinh_cha_id' => $mauSacId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ==========================================================
        // BUOC 4: TAO 10 SAN PHAM CHA
        // ==========================================================
        $products = [
            [
                'key' => 'lavie_500',
                'ten_san_pham' => 'Nước khoáng Lavie 500ml',
                'id_danh_muc' => $danhMucIds['Đồ uống'],
                'thuong_hieu' => 'Lavie',
                'mo_ta' => 'Nước khoáng thiên nhiên Lavie 500ml, đóng chai tiện lợi.',
                'type' => 'thuong',
            ],
            [
                'key' => 'omachi_tom',
                'ten_san_pham' => 'Mì Omachi tôm chua cay',
                'id_danh_muc' => $danhMucIds['Mì gói'],
                'thuong_hieu' => 'Omachi',
                'mo_ta' => 'Mì ăn liền Omachi hương vị tôm chua cay, gói 85g.',
                'type' => 'thuong',
            ],
            [
                'key' => 'oishi_bo',
                'ten_san_pham' => 'Snack Oishi vị bò',
                'id_danh_muc' => $danhMucIds['Bánh kẹo'],
                'thuong_hieu' => 'Oishi',
                'mo_ta' => 'Snack Oishi vị bò nướng, gói 45g.',
                'type' => 'thuong',
            ],
            [
                'key' => 'clear_men',
                'ten_san_pham' => 'Dầu gội Clear Men 200ml',
                'id_danh_muc' => $danhMucIds['Chăm sóc cá nhân'],
                'thuong_hieu' => 'Clear',
                'mo_ta' => 'Dầu gội Clear Men bạc hà mát lạnh, chai 200ml.',
                'type' => 'thuong',
            ],
            [
                'key' => 'colgate_brush',
                'ten_san_pham' => 'Bàn chải đánh răng Colgate',
                'id_danh_muc' => $danhMucIds['Chăm sóc cá nhân'],
                'thuong_hieu' => 'Colgate',
                'mo_ta' => 'Bàn chải đánh răng Colgate lông mềm, cho người lớn.',
                'type' => 'thuong',
            ],
            [
                'key' => 'heineken_lon',
                'ten_san_pham' => 'Bia Heineken lon 330ml',
                'id_danh_muc' => $danhMucIds['Đồ uống'],
                'thuong_hieu' => 'Heineken',
                'mo_ta' => 'Bia Heineken lon 330ml, nhập khẩu Hà Lan.',
                'type' => 'don_vi',
                'units' => [
                    ['ten' => 'Lon', 'ty_le' => 1, 'mac_dinh' => true],
                    ['ten' => 'Thùng', 'ty_le' => 24, 'mac_dinh' => false],
                ],
            ],
            [
                'key' => 'vinamilk_1l',
                'ten_san_pham' => 'Sữa tươi Vinamilk 1L',
                'id_danh_muc' => $danhMucIds['Thực phẩm'],
                'thuong_hieu' => 'Vinamilk',
                'mo_ta' => 'Sữa tươi tiệt trùng Vinamilk 100% sữa tươi, hộp 1L.',
                'type' => 'don_vi',
                'units' => [
                    ['ten' => 'Hộp', 'ty_le' => 1, 'mac_dinh' => true],
                    ['ten' => 'Thùng', 'ty_le' => 12, 'mac_dinh' => false],
                ],
            ],
            [
                'key' => 'gao_st25',
                'ten_san_pham' => 'Gạo ST25 túi 5kg',
                'id_danh_muc' => $danhMucIds['Thực phẩm'],
                'thuong_hieu' => 'ST25',
                'mo_ta' => 'Gạo ST25 hữu cơ túi 5kg, gạo ngon nhất thế giới.',
                'type' => 'don_vi',
                'units' => [
                    ['ten' => 'Túi', 'ty_le' => 1, 'mac_dinh' => true],
                    ['ten' => 'Bao', 'ty_le' => 4, 'mac_dinh' => false],
                ],
            ],
            [
                'key' => 'ao_thun',
                'ten_san_pham' => 'Áo thun nam cổ tròn',
                'id_danh_muc' => $danhMucIds['Thời trang'],
                'thuong_hieu' => 'SmartMart',
                'mo_ta' => 'Áo thun nam cổ tròn chất cotton 100%, co giãn tốt.',
                'type' => 'thuoc_tinh',
                'combos' => [
                    ['size' => 'M',  'mau' => 'Đen'],
                    ['size' => 'M',  'mau' => 'Trắng'],
                    ['size' => 'L',  'mau' => 'Đen'],
                    ['size' => 'L',  'mau' => 'Trắng'],
                    ['size' => 'XL', 'mau' => 'Đen'],
                    ['size' => 'XL', 'mau' => 'Trắng'],
                ],
            ],
            [
                'key' => 'giay_the_thao',
                'ten_san_pham' => 'Giày thể thao nữ',
                'id_danh_muc' => $danhMucIds['Thời trang'],
                'thuong_hieu' => 'SmartMart',
                'mo_ta' => 'Giày thể thao nữ đế mềm, thoáng khí, phù hợp chạy bộ.',
                'type' => 'thuoc_tinh',
                'combos' => [
                    ['size' => '38', 'mau' => 'Hồng'],
                    ['size' => '38', 'mau' => 'Xanh'],
                    ['size' => '39', 'mau' => 'Hồng'],
                    ['size' => '39', 'mau' => 'Xanh'],
                    ['size' => '40', 'mau' => 'Hồng'],
                    ['size' => '40', 'mau' => 'Xanh'],
                ],
            ],
        ];

        // Gia ban mac dinh theo loai san pham
        $giaBanMacDinh = [
            'lavie_500'      => 5000,
            'omachi_tom'     => 8500,
            'oishi_bo'       => 10000,
            'clear_men'      => 65000,
            'colgate_brush'  => 25000,
            'heineken_lon'   => 14000,
            'vinamilk_1l'    => 28000,
            'gao_st25'       => 130000,
            'ao_thun'        => 120000,
            'giay_the_thao'  => 350000,
        ];

        // Dinh muc toi thieu mac dinh
        $dinhMucMacDinh = [
            'lavie_500'      => 20,
            'omachi_tom'     => 30,
            'oishi_bo'       => 25,
            'clear_men'      => 10,
            'colgate_brush'  => 10,
            'heineken_lon'   => 15,
            'vinamilk_1l'    => 15,
            'gao_st25'       => 8,
            'ao_thun'        => 5,
            'giay_the_thao'  => 5,
        ];

        // Insert san pham cha + variants + units
        foreach ($products as $p) {
            $productId = DB::table('san_pham')->insertGetId([
                'id_danh_muc' => $p['id_danh_muc'],
                'ten_san_pham' => $p['ten_san_pham'],
                'thuong_hieu' => $p['thuong_hieu'],
                'mo_ta' => $p['mo_ta'],
                'trang_thai' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $giaBan = $giaBanMacDinh[$p['key']];
            $dinhMuc = $dinhMucMacDinh[$p['key']];

            if ($p['type'] === 'thuong' || $p['type'] === 'don_vi') {
                // 1 variant don le
                $variantId = DB::table('bien_the_san_pham')->insertGetId([
                    'product_id' => $productId,
                    'ten_bien_the' => null,
                    'ma_hang' => strtoupper(substr($p['key'], 0, 6)) . '-001',
                    'ma_vach' => '8934' . str_pad((string)$productId, 8, '0', STR_PAD_LEFT),
                    'gia_von' => (int)($giaBan * 0.7),
                    'gia_ban' => $giaBan,
                    'so_luong_ton' => 0, // observer se tu cap nhat khi seed lo hang
                    'dinh_muc_toi_thieu' => $dinhMuc,
                    'hinh_anh' => null,
                    'thuoc_tinh_ids' => null,
                    'trang_thai' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Neu co units -> insert don_vi_quy_doi
                if ($p['type'] === 'don_vi' && !empty($p['units'])) {
                    foreach ($p['units'] as $idx => $u) {
                        DB::table('don_vi_quy_doi')->insert([
                            'variant_id' => $variantId,
                            'ten_don_vi' => $u['ten'],
                            'ty_le_quy_doi' => $u['ty_le'],
                            'ma_hang' => strtoupper(substr($p['key'], 0, 6)) . '-' . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT),
                            'ma_vach' => '8934' . str_pad((string)($productId), 4, '0', STR_PAD_LEFT) . str_pad((string)($idx + 1), 4, '0', STR_PAD_LEFT),
                            'gia_von_quy_doi' => (int)($giaBan * 0.7) * $u['ty_le'],
                            'gia_ban_quy_doi' => $giaBan * $u['ty_le'],
                            'gia_ban_si' => $giaBan * $u['ty_le'] * 0.85,
                            'hinh_anh' => null,
                            'la_don_vi_mac_dinh' => $u['mac_dinh'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            } elseif ($p['type'] === 'thuoc_tinh') {
                // Nhieu variant theo Size x Mau
                foreach ($p['combos'] as $idx => $combo) {
                    $tenBienThe = $combo['size'] . ' - ' . $combo['mau'];
                    $thuocTinhIds = [$sizeIds[$combo['size']], $mauIds[$combo['mau']]];

                    DB::table('bien_the_san_pham')->insert([
                        'product_id' => $productId,
                        'ten_bien_the' => $tenBienThe,
                        'ma_hang' => strtoupper(substr($p['key'], 0, 6)) . '-' . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT),
                        'ma_vach' => '8934' . str_pad((string)$productId, 4, '0', STR_PAD_LEFT) . str_pad((string)($idx + 1), 4, '0', STR_PAD_LEFT),
                        'gia_von' => (int)($giaBan * 0.6),
                        'gia_ban' => $giaBan,
                        'so_luong_ton' => 0,
                        'dinh_muc_toi_thieu' => $dinhMuc,
                        'hinh_anh' => null,
                        'thuoc_tinh_ids' => json_encode($thuocTinhIds),
                        'trang_thai' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        $this->command->info('=== SanPhamSeeder hoan thanh ===');
        $this->command->info('Da tao: 6 danh muc, 10 san pham, ' . DB::table('bien_the_san_pham')->count() . ' variants, ' . DB::table('don_vi_quy_doi')->count() . ' units.');
    }
}