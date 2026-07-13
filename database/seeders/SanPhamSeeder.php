<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SanPhamSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================================
        // BUOC 1: XOA DU LIEU CU (tu bang con nhat den cha nhat)
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
        // BUOC 2: TAO DANH MUC
        // ==========================================================
        $danhMucs = [];
        foreach (['Đồ uống', 'Thời trang', 'Thực phẩm'] as $idx => $ten) {
            $mauSac = ['#0d6efd', '#d63384', '#198754'][$idx];
            $icon   = ['bi-cup-straw', 'bi-bag-heart', 'bi-basket3-fill'][$idx];
            $id = DB::table('danh_muc_san_pham')->insertGetId([
                'ten_danh_muc' => $ten,
                'mau_sac'      => $mauSac,
                'icon'         => $icon,
                'trang_thai'   => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $danhMucs[$ten] = $id;
        }

        // ==========================================================
        // BUOC 3: TAO THUOC TINH CHA + CON
        // ==========================================================
        // Cha: Kích thước
        $ktId = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh'   => 'Kích thước',
            'trang_thai'       => true,
            'thuoc_tinh_cha_id'=> null,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
        $sizeIds = [];
        foreach (['M', 'L', 'XL'] as $size) {
            $sizeIds[$size] = DB::table('thuoc_tinh_san_pham')->insertGetId([
                'ten_thuoc_tinh'   => $size,
                'trang_thai'       => true,
                'thuoc_tinh_cha_id'=> $ktId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // Cha: Màu sắc
        $msId = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh'   => 'Màu sắc',
            'trang_thai'       => true,
            'thuoc_tinh_cha_id'=> null,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
        $mauIds = [];
        foreach (['Đen', 'Trắng'] as $mau) {
            $mauIds[$mau] = DB::table('thuoc_tinh_san_pham')->insertGetId([
                'ten_thuoc_tinh'   => $mau,
                'trang_thai'       => true,
                'thuoc_tinh_cha_id'=> $msId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ==========================================================
        // BUOC 4: TAO SAN PHAM + BIEN THE + DON VI QUY DOI
        // ==========================================================

        // ---- 4a. Bia Heineken (Đồ uống) - Co don vi quy doi ----
        $spHeinekenId = DB::table('san_pham')->insertGetId([
            'id_danh_muc'  => $danhMucs['Đồ uống'],
            'ten_san_pham' => 'Bia Heineken',
            'thuong_hieu'  => 'Heineken',
            'mo_ta'        => 'Bia Heineken nhập khẩu Hà Lan, lon 330ml.',
            'trang_thai'   => true,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        // Bien the goc: Lon
        $vtLonId = DB::table('bien_the_san_pham')->insertGetId([
            'product_id'         => $spHeinekenId,
            'ten_bien_the'       => 'Lon',         // KHONG NULL
            'ma_hang'            => 'HEI-000001',
            'ma_vach'            => '8934801000010',
            'gia_von'            => 10000,
            'gia_ban'            => 15000,
            'so_luong_ton'       => 0,             // seeder kho se cap nhat
            'dinh_muc_toi_thieu' => 10,
            'thuoc_tinh_ids'     => null,
            'trang_thai'         => true,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        // Don vi quy doi: Thung (24 lon)
        DB::table('don_vi_quy_doi')->insert([
            'variant_id'          => $vtLonId,
            'ten_don_vi'          => 'Thùng',
            'ty_le_quy_doi'       => 24,
            'ma_hang'             => 'HEI-THU001',
            'ma_vach'             => '8934801000027',
            'gia_von_quy_doi'     => 240000,
            'gia_ban_quy_doi'     => 350000,
            'gia_ban_si'          => 320000,
            'la_don_vi_mac_dinh'  => false,
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);

        // ---- 4b. Ao thun co tron (Thoi trang) - Nhieu bien the ----
        $spAoId = DB::table('san_pham')->insertGetId([
            'id_danh_muc'  => $danhMucs['Thời trang'],
            'ten_san_pham' => 'Áo thun cổ tròn',
            'thuong_hieu'  => 'SmartMart',
            'mo_ta'        => 'Áo thun nam cổ tròn chất cotton co giãn, nhiều màu.',
            'trang_thai'   => true,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        $variantMap = [];
        foreach ([
            ['size' => 'M',    'mau' => 'Đen',   'gia' => 120000],
            ['size' => 'L',    'mau' => 'Trắng', 'gia' => 120000],
        ] as $idx => $cfg) {
            $tenBt = $cfg['size'] . ' - ' . $cfg['mau'];
            $thuocTinhIds = [$sizeIds[$cfg['size']], $mauIds[$cfg['mau']]];
            $vtId = DB::table('bien_the_san_pham')->insertGetId([
                'product_id'         => $spAoId,
                'ten_bien_the'       => $tenBt,     // KHONG NULL
                'ma_hang'            => 'ATO-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'ma_vach'            => '89349000' . str_pad($spAoId, 3, '0', STR_PAD_LEFT) . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'gia_von'            => (int)($cfg['gia'] * 0.6),
                'gia_ban'            => $cfg['gia'],
                'so_luong_ton'       => 0,          // seeder kho se cap nhat
                'dinh_muc_toi_thieu' => 5,
                'thuoc_tinh_ids'     => json_encode($thuocTinhIds),
                'trang_thai'         => true,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
            $variantMap[$tenBt] = $vtId;
        }

        // ---- 4c. Nuoc ep tao (Thuc pham) - Chi co bien the goc ----
        $spNuocEpId = DB::table('san_pham')->insertGetId([
            'id_danh_muc'  => $danhMucs['Thực phẩm'],
            'ten_san_pham' => 'Nước ép táo',
            'thuong_hieu'  => 'FreshJuice',
            'mo_ta'        => 'Nước ép táo 100% tự nhiên, không đường, chai 1L.',
            'trang_thai'   => true,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        $vtChaiId = DB::table('bien_the_san_pham')->insertGetId([
            'product_id'         => $spNuocEpId,
            'ten_bien_the'       => 'Chai',
            'ma_hang'            => 'NJT-CHAI01',
            'ma_vach'            => '8935100000018',
            'gia_von'            => 18000,
            'gia_ban'            => 28000,
            'so_luong_ton'       => 0,
            'dinh_muc_toi_thieu' => 15,
            'thuoc_tinh_ids'     => null,
            'trang_thai'         => true,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        DB::table('don_vi_quy_doi')->insert([
            'variant_id'          => $vtChaiId,
            'ten_don_vi'          => 'Thùng',
            'ty_le_quy_doi'       => 12,
            'ma_hang'             => 'NJT-THU001',
            'ma_vach'             => '8935100000025',
            'gia_von_quy_doi'     => 216000,
            'gia_ban_quy_doi'     => 320000,
            'gia_ban_si'          => 295000,
            'la_don_vi_mac_dinh'  => false,
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);

        // ==========================================================
        // BUOC 5: BAO CAO
        // ==========================================================
        $this->command->info('=== SanPhamSeeder hoan thanh ===');
        $this->command->info('Danh muc : ' . count($danhMucs));
        $this->command->info('Thuoc tinh cha: 2 (Kich thuoc, Mau sac)');
        $this->command->info('Thuoc tinh con: ' . count($sizeIds) . ' sizes + ' . count($mauIds) . ' colors');
        $this->command->info('San pham: 3 (Bia Heineken, Ao thun co tron, Nuoc ep tao)');
        $this->command->info('Bien the: ' . DB::table('bien_the_san_pham')->count());
        $this->command->info('Don vi quy doi: ' . DB::table('don_vi_quy_doi')->count());
    }
}
