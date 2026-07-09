<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ResetSanPhamSeeder
 *
 * Xóa toàn bộ dữ liệu sản phẩm cũ (theo kiến trúc sai)
 * và tạo dữ liệu mới đúng kiến trúc POS chuẩn 3 bảng:
 *
 *   san_pham (cha)
 *     └── bien_the_san_pham  ← Đơn vị cơ bản (ty_le = 1), chứa giá gốc, mã vạch gốc, tồn kho
 *           └── don_vi_quy_doi ← Đơn vị quy đổi (ty_le > 1), KHÔNG có đơn vị cơ bản
 *
 * Thứ tự xóa: bảng con trước → bảng cha sau.
 */
class ResetSanPhamSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('[ResetSanPhamSeeder] Bat dau reset du lieu san pham...');

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

        $this->command->info('[1/5] Da xoa du lieu cu. Bat dau tao du lieu moi...');

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
        $this->command->info("[2/5] Da tao {$this->count('danh_muc_san_pham')} danh muc.");

        // ==========================================================
        // BUOC 3: TAO THUOC TINH (cha + con)
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
        foreach (['M', 'L', 'XL', '38', '39', '40'] as $s) {
            $sizeIds[$s] = DB::table('thuoc_tinh_san_pham')->insertGetId([
                'ten_thuoc_tinh'   => $s,
                'trang_thai'       => true,
                'thuoc_tinh_cha_id'=> $sizeParentId,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $mauIds = [];
        foreach (['Đen', 'Trắng', 'Hồng', 'Xanh'] as $m) {
            $mauIds[$m] = DB::table('thuoc_tinh_san_pham')->insertGetId([
                'ten_thuoc_tinh'   => $m,
                'trang_thai'       => true,
                'thuoc_tinh_cha_id'=> $mauParentId,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $this->command->info('[3/5] Da tao thuoc tinh (Kich thuoc, Mau sac).');

        // ==========================================================
        // BUOC 4: DINH NGHIA 10 SAN PHAM
        // ==========================================================
        // Loai:
        //   thuong     → 1 san_pham, 1 bien_the, 0 don_vi_quy_doi
        //   don_vi     → 1 san_pham, 1 bien_the (la don vi co ban), nhieu don_vi_quy_doi (ty_le > 1)
        //   thuoc_tinh → 1 san_pham, nhieu bien_the (moi bien_the = 1 don vi co ban, 0 don_vi_quy_doi)
        $sanPhamDefs = [
            // ── THUONG (khong co don vi quy doi) ──────────────────
            [
                'key'      => 'lavie_500',
                'ten'      => 'Nước khoáng Lavie 500ml',
                'dm'       => 'Đồ uống',
                'thuong_hieu' => 'Lavie',
                'mo_ta'    => 'Nước khoáng thiên nhiên Lavie 500ml, đóng chai tiện lợi.',
                'loai'     => 'thuong',
                'gia'      => 5000,
                'dinh_muc' => 20,
            ],
            [
                'key'      => 'omachi_tom',
                'ten'      => 'Mì Omachi tôm chua cay',
                'dm'       => 'Mì gói',
                'thuong_hieu' => 'Omachi',
                'mo_ta'    => 'Mì ăn liền Omachi hương vị tôm chua cay, gói 85g.',
                'loai'     => 'thuong',
                'gia'      => 8500,
                'dinh_muc' => 30,
            ],
            [
                'key'      => 'oishi_bo',
                'ten'      => 'Snack Oishi vị bò',
                'dm'       => 'Bánh kẹo',
                'thuong_hieu' => 'Oishi',
                'mo_ta'    => 'Snack Oishi vị bò nướng, gói 45g.',
                'loai'     => 'thuong',
                'gia'      => 10000,
                'dinh_muc' => 25,
            ],
            [
                'key'      => 'clear_men',
                'ten'      => 'Dầu gội Clear Men 200ml',
                'dm'       => 'Chăm sóc cá nhân',
                'thuong_hieu' => 'Clear',
                'mo_ta'    => 'Dầu gội Clear Men bạc hà mát lạnh, chai 200ml.',
                'loai'     => 'thuong',
                'gia'      => 65000,
                'dinh_muc' => 10,
            ],
            [
                'key'      => 'colgate_brush',
                'ten'      => 'Bàn chải đánh răng Colgate',
                'dm'       => 'Chăm sóc cá nhân',
                'thuong_hieu' => 'Colgate',
                'mo_ta'    => 'Bàn chải đánh răng Colgate lông mềm, cho người lớn.',
                'loai'     => 'thuong',
                'gia'      => 25000,
                'dinh_muc' => 10,
            ],

            // ── DON VI (1 bien_the = don vi co ban, nhieu don_vi_quy_doi) ──
            [
                'key'      => 'heineken_lon',
                'ten'      => 'Bia Heineken lon 330ml',
                'dm'       => 'Đồ uống',
                'thuong_hieu' => 'Heineken',
                'mo_ta'    => 'Bia Heineken lon 330ml, nhập khẩu Hà Lan.',
                'loai'     => 'don_vi',
                'gia'      => 14000,       // gia cua don vi co ban (lon)
                'dinh_muc' => 15,
                'units'    => [
                    // don_vi_quy_doi chi chua don vi TY LE > 1
                    ['ten' => 'Thùng', 'ty_le' => 24, 'la_mac_dinh' => false],
                ],
            ],
            [
                'key'      => 'vinamilk_1l',
                'ten'      => 'Sữa tươi Vinamilk 1L',
                'dm'       => 'Thực phẩm',
                'thuong_hieu' => 'Vinamilk',
                'mo_ta'    => 'Sữa tươi tiệt trùng Vinamilk 100% sữa tươi, hộp 1L.',
                'loai'     => 'don_vi',
                'gia'      => 28000,       // gia don vi co ban (hop)
                'dinh_muc' => 15,
                'units'    => [
                    ['ten' => 'Thùng', 'ty_le' => 12, 'la_mac_dinh' => false],
                ],
            ],
            [
                'key'      => 'gao_st25',
                'ten'      => 'Gạo ST25 túi 5kg',
                'dm'       => 'Thực phẩm',
                'thuong_hieu' => 'ST25',
                'mo_ta'    => 'Gạo ST25 hữu cơ túi 5kg, gạo ngon nhất thế giới.',
                'loai'     => 'don_vi',
                'gia'      => 130000,      // gia don vi co ban (tui)
                'dinh_muc' => 8,
                'units'    => [
                    ['ten' => 'Baó', 'ty_le' => 4, 'la_mac_dinh' => false],
                ],
            ],

            // ── THUOC TINH (nhieu bien_the, moi bien_the = 1 don vi co ban) ──
            [
                'key'      => 'ao_thun',
                'ten'      => 'Áo thun nam cổ tròn',
                'dm'       => 'Thời trang',
                'thuong_hieu' => 'SmartMart',
                'mo_ta'    => 'Áo thun nam cổ tròn chất cotton 100%, co giãn tốt.',
                'loai'     => 'thuoc_tinh',
                'gia'      => 120000,
                'dinh_muc' => 5,
                'combos'   => [
                    ['size' => 'M',  'mau' => 'Đen'],
                    ['size' => 'M',  'mau' => 'Trắng'],
                    ['size' => 'L',  'mau' => 'Đen'],
                    ['size' => 'L',  'mau' => 'Trắng'],
                    ['size' => 'XL', 'mau' => 'Đen'],
                    ['size' => 'XL', 'mau' => 'Trắng'],
                ],
            ],
            [
                'key'      => 'giay_the_thao',
                'ten'      => 'Giày thể thao nữ',
                'dm'       => 'Thời trang',
                'thuong_hieu' => 'SmartMart',
                'mo_ta'    => 'Giày thể thao nữ đế mềm, thoáng khí, phù hợp chạy bộ.',
                'loai'     => 'thuoc_tinh',
                'gia'      => 350000,
                'dinh_muc' => 5,
                'combos'   => [
                    ['size' => '38', 'mau' => 'Hồng'],
                    ['size' => '38', 'mau' => 'Xanh'],
                    ['size' => '39', 'mau' => 'Hồng'],
                    ['size' => '39', 'mau' => 'Xanh'],
                    ['size' => '40', 'mau' => 'Hồng'],
                    ['size' => '40', 'mau' => 'Xanh'],
                ],
            ],
        ];

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
                // ── LOAI THUONG: 1 bien_the, 0 don_vi_quy_doi ──
                DB::table('bien_the_san_pham')->insert([
                    'product_id'        => $productId,
                    'ten_bien_the'      => null,
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
                // ── LOAI DON_VI: 1 bien_the (la don vi co ban, ten_bien_the = null),
                //    nhieu don_vi_quy_doi (chi ty_le > 1) ──
                // Bien the = don vi co ban (lon/hop/tui), gia von/ban la gia cua don vi do
                $variantId = DB::table('bien_the_san_pham')->insertGetId([
                    'product_id'        => $productId,
                    'ten_bien_the'     => null,   // don vi co ban khong co ten bien the
                    'ma_hang'          => $maHangPrefix . '-001',
                    'ma_vach'          => '8934' . str_pad((string)$productId, 8, '0', STR_PAD_LEFT),
                    'gia_von'          => $giaVon,
                    'gia_ban'          => $p['gia'],        // gia don vi co ban
                    'so_luong_ton'     => 0,
                    'dinh_muc_toi_thieu' => $p['dinh_muc'],
                    'hinh_anh'         => null,
                    'thuoc_tinh_ids'   => null,
                    'trang_thai'      => true,
                    'created_at'       => $now,
                    'updated_at'      => $now,
                ]);
                $variantCount++;

                // Chi insert don_vi_quy_doi cho cac don vi TY LE > 1
                foreach ($p['units'] as $idx => $u) {
                    if ((int)$u['ty_le'] <= 1) {
                        continue; // BO QUA don vi co ban (ty_le = 1)
                    }
                    DB::table('don_vi_quy_doi')->insert([
                        'variant_id'         => $variantId,
                        'ten_don_vi'         => $u['ten'],
                        'ty_le_quy_doi'      => (int)$u['ty_le'],
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
                // ── LOAI THUOC TINH: nhieu bien_the, moi bien_the la 1 don vi co ban ──
                foreach ($p['combos'] as $idx => $combo) {
                    $tenBienThe = $combo['size'] . ' - ' . $combo['mau'];
                    $thuocTinhIds = [$sizeIds[$combo['size']], $mauIds[$combo['mau']]];

                    DB::table('bien_the_san_pham')->insert([
                        'product_id'        => $productId,
                        'ten_bien_the'     => $tenBienThe,
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

        // ==========================================================
        // BUOC 6: TAO KHO HANG (lo hang + chi tiet lo hang)
        // ==========================================================
        $this->command->info('[4/5] Tao kho hang (lo hang, chi tiet lo hang)...');

        $nguoiDungs = DB::table('nguoi_dung')->pluck('id')->toArray();
        if (empty($nguoiDungs)) {
            $this->command->info('Khong co nguoi dung -> bo qua kho hang.');
        }
        $idNguoiDung = $nguoiDungs[0] ?? null;

        $nccIds = DB::table('nha_cung_cap')->pluck('id')->toArray();
        if (empty($nccIds)) {
            $this->command->info('Khong co nha cung cap -> bo qua kho hang.');
            $nccIds = [];
        }

        // So luong nhap cho tung san pham (tong 2 lo = so_luong_ton cua variant)
        // Mot so san pham se co ton kho < dinh_muc de test canh bao
        $tonKhoMap = [
            'lavie_500'     => 150,   // binh thuong (dinh muc 20)
            'omachi_tom'    => 200,   // binh thuong (dinh muc 30)
            'oishi_bo'      => 100,   // binh thuong (dinh muc 25)
            'clear_men'     => 8,     // DUOI DINH MUC (dinh muc 10)
            'colgate_brush' => 7,     // DUOI DINH MUC (dinh muc 10)
            'heineken_lon'  => 240,   // binh thuong (dinh muc 15)
            'vinamilk_1l'  => 120,   // binh thuong (dinh muc 15)
            'gao_st25'      => 24,    // binh thuong (dinh muc 8)
            'ao_thun'       => 4,     // DUOI DINH MUC (dinh muc 5)
            'giay_the_thao' => 4,     // DUOI DINH MUC (dinh muc 5)
        ];

        $nccIdx = 0;
        $loHangCount    = 0;
        $ctLoCount      = 0;
        $ctPhieuCount   = 0;

        $products = DB::table('san_pham')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        foreach ($products as $sp) {
            $key = collect($sanPhamDefs)->firstWhere('ten', $sp->ten_san_pham)['key'] ?? null;
            $tongTon = $tonKhoMap[$key] ?? 100;

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

            // Mỗi variant có 1 lô (không cần tách HSD gần/xa vì đã test trong seeders khác)
            $idNCC = $nccIds[$nccIdx % count($nccIds)] ?? $nccIds[0];
            $nccIdx++;

            $ngayNhap = $now->copy()->subDays(rand(5, 60))->toDateString();
            $hanSuDung = $now->copy()->addDays(rand(90, 270))->toDateString();

            // 6a. Phieu nhap
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

            // 6b. Lo hang
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

            // 6c. Chi tiet lo hang & chi tiet phieu cho TUNG VARIANT
            $giaVonTB = (float)$variants->avg('gia_von');

            // chi_tiet_lo_hang: 1 row mỗi san_pham mỗi lô (do unique constraint trên id_lo_hang+id_san_pham+han_su_dung)
            // → chỉ insert row đầu tiên với tongTon, các variant sau bỏ qua
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
                // Phân bổ số lượng: variant đầu tiên nhận phần dư
                $slVariant = $tonPerVariant + ($vIdx === 0 ? $tonRemainder : 0);

                // Cap nhat so_luong_ton cua bien_the
                DB::table('bien_the_san_pham')
                    ->where('id', $variant->id)
                    ->update(['so_luong_ton' => $slVariant]);

                // Chi tiet phieu cho variant nay
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
                    'ghi_chu'          => "{$sp->ten_san_pham} - {$variant->ten_bien_the}",
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

        // ==========================================================
        // TONG KET
        // ==========================================================
        $this->command->info('[5/5] ResetSanPhamSeeder hoan tat!');
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
        $this->command->line("  • Don vi QD      : " . $this->count('don_vi_quy_doi') . "  (chi ty_le > 1)");
        $this->command->line("  • Lo hang        : " . $loHangCount);
        $this->command->line("  • Chi tiet lo    : " . $ctLoCount);
        $this->command->line("  • Chi tiet phieu : " . $ctPhieuCount);
    }

    // ─── Helper methods ────────────────────────────────────────

    private function count(string $table): int
    {
        return \DB::table($table)->count();
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
