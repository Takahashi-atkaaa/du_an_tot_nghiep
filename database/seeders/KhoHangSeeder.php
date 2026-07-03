<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhoHangSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Lay danh sach nguoi dung (de lam nguoi tao phieu)
        $nguoiDungs = DB::table('nguoi_dung')->pluck('id')->toArray();
        if (empty($nguoiDungs)) {
            $this->command->warn('Khong co nguoi dung. Bo qua KhoHangSeeder.');
            return;
        }
        $idNguoiDung = $nguoiDungs[0];

        // Lay danh sach nha cung cap
        $nhaCungCaps = DB::table('nha_cung_cap')->pluck('id')->toArray();
        if (empty($nhaCungCaps)) {
            $this->command->warn('Khong co nha cung cap. Bo qua KhoHangSeeder.');
            return;
        }

        // Lay danh sach san pham kem variants
        $sanPhams = DB::table('san_pham')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        if ($sanPhams->isEmpty()) {
            $this->command->warn('Khong co san pham. Bo qua KhoHangSeeder.');
            return;
        }

        // Dinh muc nho: mot so san pham se co ton kho thap de test canh bao "duoi dinh muc"
        // Moi so luong nay la so luong CHO TUNG VARIANT (moi variant co 2 lo, tong = so[0]+so[1])
        // Canh bao "duoi dinh muc": bien_the.so_luong_ton <= bien_the.dinh_muc_toi_thieu
        // Theo thu tu san pham:
        //  1. Lavie 500ml          -> 50 / 100   (binh thuong)
        //  2. Omachi tom           -> 80 / 120   (binh thuong)
        //  3. Oishi bo             -> 60 / 90    (binh thuong)
        //  4. Clear Men            -> 2 / 6      -> DINH MUC (8 = 2+6, min=10) (8 <= 10)
        //  5. Colgate brush        -> 3 / 5      -> DINH MUC (8 = 3+5, min=10) (8 <= 10)
        //  6. Heineken lon         -> 100 / 150  (binh thuong - FEFO tru 30 -> con 220)
        //  7. Vinamilk 1L          -> 40 / 80    (binh thuong)
        //  8. Gao ST25             -> 12 / 30    (binh thuong)
        //  9. Ao thun              -> 1 / 3      -> DINH MUC (4 = 1+3, min=5) (4 <= 5)
        // 10. Giay the thao        -> 1 / 3      -> DINH MUC (4 = 1+3, min=5) (4 <= 5)
        $soLuongTheoSP = [
            1 => [50, 100],   // lavie
            2 => [80, 120],   // omachi
            3 => [60, 90],    // oishi
            4 => [2, 6],      // clear men -> canh bao (tong 8, dinh muc 10)
            5 => [3, 5],      // colgate -> canh bao (tong 8, dinh muc 10)
            6 => [100, 150],  // heineken (FEFO seeder se tru 30)
            7 => [40, 80],    // vinamilk
            8 => [12, 30],    // gao st25
            9 => [1, 3],      // ao thun -> canh bao (tong 4, dinh muc 5)
            10 => [1, 3],     // giay the thao -> canh bao (tong 4, dinh muc 5)
        ];

        $nccIndex = 0;
        $loCount = 0;
        $chiTietLoCount = 0;
        $phieuCount = 0;
        $chiTietPhieuCount = 0;

        foreach ($sanPhams as $sp) {
            $variants = DB::table('bien_the_san_pham')
                ->where('product_id', $sp->id)
                ->get();

            if ($variants->isEmpty()) {
                continue;
            }

            $spIndex = $sp->id; // dung id lam key
            $soLuongMoiLo = $soLuongTheoSP[$spIndex] ?? [40, 80];

            // Lay NCC xoay vong
            $idNCC = $nhaCungCaps[$nccIndex % count($nhaCungCaps)];
            $nccIndex++;

            // ==========================================================
            // Tao 2 lo hang cho san pham nay
            // ==========================================================
            // Lo 1: HSD gan (15-25 ngay) - canh bao sap het HSD
            // Lo 2: HSD xa (3-9 thang)
            $ngayNhapLo1 = $now->copy()->subDays(rand(60, 120))->toDateString(); // Nhap cach day 2-4 thang
            $hanSuDungLo1 = $now->copy()->addDays(rand(15, 25))->toDateString(); // HSD 15-25 ngay

            $ngayNhapLo2 = $now->copy()->subDays(rand(5, 30))->toDateString(); // Nhap gan day
            $hanSuDungLo2 = $now->copy()->addDays(rand(90, 270))->toDateString(); // HSD 3-9 thang

            // ----- LO 1 -----
            $idPhieu1 = DB::table('phieu')->insertGetId([
                'loai_phieu' => 'Nhập hàng',
                'loai_phieu_enum' => 'nhap_mua_hang',
                'id_nguoi_dung' => $idNguoiDung,
                'id_nha_cung_cap' => $idNCC,
                'id_hoa_don' => null,
                'ghi_chu' => "Nhập {$sp->ten_san_pham} - Lô HSD gần",
                'created_at' => $ngayNhapLo1 . ' 08:00:00',
                'updated_at' => $ngayNhapLo1 . ' 08:00:00',
            ]);
            DB::table('phieu_nhap')->insert([
                'id_phieu' => $idPhieu1,
                'loai_nhap' => 'mua_hang',
                'id_hoa_don' => null,
                'id_phieu_xuat_goc' => null,
                'ghi_chu' => "Nhập {$sp->ten_san_pham} - Lô HSD gần ({$hanSuDungLo1})",
                'created_at' => $ngayNhapLo1 . ' 08:00:00',
                'updated_at' => $ngayNhapLo1 . ' 08:00:00',
            ]);

            $idLo1 = DB::table('lo_hang')->insertGetId([
                'id_phieu' => $idPhieu1,
                'id_nha_cung_cap' => $idNCC,
                'ma_lo' => 'LO-NEAR-' . str_pad((string)$idPhieu1, 5, '0', STR_PAD_LEFT),
                'ngay_nhap' => $ngayNhapLo1,
                'ghi_chu' => "Lô HSD gần - {$hanSuDungLo1}",
                'created_at' => $ngayNhapLo1 . ' 08:00:00',
                'updated_at' => $ngayNhapLo1 . ' 08:00:00',
            ]);

            // ----- LO 2 -----
            $idPhieu2 = DB::table('phieu')->insertGetId([
                'loai_phieu' => 'Nhập hàng',
                'loai_phieu_enum' => 'nhap_mua_hang',
                'id_nguoi_dung' => $idNguoiDung,
                'id_nha_cung_cap' => $idNCC,
                'id_hoa_don' => null,
                'ghi_chu' => "Nhập {$sp->ten_san_pham} - Lô HSD xa",
                'created_at' => $ngayNhapLo2 . ' 09:00:00',
                'updated_at' => $ngayNhapLo2 . ' 09:00:00',
            ]);
            DB::table('phieu_nhap')->insert([
                'id_phieu' => $idPhieu2,
                'loai_nhap' => 'mua_hang',
                'id_hoa_don' => null,
                'id_phieu_xuat_goc' => null,
                'ghi_chu' => "Nhập {$sp->ten_san_pham} - Lô HSD xa ({$hanSuDungLo2})",
                'created_at' => $ngayNhapLo2 . ' 09:00:00',
                'updated_at' => $ngayNhapLo2 . ' 09:00:00',
            ]);

            $idLo2 = DB::table('lo_hang')->insertGetId([
                'id_phieu' => $idPhieu2,
                'id_nha_cung_cap' => $idNCC,
                'ma_lo' => 'LO-FAR-' . str_pad((string)$idPhieu2, 5, '0', STR_PAD_LEFT),
                'ngay_nhap' => $ngayNhapLo2,
                'ghi_chu' => "Lô HSD xa - {$hanSuDungLo2}",
                'created_at' => $ngayNhapLo2 . ' 09:00:00',
                'updated_at' => $ngayNhapLo2 . ' 09:00:00',
            ]);

            $loCount += 2;
            $phieuCount += 2;

            // ==========================================================
            // Tao chi tiet lo hang cho tung variant trong 2 lo
            // Moi variant co 1 row rieng, gia su unique constraint chi ap dung
            // khi variant_id = null (gop san pham). De tranh loi, ta chi insert
            // 1 row tong quat (variant_id = null) cho moi lo.
            // Sau do cap nhat so_luong_ton bien_the dua tren ty le.
            // ==========================================================

            // So luong tong cho moi lo
            $soLuongLo1 = array_sum($soLuongMoiLo) > 0 ? $soLuongMoiLo[0] * count($variants) : 0;
            $soLuongLo2 = array_sum($soLuongMoiLo) > 0 ? $soLuongMoiLo[1] * count($variants) : 0;

            $giaVonTB = $variants->avg('gia_von');

            // Chi tiet lo 1 - 1 row cho ca san pham (variant_id = null)
            $idCTLo1 = DB::table('chi_tiet_lo_hang')->insertGetId([
                'id_lo_hang' => $idLo1,
                'id_san_pham' => $sp->id,
                'variant_id' => null,
                'so_luong_nhap' => $soLuongLo1,
                'so_luong_ton' => $soLuongLo1,
                'gia_nhap' => $giaVonTB,
                'han_su_dung' => $hanSuDungLo1,
                'created_at' => $ngayNhapLo1 . ' 08:00:00',
                'updated_at' => $ngayNhapLo1 . ' 08:00:00',
            ]);

            // Chi tiet lo 2 - 1 row cho ca san pham (variant_id = null)
            $idCTLo2 = DB::table('chi_tiet_lo_hang')->insertGetId([
                'id_lo_hang' => $idLo2,
                'id_san_pham' => $sp->id,
                'variant_id' => null,
                'so_luong_nhap' => $soLuongLo2,
                'so_luong_ton' => $soLuongLo2,
                'gia_nhap' => $giaVonTB,
                'han_su_dung' => $hanSuDungLo2,
                'created_at' => $ngayNhapLo2 . ' 09:00:00',
                'updated_at' => $ngayNhapLo2 . ' 09:00:00',
            ]);

            $chiTietLoCount += 2;

            // Phan bo so luong cho tung variant
            // Lay so luong/variant cua moi lo
            $slPerVariantLo1 = $soLuongMoiLo[0];
            $slPerVariantLo2 = $soLuongMoiLo[1];

            foreach ($variants as $variant) {
                // Cap nhat truc tiep so_luong_ton cho variant (de co canh bao duoi dinh muc)
                $tongVariant = $slPerVariantLo1 + $slPerVariantLo2;
                DB::table('bien_the_san_pham')
                    ->where('id', $variant->id)
                    ->update(['so_luong_ton' => $tongVariant]);
            }

            // Chi tiet phieu cho phieu 1
            DB::table('chi_tiet_phieu')->insert([
                'id_phieu' => $idPhieu1,
                'id_san_pham' => $sp->id,
                'variant_id' => $variants->first()->id,
                'id_lo_hang' => $idLo1,
                'id_chi_tiet_lo_hang' => $idCTLo1,
                'so_luong' => $soLuongLo1,
                'gia_nhap' => $giaVonTB,
                'ma_lo' => 'LO-NEAR-' . str_pad((string)$idPhieu1, 5, '0', STR_PAD_LEFT),
                'han_su_dung' => $hanSuDungLo1,
                'so_luong_con_lai' => $soLuongLo1,
                'ghi_chu' => 'Tổng SL các variant trong lô',
                'created_at' => $ngayNhapLo1 . ' 08:00:00',
                'updated_at' => $ngayNhapLo1 . ' 08:00:00',
            ]);

            // Chi tiet phieu cho phieu 2
            DB::table('chi_tiet_phieu')->insert([
                'id_phieu' => $idPhieu2,
                'id_san_pham' => $sp->id,
                'variant_id' => $variants->first()->id,
                'id_lo_hang' => $idLo2,
                'id_chi_tiet_lo_hang' => $idCTLo2,
                'so_luong' => $soLuongLo2,
                'gia_nhap' => $giaVonTB,
                'ma_lo' => 'LO-FAR-' . str_pad((string)$idPhieu2, 5, '0', STR_PAD_LEFT),
                'han_su_dung' => $hanSuDungLo2,
                'so_luong_con_lai' => $soLuongLo2,
                'ghi_chu' => 'Tổng SL các variant trong lô',
                'created_at' => $ngayNhapLo2 . ' 09:00:00',
                'updated_at' => $ngayNhapLo2 . ' 09:00:00',
            ]);

            $chiTietPhieuCount += 2;
        }

        $this->command->info("=== KhoHangSeeder hoan thanh ===");
        $this->command->info("Da tao: {$loCount} lo hang, {$chiTietLoCount} chi tiet lo (gop variant), {$phieuCount} phieu, {$chiTietPhieuCount} chi tiet phieu.");
    }
}