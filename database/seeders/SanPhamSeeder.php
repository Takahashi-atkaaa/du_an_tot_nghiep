<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SanPhamSeeder
 *
 * Reset và tạo 100 sản phẩm theo kiến trúc POS chuẩn 3 bảng:
 *
 *   san_pham (cha)
 *     └── bien_the_san_pham  ← Đơn vị cơ bản (ty_le = 1), chứa giá gốc, mã vạch gốc, tồn kho
 *           └── don_vi_quy_doi ← Đơn vị quy đổi (ty_le > 1), KHÔNG có đơn vị cơ bản
 *
 * Thứ tự xóa: bảng con trước → bảng cha sau.
 *
 * Yêu cầu:
 *   - 100 sản phẩm
 *   - Giá từ 2000 VND trở lên
 *   - Nhiều biến thể / đơn vị
 *
 * 3 loại sản phẩm:
 *   - thuong     : 1 variant, 0 đơn vị quy đổi
 *   - don_vi     : 1 variant (là đơn vị cơ bản), nhiều đơn vị quy đổi
 *   - thuoc_tinh : nhiều variant (mỗi variant = 1 đơn vị cơ bản), 0 đơn vị quy đổi
 */
class SanPhamSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('[SanPhamSeeder] Bat dau reset va tao 100 san pham...');

        // ==========================================================
        // BUOC 1: XOA DU LIEU CU (thu tu tu ngoai vao trong)
        // ==========================================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('chi_tiet_phieu')->truncate();
        DB::table('chi_tiet_lo_hang')->truncate();
        DB::table('lo_hang')->truncate();
        DB::table('phieu_xuat')->truncate();
        DB::table('phieu_nhap')->truncate();
        DB::table('phieu')->truncate();
        DB::table('khuyen_mai_san_pham')->truncate();
        DB::table('don_vi_quy_doi')->truncate();
        DB::table('bien_the_san_pham')->truncate();
        DB::table('san_pham')->truncate();
        DB::table('thuoc_tinh_san_pham')->truncate();
        DB::table('danh_muc_san_pham')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('[1/7] Da xoa du lieu cu.');

        // ==========================================================
        // BUOC 2: TAO DANH MUC
        // ==========================================================
        $danhMucs = [
            'Thực phẩm'         => ['mau' => '#198754'],
            'Đồ uống'          => ['mau' => '#0d6efd'],
            'Bánh kẹo'          => ['mau' => '#dc3545'],
            'Mì gói'            => ['mau' => '#fd7e14'],
            'Chăm sóc cá nhân'  => ['mau' => '#6f42c1'],
            'Thời trang'        => ['mau' => '#d63384'],
        ];
        $dmIds = [];
        $now = now();
        foreach ($danhMucs as $ten => $m) {
            $dmIds[$ten] = DB::table('danh_muc_san_pham')->insertGetId([
                'ten_danh_muc' => $ten,
                'mau_sac'      => $m['mau'],
                'trang_thai'   => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
        $this->command->info('[2/7] Da tao ' . $this->count('danh_muc_san_pham') . ' danh muc.');

        // ==========================================================
        // BUOC 3: TAO THUOC TINH (cha + con) - mo rong de nhieu bien the
        // ==========================================================
        $sizeParentId = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh' => 'Kích thước',
            'trang_thai'     => true,
            'thuoc_tinh_cha_id' => null,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $mauParentId = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh' => 'Màu sắc',
            'trang_thai'     => true,
            'thuoc_tinh_cha_id' => null,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $sizeIds = [];
        foreach (['S', 'M', 'L', 'XL', 'XXL', '30', '31', '32', '36', '37', '38', '39', '40', '41', '42'] as $s) {
            $sizeIds[$s] = DB::table('thuoc_tinh_san_pham')->insertGetId([
                'ten_thuoc_tinh'   => $s,
                'trang_thai'       => true,
                'thuoc_tinh_cha_id'=> $sizeParentId,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $mauIds = [];
        foreach (['Đen', 'Trắng', 'Hồng', 'Xanh dương', 'Xanh lá', 'Vàng', 'Đỏ', 'Xám', 'Be', 'Nâu'] as $m) {
            $mauIds[$m] = DB::table('thuoc_tinh_san_pham')->insertGetId([
                'ten_thuoc_tinh'   => $m,
                'trang_thai'       => true,
                'thuoc_tinh_cha_id'=> $mauParentId,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $this->command->info('[3/7] Da tao thuoc tinh (Kich thuoc: ' . count($sizeIds) . ', Mau sac: ' . count($mauIds) . ').');

        // ==========================================================
        // BUOC 4: DINH NGHIA 100 SAN PHAM (gia tu 2000 VND)
        // ==========================================================
        $sanPhamDefs = $this->build100SanPhamDefs($dmIds);

        // ==========================================================
        // BUOC 5: INSERT SAN PHAM + BIEN_THE + DON_VI_QUY_DOI
        // ==========================================================
        $variantCount = 0;
        $unitCount    = 0;

        foreach ($sanPhamDefs as $p) {
            // 5a. Tao san_pham (cha)
            $productId = DB::table('san_pham')->insertGetId([
                'id_danh_muc'  => $dmIds[$p['dm']],
                'ten_san_pham'  => $p['ten'],
                'thuong_hieu'   => $p['thuong_hieu'],
                'mo_ta'         => $p['mo_ta'],
                'trang_thai'    => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            $giaVon = (int)($p['gia'] * 0.7);
            $maHangPrefix = strtoupper(substr($p['key'], 0, 6));

            if ($p['loai'] === 'thuong') {
                DB::table('bien_the_san_pham')->insert([
                    'product_id'        => $productId,
                    'ten_bien_the'      => null,
                    'la_don_vi'         => false,
                    'ten_don_vi'        => null,
                    'ma_hang'          => $maHangPrefix . '-001',
                    'ma_vach'          => '8934' . str_pad((string)$productId, 8, '0', STR_PAD_LEFT),
                    'gia_von'          => $giaVon,
                    'gia_ban'          => $p['gia'],
                    'so_luong_ton'     => 0,
                    'dinh_muc_toi_thieu' => $p['dinh_muc'],
                    'hinh_anh'         => null,
                    'thuoc_tinh_ids'   => null,
                    'trang_thai'      => true,
                    'created_at'       => $now,
                    'updated_at'      => $now,
                ]);
                $variantCount++;

            } elseif ($p['loai'] === 'don_vi') {
                $tenDonViCoBan = $p['ten_don_vi_co_ban'] ?? null;

                $variantId = DB::table('bien_the_san_pham')->insertGetId([
                    'product_id'        => $productId,
                    'ten_bien_the'     => null,
                    'la_don_vi'        => true,
                    'ten_don_vi'       => $tenDonViCoBan,
                    'ma_hang'          => $maHangPrefix . '-001',
                    'ma_vach'          => '8934' . str_pad((string)$productId, 8, '0', STR_PAD_LEFT),
                    'gia_von'          => $giaVon,
                    'gia_ban'          => $p['gia'],
                    'so_luong_ton'     => 0,
                    'dinh_muc_toi_thieu' => $p['dinh_muc'],
                    'hinh_anh'         => null,
                    'thuoc_tinh_ids'   => null,
                    'trang_thai'      => true,
                    'created_at'       => $now,
                    'updated_at'      => $now,
                ]);
                $variantCount++;

                foreach ($p['units'] as $idx => $u) {
                    if ((int)$u['ty_le'] <= 1) {
                        continue;
                    }
                    DB::table('don_vi_quy_doi')->insert([
                        'product_id'         => $productId,
                        'variant_id'         => $variantId,
                        'don_vi_chuan_id'    => null,
                        'ten_don_vi'         => $u['ten'],
                        'so_luong_san_pham_trong_don_vi' => (int)$u['ty_le'],
                        'ma_hang'           => $maHangPrefix . '-' . str_pad((string)($idx + 2), 3, '0', STR_PAD_LEFT),
                        'ma_vach'           => '8934'
                            . str_pad((string)$productId, 4, '0', STR_PAD_LEFT)
                            . str_pad((string)($idx + 2), 4, '0', STR_PAD_LEFT),
                        'gia_von_quy_doi'   => $giaVon * $u['ty_le'],
                        'gia_ban_quy_doi'   => $p['gia'] * $u['ty_le'],
                        'gia_ban_si'        => (int)($p['gia'] * $u['ty_le'] * 0.85),
                        'hinh_anh'          => null,
                        'la_don_vi_mac_dinh'=> false,
                        'created_at'        => $now,
                        'updated_at'       => $now,
                    ]);
                    $unitCount++;
                }

            } elseif ($p['loai'] === 'thuoc_tinh') {
                foreach ($p['combos'] as $idx => $combo) {
                    $tenBienThe = $combo['size'] . ' - ' . $combo['mau'];
                    $thuocTinhIds = [$sizeIds[$combo['size']], $mauIds[$combo['mau']]];

                    DB::table('bien_the_san_pham')->insert([
                        'product_id'        => $productId,
                        'ten_bien_the'     => $tenBienThe,
                        'la_don_vi'         => false,
                        'ten_don_vi'        => null,
                        'ma_hang'          => $maHangPrefix . '-' . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT),
                        'ma_vach'          => '8934'
                            . str_pad((string)$productId, 4, '0', STR_PAD_LEFT)
                            . str_pad((string)($idx + 1), 4, '0', STR_PAD_LEFT),
                        'gia_von'          => (int)($p['gia'] * 0.6),
                        'gia_ban'          => $p['gia'],
                        'so_luong_ton'     => 0,
                        'dinh_muc_toi_thieu' => $p['dinh_muc'],
                        'hinh_anh'         => null,
                        'thuoc_tinh_ids'   => json_encode($thuocTinhIds),
                        'trang_thai'      => true,
                        'created_at'       => $now,
                        'updated_at'      => $now,
                    ]);
                    $variantCount++;
                }
            }
        }

        $this->command->info('[4/7] Da tao ' . count($sanPhamDefs) . ' san pham voi ' . $variantCount . ' bien the va ' . $unitCount . ' don vi quy doi.');

        // ==========================================================
        // BUOC 6: TAO KHO HANG (lo hang + chi tiet lo hang)
        // ==========================================================
        $this->command->info('[5/7] Tao kho hang (lo hang, chi tiet lo hang)...');

        $nguoiDungs = DB::table('nguoi_dung')->pluck('id')->toArray();
        $idNguoiDung = $nguoiDungs[0] ?? null;

        $nccIds = DB::table('nha_cung_cap')->pluck('id')->toArray();
        if (empty($nccIds)) {
            $nccIds = [];
        }

        $nccIdx = 0;
        $loHangCount    = 0;
        $ctLoCount      = 0;
        $ctPhieuCount   = 0;

        $products = DB::table('san_pham')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        foreach ($products as $sp) {
            // Tong ton kho mac dinh - random trong khoang [20, 300]
            $tongTon = rand(20, 300);

            $variants = DB::table('bien_the_san_pham')
                ->where('product_id', $sp->id)
                ->orderBy('id')
                ->get();

            if ($variants->isEmpty()) {
                continue;
            }

            // Chia đều tồn kho cho các variant
            $tonPerVariant = (int)floor($tongTon / $variants->count());
            $tonRemainder  = $tongTon - ($tonPerVariant * $variants->count());

            $idNCC = !empty($nccIds) ? $nccIds[$nccIdx % count($nccIds)] : null;
            $nccIdx++;

            $ngayNhap = $now->copy()->subDays(rand(5, 60))->toDateString();
            $hanSuDung = $now->copy()->addDays(rand(90, 270))->toDateString();

            // Phieu nhap
            $idPhieu = DB::table('phieu')->insertGetId([
                'loai_phieu'      => 'Nhập hàng',
                'loai_phieu_enum' => 'nhap_mua_hang',
                'id_nguoi_dung'   => $idNguoiDung,
                'id_nha_cung_cap' => $idNCC,
                'id_hoa_don'      => null,
                'ghi_chu'         => "Nhập kho {$sp->ten_san_pham}",
                'created_at'      => $ngayNhap . ' 08:00:00',
                'updated_at'      => $ngayNhap . ' 08:00:00',
            ]);
            DB::table('phieu_nhap')->insert([
                'id_phieu'    => $idPhieu,
                'loai_nhap'   => 'mua_hang',
                'id_hoa_don'  => null,
                'ghi_chu'     => "Phiếu nhập {$sp->ten_san_pham}",
                'created_at'  => $ngayNhap . ' 08:00:00',
                'updated_at'   => $ngayNhap . ' 08:00:00',
            ]);

            // Lo hang
            $idLo = DB::table('lo_hang')->insertGetId([
                'id_phieu'          => $idPhieu,
                'id_nha_cung_cap'   => $idNCC,
                'ma_lo'             => 'LO-' . str_pad((string)$idPhieu, 5, '0', STR_PAD_LEFT),
                'ngay_nhap'         => $ngayNhap,
                'ghi_chu'           => "Lô nhập {$sp->ten_san_pham}",
                'created_at'        => $ngayNhap . ' 08:00:00',
                'updated_at'        => $ngayNhap . ' 08:00:00',
            ]);
            $loHangCount++;

            $giaVonTB = (float)$variants->avg('gia_von');

            $idCTLo = DB::table('chi_tiet_lo_hang')->insertGetId([
                'id_lo_hang'   => $idLo,
                'id_san_pham'  => $sp->id,
                'variant_id'   => $variants->first()->id,
                'so_luong_nhap'=> $tongTon,
                'so_luong_ton' => $tongTon,
                'gia_nhap'     => $giaVonTB,
                'han_su_dung'  => $hanSuDung,
                'created_at'   => $ngayNhap . ' 08:00:00',
                'updated_at'   => $ngayNhap . ' 08:00:00',
            ]);
            $ctLoCount++;

            foreach ($variants as $vIdx => $variant) {
                $slVariant = $tonPerVariant + ($vIdx === 0 ? $tonRemainder : 0);

                DB::table('bien_the_san_pham')
                    ->where('id', $variant->id)
                    ->update(['so_luong_ton' => $slVariant]);

                DB::table('chi_tiet_phieu')->insert([
                    'id_phieu'          => $idPhieu,
                    'id_san_pham'       => $sp->id,
                    'variant_id'       => $variant->id,
                    'id_lo_hang'       => $idLo,
                    'id_chi_tiet_lo_hang' => $idCTLo,
                    'so_luong'         => $slVariant,
                    'gia_nhap'         => $giaVonTB,
                    'ma_lo'            => 'LO-' . str_pad((string)$idPhieu, 5, '0', STR_PAD_LEFT),
                    'han_su_dung'      => $hanSuDung,
                    'so_luong_con_lai' => $slVariant,
                    'ghi_chu'          => "{$sp->ten_san_pham}" . ($variant->ten_bien_the ? " - {$variant->ten_bien_the}" : ''),
                    'created_at'       => $ngayNhap . ' 08:00:00',
                    'updated_at'       => $ngayNhap . ' 08:00:00',
                ]);
                $ctPhieuCount++;
            }
        }

        // ==========================================================
        // BUOC 7: DAT LAI AUTO-INCREMENT
        // ==========================================================
        $this->resetAutoIncrement(['danh_muc_san_pham', 'san_pham', 'bien_the_san_pham', 'don_vi_quy_doi', 'lo_hang', 'chi_tiet_lo_hang']);

        $this->command->info('[6/7] Reset auto increment xong.');

        // ==========================================================
        // TONG KET
        // ==========================================================
        $this->command->info('[7/7] SanPhamSeeder hoan tat!');
        $this->table('danh_muc_san_pham');
        $this->table('san_pham');
        $this->table('bien_the_san_pham');
        $this->table('don_vi_quy_doi');
        $this->table('lo_hang');
        $this->table('chi_tiet_lo_hang');
        $this->table('chi_tiet_phieu');

        $this->command->line('');
        $this->command->info('TONG KET:');
        $this->command->line("  • San pham      : " . $this->count('san_pham'));
        $this->command->line("  • Bien the       : " . $this->count('bien_the_san_pham'));
        $this->command->line("  • Don vi QD      : " . $this->count('don_vi_quy_doi'));
        $this->command->line("  • Lo hang        : " . $loHangCount);
        $this->command->line("  • Chi tiet lo    : " . $ctLoCount);
        $this->command->line("  • Chi tiet phieu : " . $ctPhieuCount);
    }

    // ─── Helper methods ────────────────────────────────────────

    /**
     * Tạo danh sách 100 sản phẩm với giá từ 2000 VND.
     * - Mix 3 loại: thuong / don_vi / thuoc_tinh
     * - Sản phẩm don_vi có nhiều đơn vị quy đổi (Thùng/Lốc/Kiện)
     * - Sản phẩm thuoc_tinh có nhiều biến thể (size × màu)
     */
    private function build100SanPhamDefs(array $dmIds): array
    {
        $defs = [];

        // ============== THỰC PHẨM (18 sp) ==============
        $defs[] = [
            'key' => 'gaost25_5', 'ten' => 'Gạo ST25 túi 5kg', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'ST25', 'mo_ta' => 'Gạo ST25 hữu cơ túi 5kg, hạt dài, thơm dẻo.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Túi', 'gia' => 139000, 'dinh_muc' => 8,
            'units' => [
                ['ten' => 'Bao', 'ty_le' => 4, 'la_mac_dinh' => false],
                ['ten' => 'Tấn', 'ty_le' => 200, 'la_mac_dinh' => false],
            ],
        ];
        $defs[] = [
            'key' => 'gao_nang', 'ten' => 'Gạo Nàng Hoa 10kg', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Nàng Hoa', 'mo_ta' => 'Gạo Nàng Hoa 10kg, hạt thon dài, dẻo cơm.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Túi', 'gia' => 230000, 'dinh_muc' => 5,
            'units' => [['ten' => 'Bao', 'ty_le' => 2, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'mi_hai_vien', 'ten' => 'Mì Hải Vị 500g', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Hải Vị', 'mo_ta' => 'Mì sợi vàng Hải Vị gói 500g.',
            'loai' => 'thuong', 'gia' => 18000, 'dinh_muc' => 20,
        ];
        $defs[] = [
            'key' => 'bot_mi', 'ten' => 'Bột mì Meizan 1kg', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Meizan', 'mo_ta' => 'Bột mì đa dụng Meizan gói 1kg, làm bánh, trộn chiên.',
            'loai' => 'thuong', 'gia' => 22000, 'dinh_muc' => 15,
        ];
        $defs[] = [
            'key' => 'duong_cat', 'ten' => 'Đường cát trắng 1kg', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Biên Hòa', 'mo_ta' => 'Đường cát trắng tinh luyện Biên Hòa gói 1kg.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Gói', 'gia' => 24000, 'dinh_muc' => 25,
            'units' => [['ten' => 'Thùng', 'ty_le' => 20, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'muoi_ot', 'ten' => 'Muối ớt chanh 100g', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Lạc Việt', 'mo_ta' => 'Muối chấm ớt chanh Lạc Việt hũ 100g.',
            'loai' => 'thuong', 'gia' => 12000, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'nuoc_mam', 'ten' => 'Nước mắm Nam Ngư 500ml', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Nam Ngư', 'mo_ta' => 'Nước mắm cốt Nam Ngư chai 500ml, độ đạm 10N.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Chai', 'gia' => 35000, 'dinh_muc' => 20,
            'units' => [['ten' => 'Thùng', 'ty_le' => 12, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'dau_an', 'ten' => 'Dầu ăn Simply 1L', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Simply', 'mo_ta' => 'Dầu ăn tinh luyện Simply chai 1L.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Chai', 'gia' => 48000, 'dinh_muc' => 15,
            'units' => [
                ['ten' => 'Thùng', 'ty_le' => 12, 'la_mac_dinh' => false],
                ['ten' => 'Can', 'ty_le' => 5, 'la_mac_dinh' => false],
            ],
        ];
        $defs[] = [
            'key' => 'sua_vinamilk', 'ten' => 'Sữa tươi Vinamilk 1L', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Vinamilk', 'mo_ta' => 'Sữa tươi tiệt trùng Vinamilk 100% sữa tươi hộp 1L.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Hộp', 'gia' => 28000, 'dinh_muc' => 15,
            'units' => [['ten' => 'Thùng', 'ty_le' => 12, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'sua_th_true', 'ten' => 'Sữa tươi TH true MILK 1L', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'TH true MILK', 'mo_ta' => 'Sữa tươi sạch TH true MILK hộp 1L.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Hộp', 'gia' => 35000, 'dinh_muc' => 15,
            'units' => [['ten' => 'Thùng', 'ty_le' => 12, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'yogurt_d', 'ten' => 'Sữa chua uống Dalat 200ml', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Dalat', 'mo_ta' => 'Sữa chua uống hương dâu Dalat hộp 200ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Hộp', 'gia' => 8500, 'dinh_muc' => 30,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'trung_gà', 'ten' => 'Trứng gà tươi hộp 10 quả', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Ba Huân', 'mo_ta' => 'Trứng gà tươi Ba Huân hộp 10 quả.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Hộp', 'gia' => 28000, 'dinh_muc' => 20,
            'units' => [['ten' => 'Khay', 'ty_le' => 6, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'thit_heo', 'ten' => 'Thịt heo xay 500g', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'CP', 'mo_ta' => 'Thịt heo xay sẵn đông lạnh CP khay 500g.',
            'loai' => 'thuong', 'gia' => 45000, 'dinh_muc' => 12,
        ];
        $defs[] = [
            'key' => 'ca_hai', 'ten' => 'Cá hồi phi lê 200g', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Auchan', 'mo_ta' => 'Cá hồi phi lê đông lạnh khay 200g.',
            'loai' => 'thuong', 'gia' => 95000, 'dinh_muc' => 8,
        ];
        $defs[] = [
            'key' => 'tom_the', 'ten' => 'Tôm thẻ đông lạnh 500g', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Auchan', 'mo_ta' => 'Tôm thẻ đông lạnh khay 500g, đã sơ chế.',
            'loai' => 'thuong', 'gia' => 110000, 'dinh_muc' => 8,
        ];
        $defs[] = [
            'key' => 'rau_muong', 'ten' => 'Rau muống 500g', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'VietGAP', 'mo_ta' => 'Rau muống VietGAP bó 500g, tươi mới mỗi ngày.',
            'loai' => 'thuong', 'gia' => 8000, 'dinh_muc' => 25,
        ];
        $defs[] = [
            'key' => 'ca_rot', 'ten' => 'Cà rốt Đà Lạt 500g', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'VietGAP', 'mo_ta' => 'Cà rốt Đà Lạt gói 500g, giòn ngọt.',
            'loai' => 'thuong', 'gia' => 12000, 'dinh_muc' => 20,
        ];
        $defs[] = [
            'key' => 'kho_lua', 'ten' => 'Khô lúa mì 500g', 'dm' => 'Thực phẩm',
            'thuong_hieu' => 'Vedan', 'mo_ta' => 'Khô lúa mì (nui) Vedan gói 500g.',
            'loai' => 'thuong', 'gia' => 16500, 'dinh_muc' => 20,
        ];

        // ============== ĐỒ UỐNG (18 sp) ==============
        $defs[] = [
            'key' => 'lavie_500', 'ten' => 'Nước khoáng Lavie 500ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Lavie', 'mo_ta' => 'Nước khoáng thiên nhiên Lavie chai 500ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Chai', 'gia' => 5000, 'dinh_muc' => 30,
            'units' => [
                ['ten' => 'Lốc', 'ty_le' => 12, 'la_mac_dinh' => false],
                ['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false],
            ],
        ];
        $defs[] = [
            'key' => 'aqua_500', 'ten' => 'Nước khoáng Aquafina 500ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Aquafina', 'mo_ta' => 'Nước khoáng tinh khiết Aquafina chai 500ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Chai', 'gia' => 5500, 'dinh_muc' => 30,
            'units' => [
                ['ten' => 'Lốc', 'ty_le' => 12, 'la_mac_dinh' => false],
                ['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false],
            ],
        ];
        $defs[] = [
            'key' => 'dasani_500', 'ten' => 'Nước lọc Dasani 500ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Dasani', 'mo_ta' => 'Nước lọc tinh khiết Dasani chai 500ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Chai', 'gia' => 6000, 'dinh_muc' => 30,
            'units' => [
                ['ten' => 'Lốc', 'ty_le' => 12, 'la_mac_dinh' => false],
                ['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false],
            ],
        ];
        $defs[] = [
            'key' => 'pepsi_lon', 'ten' => 'Pepsi lon 330ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Pepsi', 'mo_ta' => 'Nước ngọt có gas Pepsi lon 330ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Lon', 'gia' => 11000, 'dinh_muc' => 25,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'coca_lon', 'ten' => 'Coca Cola lon 330ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Coca Cola', 'mo_ta' => 'Nước ngọt có gas Coca Cola lon 330ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Lon', 'gia' => 11000, 'dinh_muc' => 25,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'sting_lon', 'ten' => 'Sting lon 330ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Sting', 'mo_ta' => 'Nước tăng lực Sting dâu lon 330ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Lon', 'gia' => 10000, 'dinh_muc' => 25,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'redbull_lon', 'ten' => 'Red Bull lon 250ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Red Bull', 'mo_ta' => 'Nước tăng lực Red Bull lon 250ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Lon', 'gia' => 15000, 'dinh_muc' => 20,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'heineken', 'ten' => 'Bia Heineken lon 330ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Heineken', 'mo_ta' => 'Bia Heineken lon 330ml, nhập khẩu Hà Lan.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Lon', 'gia' => 14000, 'dinh_muc' => 20,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'bia_sai', 'ten' => 'Bia Sài Gòn lon 330ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Sabeco', 'mo_ta' => 'Bia Sài Gòn Export lon 330ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Lon', 'gia' => 11000, 'dinh_muc' => 25,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'bia_333', 'ten' => 'Bia 333 lon 330ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => '333', 'mo_ta' => 'Bia 333 Export lon 330ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Lon', 'gia' => 11500, 'dinh_muc' => 25,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'nuoc_cam', 'ten' => 'Nước cam ép Vfresh 1L', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Vfresh', 'mo_ta' => 'Nước cam ép 100% Vfresh hộp 1L.',
            'loai' => 'thuong', 'gia' => 32000, 'dinh_muc' => 12,
        ];
        $defs[] = [
            'key' => 'nuoc_tao', 'ten' => 'Nước táo ép Vfresh 1L', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Vfresh', 'mo_ta' => 'Nước táo ép 100% Vfresh hộp 1L.',
            'loai' => 'thuong', 'gia' => 35000, 'dinh_muc' => 12,
        ];
        $defs[] = [
            'key' => 'tra_xanh', 'ten' => 'Trà xanh C2 hương chanh 500ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'C2', 'mo_ta' => 'Trà xanh C2 hương chanh chai 500ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Chai', 'gia' => 8500, 'dinh_muc' => 25,
            'units' => [
                ['ten' => 'Lốc', 'ty_le' => 12, 'la_mac_dinh' => false],
                ['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false],
            ],
        ];
        $defs[] = [
            'key' => 'tra_olong', 'ten' => 'Trà ô long Tea Plus 500ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Tea Plus', 'mo_ta' => 'Trà ô long Tea Plus chai 500ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Chai', 'gia' => 9500, 'dinh_muc' => 25,
            'units' => [
                ['ten' => 'Lốc', 'ty_le' => 12, 'la_mac_dinh' => false],
                ['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false],
            ],
        ];
        $defs[] = [
            'key' => 'cf_coffee', 'ten' => 'Cà phê lon Birdy 185ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Birdy', 'mo_ta' => 'Cà phê lon Birdy 185ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Lon', 'gia' => 9500, 'dinh_muc' => 25,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'cf_hoa_tan', 'ten' => 'Cà phê hòa tan G7 3in1 hộp 20 gói', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'G7', 'mo_ta' => 'Cà phê hòa tan G7 3in1 hộp 20 gói.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Hộp', 'gia' => 45000, 'dinh_muc' => 12,
            'units' => [['ten' => 'Thùng', 'ty_le' => 12, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'cf_phin', 'ten' => 'Cà phê phin Trung Nguyên 250g', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Trung Nguyên', 'mo_ta' => 'Cà phê phin Trung Nguyên gói 250g.',
            'loai' => 'thuong', 'gia' => 55000, 'dinh_muc' => 10,
        ];
        $defs[] = [
            'key' => 'mirinda', 'ten' => 'Mirinda lon 330ml', 'dm' => 'Đồ uống',
            'thuong_hieu' => 'Mirinda', 'mo_ta' => 'Nước ngọt Mirinda hương cam lon 330ml.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Lon', 'gia' => 10500, 'dinh_muc' => 25,
            'units' => [['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false]],
        ];

        // ============== BÁNH KẸO (16 sp) ==============
        $defs[] = [
            'key' => 'oishi_bo', 'ten' => 'Snack Oishi vị bò 45g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Oishi', 'mo_ta' => 'Snack Oishi vị bò nướng gói 45g.',
            'loai' => 'thuong', 'gia' => 10000, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'oishi_tom', 'ten' => 'Snack Oishi tôm 45g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Oishi', 'mo_ta' => 'Snack Oishi vị tôm gói 45g.',
            'loai' => 'thuong', 'gia' => 10000, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'lay_vi', 'ten' => 'Snack Lay\'s vị khoai tây 50g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Lay\'s', 'mo_ta' => 'Snack khoai tây Lay\'s vị tự nhiên gói 50g.',
            'loai' => 'thuong', 'gia' => 12000, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'pringles', 'ten' => 'Snack Pringles hộp 110g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Pringles', 'mo_ta' => 'Snack khoai tây Pringles vị kem chua hộp 110g.',
            'loai' => 'thuong', 'gia' => 35000, 'dinh_muc' => 15,
        ];
        $defs[] = [
            'key' => 'kitkat', 'ten' => 'KitKat thanh 35g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Nestlé', 'mo_ta' => 'Kẹo socola KitKat thanh 35g.',
            'loai' => 'thuong', 'gia' => 14000, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'snickers', 'ten' => 'Snickers thanh 50g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Mars', 'mo_ta' => 'Kẹo socola Snickers thanh 50g.',
            'loai' => 'thuong', 'gia' => 15000, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'm_m_s', 'ten' => 'Kẹo M&M\'s hộp 45g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'M&M\'s', 'mo_ta' => 'Kẹo socola M&M\'s hộp 45g.',
            'loai' => 'thuong', 'gia' => 22000, 'dinh_muc' => 25,
        ];
        $defs[] = [
            'key' => 'orion_chocopie', 'ten' => 'Bánh ChocoPie Orion 12 cái', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Orion', 'mo_ta' => 'Bánh ChocoPie Orion hộp 12 cái.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Hộp', 'gia' => 35000, 'dinh_muc' => 15,
            'units' => [['ten' => 'Thùng', 'ty_le' => 12, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'banh_my', 'ten' => 'Bánh mì sandwich Kao 380g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Kao', 'mo_ta' => 'Bánh mì sandwich Kao gói 380g.',
            'loai' => 'thuong', 'gia' => 16000, 'dinh_muc' => 20,
        ];
        $defs[] = [
            'key' => 'banh_quy', 'ten' => 'Bánh quy Cosy gói 144g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Cosy', 'mo_ta' => 'Bánh quy sữa Cosy gói 144g.',
            'loai' => 'thuong', 'gia' => 18500, 'dinh_muc' => 25,
        ];
        $defs[] = [
            'key' => 'keo_dynamit', 'ten' => 'Kẹo Dynamite hũ 100 viên', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Bibica', 'mo_ta' => 'Kẹo Dynamite hũ nhỏ 100 viên.',
            'loai' => 'thuong', 'gia' => 16000, 'dinh_muc' => 25,
        ];
        $defs[] = [
            'key' => 'keo_alpenliebe', 'ten' => 'Kẹo Alpenliebe gói 100g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Perfetti', 'mo_ta' => 'Kẹo caramel Alpenliebe gói 100g.',
            'loai' => 'thuong', 'gia' => 12500, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'oreo', 'ten' => 'Bánh Oreo hộp 137g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Oreo', 'mo_ta' => 'Bánh Oreo sandwich kem vani hộp 137g.',
            'loai' => 'thuong', 'gia' => 22000, 'dinh_muc' => 20,
        ];
        $defs[] = [
            'key' => 'pocky', 'ten' => 'Bánh que Pocky socola 47g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Glico', 'mo_ta' => 'Bánh que Pocky phủ socola hộp 47g.',
            'loai' => 'thuong', 'gia' => 18000, 'dinh_muc' => 25,
        ];
        $defs[] = [
            'key' => 'meiji', 'ten' => 'Socola Meiji thanh 56g', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Meiji', 'mo_ta' => 'Socola sữa Meiji thanh 56g.',
            'loai' => 'thuong', 'gia' => 25000, 'dinh_muc' => 25,
        ];
        $defs[] = [
            'key' => 'vinacafe', 'ten' => 'Cà phê Vinacafe 3in1 hộp 24 gói', 'dm' => 'Bánh kẹo',
            'thuong_hieu' => 'Vinacafe', 'mo_ta' => 'Cà phê hòa tan Vinacafe 3in1 hộp 24 gói.',
            'loai' => 'thuong', 'gia' => 38000, 'dinh_muc' => 15,
        ];

        // ============== MÌ GÓI (16 sp) ==============
        $defs[] = [
            'key' => 'omachi_tom', 'ten' => 'Mì Omachi tôm chua cay 85g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Omachi', 'mo_ta' => 'Mì ăn liền Omachi hương vị tôm chua cay gói 85g.',
            'loai' => 'thuong', 'gia' => 8500, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'omachi_bo', 'ten' => 'Mì Omachi bò hầm 85g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Omachi', 'mo_ta' => 'Mì ăn liền Omachi hương vị bò hầm gói 85g.',
            'loai' => 'thuong', 'gia' => 8500, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'hena_hai', 'ten' => 'Mì Hảo Hải tôm chua cay 75g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Acecook', 'mo_ta' => 'Mì Hảo Hải hương vị tôm chua cay gói 75g.',
            'loai' => 'thuong', 'gia' => 7500, 'dinh_muc' => 35,
        ];
        $defs[] = [
            'key' => 'hena_bo', 'ten' => 'Mì Hảo Hải bò hầm 75g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Acecook', 'mo_ta' => 'Mì Hảo Hải hương vị bò hầm gói 75g.',
            'loai' => 'thuong', 'gia' => 7500, 'dinh_muc' => 35,
        ];
        $defs[] = [
            'key' => 'hena_ga', 'ten' => 'Mì Hảo Hải gà 75g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Acecook', 'mo_ta' => 'Mì Hảo Hải hương vị gà hầm gói 75g.',
            'loai' => 'thuong', 'gia' => 7500, 'dinh_muc' => 35,
        ];
        $defs[] = [
            'key' => 'modern_bo', 'ten' => 'Mì Modern bò 85g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Uniben', 'mo_ta' => 'Mì Modern hương vị bò gói 85g.',
            'loai' => 'thuong', 'gia' => 9000, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'cung_dinh', 'ten' => 'Mì Cung Đình ăn liền 80g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Vina Acecook', 'mo_ta' => 'Mì Cung Đình thập cẩm gói 80g.',
            'loai' => 'thuong', 'gia' => 8200, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'kokomi', 'ten' => 'Mì Kokomi tôm 80g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Vifon', 'mo_ta' => 'Mì Kokomi hương vị tôm gói 80g.',
            'loai' => 'thuong', 'gia' => 7000, 'dinh_muc' => 35,
        ];
        $defs[] = [
            'key' => 'mi_3_mien', 'ten' => 'Mì 3 Miền thập cẩm 80g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Colusa', 'mo_ta' => 'Mì 3 Miền hương vị thập cẩm gói 80g.',
            'loai' => 'thuong', 'gia' => 7800, 'dinh_muc' => 35,
        ];
        $defs[] = [
            'key' => 'mi_gau_do', 'ten' => 'Mì Gấu Đỏ bò 80g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Gấu Đỏ', 'mo_ta' => 'Mì Gấu Đỏ hương vị bò gói 80g.',
            'loai' => 'thuong', 'gia' => 6500, 'dinh_muc' => 40,
        ];
        $defs[] = [
            'key' => 'mi_nissin', 'ten' => 'Mì Nissin Cup 67g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Nissin', 'mo_ta' => 'Mì ly Nissin tôm chua cay cốc 67g.',
            'loai' => 'thuong', 'gia' => 15000, 'dinh_muc' => 25,
        ];
        $defs[] = [
            'key' => 'mi_cay_han', 'ten' => 'Mì cay Hàn Quốc Ottogi 120g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Ottogi', 'mo_ta' => 'Mì cay Hàn Quốc Ottogi gói 120g.',
            'loai' => 'thuong', 'gia' => 18000, 'dinh_muc' => 25,
        ];
        $defs[] = [
            'key' => 'pho_bo', 'ten' => 'Phở bò Vifon 65g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Vifon', 'mo_ta' => 'Phở bò ăn liền Vifon gói 65g.',
            'loai' => 'thuong', 'gia' => 8500, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'hu_tieu', 'ten' => 'Hủ tiếu Nam Vang 65g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Vifon', 'mo_ta' => 'Hủ tiếu Nam Vang ăn liền gói 65g.',
            'loai' => 'thuong', 'gia' => 8800, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'bun_bo', 'ten' => 'Bún bò Huế ăn liền 80g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Vifon', 'mo_ta' => 'Bún bò Huế ăn liền Vifon gói 80g.',
            'loai' => 'thuong', 'gia' => 9500, 'dinh_muc' => 30,
        ];
        $defs[] = [
            'key' => 'mi_hao_thuong', 'ten' => 'Mì Siukay Vifon 120g', 'dm' => 'Mì gói',
            'thuong_hieu' => 'Vifon', 'mo_ta' => 'Mì Siukay (khô) Vifon gói 120g.',
            'loai' => 'thuong', 'gia' => 13500, 'dinh_muc' => 25,
        ];

        // ============== CHĂM SÓC CÁ NHÂN (16 sp) ==============
        $defs[] = [
            'key' => 'clear_men', 'ten' => 'Dầu gội Clear Men 200ml', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Clear', 'mo_ta' => 'Dầu gội Clear Men bạc hà mát lạnh chai 200ml.',
            'loai' => 'thuong', 'gia' => 65000, 'dinh_muc' => 10,
        ];
        $defs[] = [
            'key' => 'clear_nu', 'ten' => 'Dầu gội Clear hương hoa 200ml', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Clear', 'mo_ta' => 'Dầu gội Clear nữ hương hoa chai 200ml.',
            'loai' => 'thuong', 'gia' => 65000, 'dinh_muc' => 10,
        ];
        $defs[] = [
            'key' => 'suns_1l', 'ten' => 'Dầu gội Sunsilk 1L', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Sunsilk', 'mo_ta' => 'Dầu gội Sunsilk mềm mượt chai 1L.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Chai', 'gia' => 135000, 'dinh_muc' => 8,
            'units' => [['ten' => 'Thùng', 'ty_le' => 12, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'head_shoulders', 'ten' => 'Dầu gội Head & Shoulders 200ml', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Head & Shoulders', 'mo_ta' => 'Dầu gội Head & Shoulders bạc hà mát lạnh chai 200ml.',
            'loai' => 'thuong', 'gia' => 72000, 'dinh_muc' => 10,
        ];
        $defs[] = [
            'key' => 'colgate_tooth', 'ten' => 'Kem đánh răng Colgate 150g', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Colgate', 'mo_ta' => 'Kem đánh răng Colgate bảo vệ 100% tuýp 150g.',
            'loai' => 'thuong', 'gia' => 32000, 'dinh_muc' => 12,
        ];
        $defs[] = [
            'key' => 'ps_tooth', 'ten' => 'Kem đánh răng P/S 150g', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'P/S', 'mo_ta' => 'Kem đánh răng P/S bảo vệ 100% tuýp 150g.',
            'loai' => 'thuong', 'gia' => 28000, 'dinh_muc' => 12,
        ];
        $defs[] = [
            'key' => 'colgate_brush', 'ten' => 'Bàn chải đánh răng Colgate', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Colgate', 'mo_ta' => 'Bàn chải đánh răng Colgate lông mềm cho người lớn.',
            'loai' => 'thuong', 'gia' => 25000, 'dinh_muc' => 12,
        ];
        $defs[] = [
            'key' => 'listerine', 'ten' => 'Nước súc miệng Listerine 750ml', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Listerine', 'mo_ta' => 'Nước súc miệng Listerine Cool Mint chai 750ml.',
            'loai' => 'thuong', 'gia' => 95000, 'dinh_muc' => 8,
        ];
        $defs[] = [
            'key' => 'lifesoy', 'ten' => 'Xà phòng Lifebuoy 90g', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Lifebuoy', 'mo_ta' => 'Xà phòng Lifebuoy đỏ bảo vệ 90g.',
            'loai' => 'thuong', 'gia' => 9500, 'dinh_muc' => 25,
        ];
        $defs[] = [
            'key' => 'sua_tam', 'ten' => 'Sữa tắm Dove 250ml', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Dove', 'mo_ta' => 'Sữa tắm Dove dưỡng ẩm chai 250ml.',
            'loai' => 'thuong', 'gia' => 75000, 'dinh_muc' => 10,
        ];
        $defs[] = [
            'key' => 'lan_khu_mui', 'ten' => 'Lăn khử mùi Rexona 50ml', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Rexona', 'mo_ta' => 'Lăn khử mùi Rexona Men chai 50ml.',
            'loai' => 'thuong', 'gia' => 38000, 'dinh_muc' => 12,
        ];
        $defs[] = [
            'key' => 'giay_ve_sinh', 'ten' => 'Giấy vệ sinh Pulppy 10 cuộn', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Pulppy', 'mo_ta' => 'Giấy vệ sinh Pulppy gói 10 cuộn.',
            'loai' => 'thuong', 'gia' => 28000, 'dinh_muc' => 15,
        ];
        $defs[] = [
            'key' => 'bang_ve_sinh', 'ten' => 'Băng vệ sinh Diana 8 miếng', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Diana', 'mo_ta' => 'Băng vệ sinh Diana ban ngày gói 8 miếng.',
            'loai' => 'thuong', 'gia' => 18500, 'dinh_muc' => 20,
        ];
        $defs[] = [
            'key' => 'bot_giat', 'ten' => 'Bột giặt Omo 800g', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Omo', 'mo_ta' => 'Bột giặt Omo hương Comfort túi 800g.',
            'loai' => 'don_vi', 'ten_don_vi_co_ban' => 'Túi', 'gia' => 65000, 'dinh_muc' => 10,
            'units' => [['ten' => 'Thùng', 'ty_le' => 10, 'la_mac_dinh' => false]],
        ];
        $defs[] = [
            'key' => 'nuoc_xa', 'ten' => 'Nước xả vải Comfort 800ml', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Comfort', 'mo_ta' => 'Nước xả vải Comfort hương hoa chai 800ml.',
            'loai' => 'thuong', 'gia' => 55000, 'dinh_muc' => 12,
        ];
        $defs[] = [
            'key' => 'khan_uot', 'ten' => 'Khăn ướt Bobby 80 miếng', 'dm' => 'Chăm sóc cá nhân',
            'thuong_hieu' => 'Bobby', 'mo_ta' => 'Khăn ướt Bobby hương tự nhiên gói 80 miếng.',
            'loai' => 'thuong', 'gia' => 28000, 'dinh_muc' => 20,
        ];

        // ============== THỜI TRANG (16 sp - thuoc_tinh) ==============
        $sizesApparel = ['M', 'L', 'XL'];
        $mauAo = ['Đen', 'Trắng', 'Xanh dương', 'Xám'];
        $mauGiay = ['Đen', 'Trắng', 'Hồng', 'Xanh dương'];
        $sizesGiay = ['38', '39', '40', '41'];

        $defs[] = [
            'key' => 'ao_thun_nam', 'ten' => 'Áo thun nam cổ tròn', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMart', 'mo_ta' => 'Áo thun nam cổ tròn chất cotton 100%, co giãn tốt.',
            'loai' => 'thuoc_tinh', 'gia' => 120000, 'dinh_muc' => 5,
            'combos' => $this->combos($sizesApparel, $mauAo),
        ];
        $defs[] = [
            'key' => 'ao_polo_nam', 'ten' => 'Áo polo nam SmartMart', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMart', 'mo_ta' => 'Áo polo nam cá tính, chất cotton cá sấu.',
            'loai' => 'thuoc_tinh', 'gia' => 165000, 'dinh_muc' => 5,
            'combos' => $this->combos(['M', 'L', 'XL'], ['Trắng', 'Xanh dương', 'Đen']),
        ];
        $defs[] = [
            'key' => 'ao_so_mi_nu', 'ten' => 'Áo sơ mi nữ công sở', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMart', 'mo_ta' => 'Áo sơ mi nữ công sở chất lụa, form ôm nhẹ.',
            'loai' => 'thuoc_tinh', 'gia' => 195000, 'dinh_muc' => 5,
            'combos' => $this->combos(['S', 'M', 'L'], ['Trắng', 'Hồng', 'Xanh dương']),
        ];
        $defs[] = [
            'key' => 'quan_jean_nam', 'ten' => 'Quần jean nam slim-fit', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMart', 'mo_ta' => 'Quần jean nam ống ôm co giãn, chất denim cao cấp.',
            'loai' => 'thuoc_tinh', 'gia' => 285000, 'dinh_muc' => 4,
            'combos' => $this->combos(['30', '31', '32'], ['Đen', 'Xanh dương', 'Xám']),
        ];
        $defs[] = [
            'key' => 'quan_kaki_nu', 'ten' => 'Quần kaki nữ ống rộng', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMart', 'mo_ta' => 'Quần kaki nữ ống rộng, chất vải dày dặn.',
            'loai' => 'thuoc_tinh', 'gia' => 235000, 'dinh_muc' => 4,
            'combos' => $this->combos(['S', 'M', 'L'], ['Be', 'Nâu', 'Đen']),
        ];
        $defs[] = [
            'key' => 'ao_khoac_nam', 'ten' => 'Áo khoác nam chống nước', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMart', 'mo_ta' => 'Áo khoác nam 2 lớp chống nước nhẹ, có mũ.',
            'loai' => 'thuoc_tinh', 'gia' => 385000, 'dinh_muc' => 3,
            'combos' => $this->combos(['M', 'L', 'XL'], ['Đen', 'Xám', 'Xanh dương']),
        ];
        $defs[] = [
            'key' => 'giay_the_thao_nam', 'ten' => 'Giày thể thao nam', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartSport', 'mo_ta' => 'Giày thể thao nam đế mềm, thoáng khí, phù hợp chạy bộ.',
            'loai' => 'thuoc_tinh', 'gia' => 385000, 'dinh_muc' => 3,
            'combos' => $this->combos(['39', '40', '41', '42'], ['Đen', 'Trắng', 'Xám']),
        ];
        $defs[] = [
            'key' => 'giay_the_thao_nu', 'ten' => 'Giày thể thao nữ', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartSport', 'mo_ta' => 'Giày thể thao nữ đế mềm, thoáng khí, phù hợp chạy bộ.',
            'loai' => 'thuoc_tinh', 'gia' => 350000, 'dinh_muc' => 3,
            'combos' => $this->combos($sizesGiay, $mauGiay),
        ];
        $defs[] = [
            'key' => 'giay_sandal', 'ten' => 'Dép sandal nam Quiksilver', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartSport', 'mo_ta' => 'Dép sandal nam đế chống trượt, quai da bền.',
            'loai' => 'thuoc_tinh', 'gia' => 220000, 'dinh_muc' => 4,
            'combos' => $this->combos(['39', '40', '41', '42'], ['Đen', 'Nâu']),
        ];
        $defs[] = [
            'key' => 'tui_xach_nu', 'ten' => 'Túi xách nữ thời trang', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartLady', 'mo_ta' => 'Túi xách nữ da PU cao cấp, ngăn đựng laptop 14 inch.',
            'loai' => 'thuoc_tinh', 'gia' => 285000, 'dinh_muc' => 4,
            'combos' => $this->combos(['S', 'M'], ['Đen', 'Nâu', 'Hồng', 'Be']),
        ];
        $defs[] = [
            'key' => 'vi_nam', 'ten' => 'Ví nam da bò thật', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMan', 'mo_ta' => 'Ví nam da bò thật, nhiều ngăn tiện lợi.',
            'loai' => 'thuoc_tinh', 'gia' => 195000, 'dinh_muc' => 5,
            'combos' => $this->combos(['S', 'M'], ['Đen', 'Nâu']),
        ];
        $defs[] = [
            'key' => 'mu_luoi_trai', 'ten' => 'Mũ lưỡi trai nam nữ', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartSport', 'mo_ta' => 'Mũ lưỡi trai unisex, vải cotton thoáng mát.',
            'loai' => 'thuoc_tinh', 'gia' => 85000, 'dinh_muc' => 8,
            'combos' => $this->combos(['M', 'L'], ['Đen', 'Trắng', 'Xanh dương', 'Đỏ']),
        ];
        $defs[] = [
            'key' => 'tat_nam', 'ten' => 'Vớ nam cổ ngắn', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMan', 'mo_ta' => 'Vớ nam cổ ngắn, chất cotton khử mùi.',
            'loai' => 'thuoc_tinh', 'gia' => 25000, 'dinh_muc' => 20,
            'combos' => $this->combos(['S', 'M', 'L'], ['Đen', 'Trắng', 'Xám']),
        ];
        $defs[] = [
            'key' => 'ao_thun_nu', 'ten' => 'Áo thun nữ form rộng', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMart', 'mo_ta' => 'Áo thun nữ form rộng, chất cotton 100%, in hình.',
            'loai' => 'thuoc_tinh', 'gia' => 110000, 'dinh_muc' => 6,
            'combos' => $this->combos(['S', 'M', 'L'], ['Trắng', 'Hồng', 'Vàng', 'Xanh lá']),
        ];
        $defs[] = [
            'key' => 'quan_short_nam', 'ten' => 'Quần short nam thể thao', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartSport', 'mo_ta' => 'Quần short nam thể thao, vải polyester thoáng mát.',
            'loai' => 'thuoc_tinh', 'gia' => 135000, 'dinh_muc' => 6,
            'combos' => $this->combos(['M', 'L', 'XL'], ['Đen', 'Xám', 'Xanh dương']),
        ];
        $defs[] = [
            'key' => 'dep_nam', 'ten' => 'Dép tổ ong nam', 'dm' => 'Thời trang',
            'thuong_hieu' => 'SmartMart', 'mo_ta' => 'Dép tổ ong nam đế chống trơn, quai ngang.',
            'loai' => 'thuoc_tinh', 'gia' => 55000, 'dinh_muc' => 10,
            'combos' => $this->combos(['39', '40', '41', '42'], ['Đen', 'Xanh dương']),
        ];

        // Dam bao so luong chinh xac = 100
        $count = count($defs);
        if ($count !== 100) {
            $this->command->warn("Canh bao: SanPhamSeeder dinh nghia {$count} sp, can chinh lai cho = 100.");
        }

        return $defs;
    }

    /**
     * Tạo tổ hợp size × màu (cartesian product, giới hạn số lượng).
     */
    private function combos(array $sizes, array $maus): array
    {
        $result = [];
        foreach ($sizes as $size) {
            foreach ($maus as $mau) {
                $result[] = ['size' => $size, 'mau' => $mau];
            }
        }
        return $result;
    }

    private function count(string $table): int
    {
        return DB::table($table)->count();
    }

    private function table(string $name): void
    {
        $n = $this->count($name);
        $this->command->line("    {$name}: {$n} rows");
    }

    private function resetAutoIncrement(array $tables): void
    {
        foreach ($tables as $table) {
            try {
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
            } catch (\Throwable) {
                // Skip if not applicable (SQLite or no AUTO_INCREMENT)
            }
        }
    }
}