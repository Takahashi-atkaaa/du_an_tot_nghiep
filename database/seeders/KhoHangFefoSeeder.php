<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhoHangFefoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Đảm bảo có sản phẩm để test
        $this->seedProducts($now);

        // Lấy dữ liệu để test
        $nguoiDungs = DB::table('nguoi_dung')->pluck('id')->toArray();
        $nhaCungCaps = DB::table('nha_cung_cap')->pluck('id')->toArray();
        $sanPhams = DB::table('san_pham')
            ->where('trang_thai', true)
            ->limit(5)
            ->get(['id', 'ten_san_pham', 'ma_vach'])
            ->keyBy('id');

        if ($sanPhams->isEmpty()) {
            $this->command->warn('Chưa có sản phẩm. Bỏ qua seed FEFO.');
            return;
        }

        $idNguoiDung = $nguoiDungs[0] ?? 1;
        $idNCC_A = $nhaCungCaps[0] ?? null;
        $idNCC_B = $nhaCungCaps[1] ?? $idNCC_A;

        // ==========================================
        // KỊCH BẢN FEFO: Cùng 1 sản phẩm, 2 lô khác HSD
        // ==========================================
        // Lô 1 (lô cũ, HSD gần): nhập ngày 1/5, HSD 20/10/2026
        // Lô 2 (lô mới, HSD xa): nhập ngày 1/6, HSD 20/11/2026

        $spKemDanhrang = $sanPhams->first();
        $spKemDanhrangId = $spKemDanhrang->id;

        // Tạo 2 lô: Lô A (HSD gần) và Lô B (HSD xa)
        $idLoA = DB::table('lo_hang')->insertGetId([
            'id_nha_cung_cap' => $idNCC_A,
            'ma_lo' => 'LO-2026-001',
            'ngay_nhap' => '2026-05-01',
            'ghi_chu' => 'Lô nhập ngày 01/05/2026 - HSD gần nhất',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $idLoB = DB::table('lo_hang')->insertGetId([
            'id_nha_cung_cap' => $idNCC_A,
            'ma_lo' => 'LO-2026-002',
            'ngay_nhap' => '2026-06-01',
            'ghi_chu' => 'Lô nhập ngày 01/06/2026 - HSD xa hơn',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Chi tiết lô A: HSD 20/10/2026
        DB::table('chi_tiet_lo_hang')->insert([
            'id_lo_hang' => $idLoA,
            'id_san_pham' => $spKemDanhrangId,
            'so_luong_nhap' => 50,
            'so_luong_ton' => 50,
            'gia_nhap' => 15000,
            'han_su_dung' => '2026-10-20',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Chi tiết lô B: HSD 20/11/2026
        DB::table('chi_tiet_lo_hang')->insert([
            'id_lo_hang' => $idLoB,
            'id_san_pham' => $spKemDanhrangId,
            'so_luong_nhap' => 80,
            'so_luong_ton' => 80,
            'gia_nhap' => 15500,
            'han_su_dung' => '2026-11-20',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ==========================================
        // Tạo phiếu nhập cho 2 lô trên
        // ==========================================
        $this->taoPhieuNhap($idNguoiDung, $idNCC_A, $idLoA, $spKemDanhrangId, $idLoB, $spKemDanhrangId, $now);

        // ==========================================
        // Lô 3: Nhập ngày 1/5 từ NCC B (các sản phẩm khác)
        // ==========================================
        $spKhac = $sanPhams->skip(1)->first();
        if ($spKhac) {
            $idLoC = DB::table('lo_hang')->insertGetId([
                'id_nha_cung_cap' => $idNCC_B,
                'ma_lo' => 'LO-2026-003',
                'ngay_nhap' => '2026-05-15',
                'ghi_chu' => 'Lô NCC B - sản phẩm đa dạng',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($sanPhams->skip(1) as $sp) {
                DB::table('chi_tiet_lo_hang')->insert([
                    'id_lo_hang' => $idLoC,
                    'id_san_pham' => $sp->id,
                    'so_luong_nhap' => rand(20, 100),
                    'so_luong_ton' => rand(20, 100),
                    'gia_nhap' => rand(5000, 50000),
                    'han_su_dung' => $now->copy()->addMonths(rand(3, 12))->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ==========================================
        // Tạo 1 phiếu xuất trả hàng NCC (test FEFO xuất)
        // Xuất 30 cái → trừ vào Lô A trước (HSD gần)
        // ==========================================
        $this->taoPhieuXuat($idNguoiDung, $idNCC_A, $spKemDanhrangId, 30, $idLoA, $now);

        // ==========================================
        // Cập nhật tổng tồn kho trên san_pham
        // ==========================================
        $this->capNhatTonKho($spKemDanhrangId);

        $this->command->info('=== Kịch bản FEFO đã được tạo ===');
        $this->command->info("Sản phẩm: {$spKemDanhrang->ten_san_pham} (ID: {$spKemDanhrangId})");
        $this->command->info('Lô A (LO-2026-001): nhập 50 cái, HSD 20/10/2026, còn 20 cái (đã xuất 30)');
        $this->command->info('Lô B (LO-2026-002): nhập 80 cái, HSD 20/11/2026, còn 80 cái');
        $this->command->info('Khi xuất thêm 20 cái → hệ thống FEFO sẽ trừ Lô A trước (HSD 20/10)');
    }

    private function seedProducts($now): void
    {
        $danhMucIds = [];
        $danhMucs = ['Thực phẩm' => '#198754', 'Bánh kẹo' => '#dc3545', 'Mì gói' => '#fd7e14'];
        foreach ($danhMucs as $ten => $mau) {
            DB::table('danh_muc_san_pham')->updateOrInsert(
                ['ten_danh_muc' => $ten],
                ['mau_sac' => $mau, 'icon' => 'bi bi-bag-fill', 'trang_thai' => true, 'created_at' => $now, 'updated_at' => $now]
            );
            $danhMucIds[$ten] = DB::table('danh_muc_san_pham')->where('ten_danh_muc', $ten)->value('id');
        }

        $donVis = ['Cái' => 1, 'Gói' => 1, 'Chai' => 1];
        foreach ($donVis as $ten => $soLuong) {
            DB::table('don_vi_san_pham')->updateOrInsert(
                ['ten_don_vi' => $ten],
                ['so_luong_san_pham_trong_don_vi' => $soLuong, 'trang_thai' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
        $donViCai = DB::table('don_vi_san_pham')->where('ten_don_vi', 'Cái')->value('id');
        $donViChai = DB::table('don_vi_san_pham')->where('ten_don_vi', 'Chai')->value('id');
        $donViGoi = DB::table('don_vi_san_pham')->where('ten_don_vi', 'Gói')->value('id');

        $products = [
            ['ten' => 'Sữa tươi Vinamilk 180ml', 'ma_vach' => 'FEFO-SP-001', 'danh_muc' => 'Thực phẩm', 'don_vi' => $donViChai, 'gia_ban' => 8500, 'thuong_hieu' => 'Vinamilk'],
            ['ten' => 'Bánh Oreo 133g', 'ma_vach' => 'FEFO-SP-002', 'danh_muc' => 'Bánh kẹo', 'don_vi' => $donViGoi, 'gia_ban' => 22000, 'thuong_hieu' => 'Oreo'],
            ['ten' => 'Mì Hảo Tấm gói', 'ma_vach' => 'FEFO-SP-003', 'danh_muc' => 'Mì gói', 'don_vi' => $donViGoi, 'gia_ban' => 7000, 'thuong_hieu' => 'Hảo Tấm'],
        ];

        foreach ($products as $p) {
            DB::table('san_pham')->updateOrInsert(
                ['ma_vach' => $p['ma_vach']],
                [
                    'id_danh_muc' => $danhMucIds[$p['danh_muc']],
                    'ten_san_pham' => $p['ten'],
                    'thuong_hieu' => $p['thuong_hieu'],
                    'hinh_anh' => null,
                    'gia_ban' => $p['gia_ban'],
                    'so_luong_ton_kho' => 0,
                    'mo_ta' => $p['ten'],
                    'id_don_vi' => $p['don_vi'],
                    'dinh_muc_toi_thieu' => 10,
                    'trang_thai' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function taoPhieuNhap($idNguoiDung, $idNCC, $idLoA, $idSpA, $idLoB, $idSpB, $now): void
    {
        $idPhieuA = DB::table('phieu')->insertGetId([
            'loai_phieu' => 'Nhập hàng',
            'loai_phieu_enum' => 'nhap_mua_hang',
            'id_nguoi_dung' => $idNguoiDung,
            'id_nha_cung_cap' => $idNCC,
            'ghi_chu' => 'Phiếu nhập kho FEFO - Lô A (HSD 20/10)',
            'created_at' => '2026-05-01 08:00:00',
            'updated_at' => '2026-05-01 08:00:00',
        ]);

        DB::table('phieu_nhap')->insert([
            'id_phieu' => $idPhieuA,
            'loai_nhap' => 'mua_hang',
            'ghi_chu' => 'Lô A - HSD 20/10/2026',
            'created_at' => '2026-05-01 08:00:00',
            'updated_at' => '2026-05-01 08:00:00',
        ]);

        $ctLoA = DB::table('chi_tiet_lo_hang')->where('id_lo_hang', $idLoA)->first();

        DB::table('chi_tiet_phieu')->insert([
            'id_phieu' => $idPhieuA,
            'id_san_pham' => $idSpA,
            'id_lo_hang' => $idLoA,
            'id_chi_tiet_lo_hang' => $ctLoA->id ?? null,
            'so_luong' => 50,
            'gia_nhap' => 15000,
            'han_su_dung' => '2026-10-20',
            'so_luong_con_lai' => 50,
            'created_at' => '2026-05-01 08:00:00',
            'updated_at' => '2026-05-01 08:00:00',
        ]);

        $idPhieuB = DB::table('phieu')->insertGetId([
            'loai_phieu' => 'Nhập hàng',
            'loai_phieu_enum' => 'nhap_mua_hang',
            'id_nguoi_dung' => $idNguoiDung,
            'id_nha_cung_cap' => $idNCC,
            'ghi_chu' => 'Phiếu nhập kho FEFO - Lô B (HSD 20/11)',
            'created_at' => '2026-06-01 08:00:00',
            'updated_at' => '2026-06-01 08:00:00',
        ]);

        DB::table('phieu_nhap')->insert([
            'id_phieu' => $idPhieuB,
            'loai_nhap' => 'mua_hang',
            'ghi_chu' => 'Lô B - HSD 20/11/2026',
            'created_at' => '2026-06-01 08:00:00',
            'updated_at' => '2026-06-01 08:00:00',
        ]);

        $ctLoB = DB::table('chi_tiet_lo_hang')->where('id_lo_hang', $idLoB)->first();

        DB::table('chi_tiet_phieu')->insert([
            'id_phieu' => $idPhieuB,
            'id_san_pham' => $idSpB,
            'id_lo_hang' => $idLoB,
            'id_chi_tiet_lo_hang' => $ctLoB->id ?? null,
            'so_luong' => 80,
            'gia_nhap' => 15500,
            'han_su_dung' => '2026-11-20',
            'so_luong_con_lai' => 80,
            'created_at' => '2026-06-01 08:00:00',
            'updated_at' => '2026-06-01 08:00:00',
        ]);
    }

    private function taoPhieuXuat($idNguoiDung, $idNCC, $idSanPham, $soLuong, $idLoA, $now): void
    {
        $idPhieuXuat = DB::table('phieu')->insertGetId([
            'loai_phieu' => 'Trả hàng NCC',
            'loai_phieu_enum' => 'xuat_tra_hang_nha_cung_cap',
            'id_nguoi_dung' => $idNguoiDung,
            'id_nha_cung_cap' => $idNCC,
            'ly_do' => 'Test FEFO - hàng lỗi nhà cung cấp',
            'ghi_chu' => 'Phiếu xuất test FEFO: xuất 30 cái → trừ Lô A (HSD gần nhất) trước',
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ]);

        DB::table('phieu_xuat')->insert([
            'id_phieu' => $idPhieuXuat,
            'loai_xuat' => 'tra_hang_nha_cung_cap',
            'id_phieu_nhap_lien_quan' => null,
            'ly_do' => 'Test FEFO - hàng lỗi',
            'ghi_chu' => 'Xuất 30 cái để test FEFO',
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ]);

        // Cập nhật chi_tiet_lo_hang (trừ 30 cái khỏi Lô A)
        DB::table('chi_tiet_lo_hang')
            ->where('id_lo_hang', $idLoA)
            ->where('id_san_pham', $idSanPham)
            ->update([
                'so_luong_ton' => DB::raw('so_luong_ton - ' . $soLuong),
                'updated_at' => $now,
            ]);

        $ctLoA = DB::table('chi_tiet_lo_hang')
            ->where('id_lo_hang', $idLoA)
            ->where('id_san_pham', $idSanPham)
            ->first();

        DB::table('chi_tiet_phieu')->insert([
            'id_phieu' => $idPhieuXuat,
            'id_san_pham' => $idSanPham,
            'id_lo_hang' => $idLoA,
            'id_chi_tiet_lo_hang' => $ctLoA->id ?? null,
            'so_luong' => $soLuong,
            'gia_nhap' => 15000,
            'han_su_dung' => '2026-10-20',
            'so_luong_con_lai' => DB::raw('(SELECT so_luong_ton FROM chi_tiet_lo_hang WHERE id_lo_hang = ' . $idLoA . ' AND id_san_pham = ' . $idSanPham . ')'),
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ]);
    }

    private function capNhatTonKho(int $idSanPham): void
    {
        $tongTon = DB::table('chi_tiet_lo_hang')
            ->where('id_san_pham', $idSanPham)
            ->sum('so_luong_ton');

        DB::table('san_pham')
            ->where('id', $idSanPham)
            ->update(['so_luong_ton_kho' => $tongTon]);
    }
}
