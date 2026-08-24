<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhoHangSeeder extends Seeder
{
    public function run(): void
    {
        // Lay nguoi dung va nha cung cap
        $userId = DB::table('nguoi_dung')->value('id');
        if (!$userId) {
            $this->command->warn('Khong co nguoi dung. Bo qua KhoHangSeeder.');
            return;
        }

        $nccIds = DB::table('nha_cung_cap')->pluck('id')->toArray();
        if (empty($nccIds)) {
            $this->command->warn('Khong co nha cung cap. Bo qua KhoHangSeeder.');
            return;
        }

        // Xoa du lieu kho cu (chi tiet lo -> lo -> phieu)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('chi_tiet_phieu')->truncate();
        DB::table('chi_tiet_lo_hang')->truncate();
        DB::table('lo_hang')->truncate();
        DB::table('phieu_xuat')->truncate();
        DB::table('phieu_nhap')->truncate();
        DB::table('phieu')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        $nccIdx = 0;

        // So luong nhap cho tung bien the (chia deu vao 2 lo)
        $soLuongMoiLo = [80, 120];

        // ======= Lay toan bo bien the tu DB (sau SanPhamSeeder) =======
        $variants = DB::table('bien_the_san_pham')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        if ($variants->isEmpty()) {
            $this->command->warn('Khong co bien the. Bo qua KhoHangSeeder.');
            return;
        }

        $tongLo = 0;
        $tongCtl = 0;
        $tongPhieu = 0;
        $tongCtPhieu = 0;

        foreach ($variants as $variant) {
            $nccId = $nccIds[$nccIdx % count($nccIds)];
            $nccIdx++;

            // Đặt giá vốn mẫu cho variant trước khi tạo lô hàng:
            // Chỉ đặt nếu hiện tại = 0 và có giá bán > 0 (để seeder tạo lô với giá nhập hợp lý)
            if ((float)$variant->gia_von == 0 && (float)$variant->gia_ban > 0) {
                DB::table('bien_the_san_pham')
                    ->where('id', $variant->id)
                    ->update(['gia_von' => round((float)$variant->gia_ban * 0.7, 2)]);
                $variant->gia_von = round((float)$variant->gia_ban * 0.7, 2);
            }

            // Moi variant co 2 lo: Lo gan (HSD 20-30 ngay) va Lo xa (HSD 3-6 thang)
            $ngayNhapLo1 = $now->copy()->subDays(rand(30, 60))->toDateString();
            $ngayNhapLo2 = $now->copy()->subDays(rand(5, 20))->toDateString();
            $hsdLo1 = $now->copy()->addDays(rand(20, 30))->toDateString();
            $hsdLo2 = $now->copy()->addDays(rand(90, 180))->toDateString();

            // ---------- LO 1: HSD gan ----------
            $idPhieu1 = DB::table('phieu')->insertGetId([
                'loai_phieu'        => 'Nhập hàng',
                'loai_phieu_enum'   => 'nhap_mua_hang',
                'id_nguoi_dung'    => $userId,
                'id_nha_cung_cap'  => $nccId,
                'ghi_chu'          => "Nhap kho san pham (Lo HSD gan)",
                'created_at'        => $ngayNhapLo1 . ' 08:00:00',
                'updated_at'        => $ngayNhapLo1 . ' 08:00:00',
            ]);

            DB::table('phieu_nhap')->insert([
                'id_phieu'      => $idPhieu1,
                'loai_nhap'    => 'mua_hang',
                'ghi_chu'      => "Nhap lo HSD gan - {$hsdLo1}",
                'created_at'    => $ngayNhapLo1 . ' 08:00:00',
                'updated_at'    => $ngayNhapLo1 . ' 08:00:00',
            ]);

            $maLo1 = 'LO-' . str_pad((string)$idPhieu1, 5, '0', STR_PAD_LEFT);
            $idLo1 = DB::table('lo_hang')->insertGetId([
                'id_phieu'            => $idPhieu1,
                'id_nha_cung_cap'    => $nccId,
                'ma_lo'              => $maLo1,
                'ngay_nhap'          => $ngayNhapLo1,
                'ghi_chu'            => "Lo HSD gan - {$hsdLo1}",
                'created_at'          => $ngayNhapLo1 . ' 08:00:00',
                'updated_at'          => $ngayNhapLo1 . ' 08:00:00',
            ]);

            // Chi tiet lo 1 - VARIANT_ID BAT BUOC (khong NULL)
            $slLo1 = $soLuongMoiLo[0];
            $idCtLo1 = DB::table('chi_tiet_lo_hang')->insertGetId([
                'id_lo_hang'    => $idLo1,
                'id_san_pham'  => $variant->product_id,
                'variant_id'    => $variant->id,    // DUNG VARIANT - TUYET DOI KHONG NULL
                'so_luong_nhap' => $slLo1,
                'so_luong_ton'  => $slLo1,
                'gia_nhap'      => $variant->gia_von,
                'han_su_dung'   => $hsdLo1,
                'created_at'    => $ngayNhapLo1 . ' 08:00:00',
                'updated_at'    => $ngayNhapLo1 . ' 08:00:00',
            ]);

            // Chi tiet phieu 1
            DB::table('chi_tiet_phieu')->insert([
                'id_phieu'              => $idPhieu1,
                'id_san_pham'          => $variant->product_id,
                'variant_id'           => $variant->id,
                'id_lo_hang'           => $idLo1,
                'id_chi_tiet_lo_hang'  => $idCtLo1,
                'so_luong'             => $slLo1,
                'gia_nhap'             => $variant->gia_von,
                'ma_lo'                => $maLo1,
                'han_su_dung'          => $hsdLo1,
                'so_luong_con_lai'     => $slLo1,
                'ghi_chu'              => "Nhap lo HSD gan variant #{$variant->id}",
                'created_at'           => $ngayNhapLo1 . ' 08:00:00',
                'updated_at'           => $ngayNhapLo1 . ' 08:00:00',
            ]);

            // ---------- LO 2: HSD xa ----------
            $idPhieu2 = DB::table('phieu')->insertGetId([
                'loai_phieu'        => 'Nhập hàng',
                'loai_phieu_enum'   => 'nhap_mua_hang',
                'id_nguoi_dung'    => $userId,
                'id_nha_cung_cap'  => $nccId,
                'ghi_chu'          => "Nhap kho san pham (Lo HSD xa)",
                'created_at'        => $ngayNhapLo2 . ' 09:00:00',
                'updated_at'        => $ngayNhapLo2 . ' 09:00:00',
            ]);

            DB::table('phieu_nhap')->insert([
                'id_phieu'      => $idPhieu2,
                'loai_nhap'    => 'mua_hang',
                'ghi_chu'      => "Nhap lo HSD xa - {$hsdLo2}",
                'created_at'    => $ngayNhapLo2 . ' 09:00:00',
                'updated_at'    => $ngayNhapLo2 . ' 09:00:00',
            ]);

            $maLo2 = 'LO-' . str_pad((string)$idPhieu2, 5, '0', STR_PAD_LEFT);
            $idLo2 = DB::table('lo_hang')->insertGetId([
                'id_phieu'            => $idPhieu2,
                'id_nha_cung_cap'    => $nccId,
                'ma_lo'              => $maLo2,
                'ngay_nhap'          => $ngayNhapLo2,
                'ghi_chu'            => "Lo HSD xa - {$hsdLo2}",
                'created_at'          => $ngayNhapLo2 . ' 09:00:00',
                'updated_at'          => $ngayNhapLo2 . ' 09:00:00',
            ]);

            // Chi tiet lo 2 - VARIANT_ID BAT BUOC
            $slLo2 = $soLuongMoiLo[1];
            $idCtLo2 = DB::table('chi_tiet_lo_hang')->insertGetId([
                'id_lo_hang'    => $idLo2,
                'id_san_pham'  => $variant->product_id,
                'variant_id'    => $variant->id,    // DUNG VARIANT - TUYET DOI KHONG NULL
                'so_luong_nhap' => $slLo2,
                'so_luong_ton'  => $slLo2,
                'gia_nhap'      => $variant->gia_von,
                'han_su_dung'   => $hsdLo2,
                'created_at'    => $ngayNhapLo2 . ' 09:00:00',
                'updated_at'    => $ngayNhapLo2 . ' 09:00:00',
            ]);

            // Chi tiet phieu 2
            DB::table('chi_tiet_phieu')->insert([
                'id_phieu'              => $idPhieu2,
                'id_san_pham'          => $variant->product_id,
                'variant_id'           => $variant->id,
                'id_lo_hang'           => $idLo2,
                'id_chi_tiet_lo_hang'  => $idCtLo2,
                'so_luong'             => $slLo2,
                'gia_nhap'             => $variant->gia_von,
                'ma_lo'                => $maLo2,
                'han_su_dung'          => $hsdLo2,
                'so_luong_con_lai'     => $slLo2,
                'ghi_chu'              => "Nhap lo HSD xa variant #{$variant->id}",
                'created_at'           => $ngayNhapLo2 . ' 09:00:00',
                'updated_at'           => $ngayNhapLo2 . ' 09:00:00',
            ]);

            // ---------- CAP NHAT so_luong_ton CUA BIEN THE ----------
            DB::table('bien_the_san_pham')
                ->where('id', $variant->id)
                ->update(['so_luong_ton' => $slLo1 + $slLo2]);

            $tongLo      += 2;
            $tongCtl     += 2;
            $tongPhieu   += 2;
            $tongCtPhieu += 2;
        }

        $this->command->info('=== KhoHangSeeder hoan thanh ===');
        $this->command->info("Bien the da nhap: {$variants->count()}");
        $this->command->info("Lo hang: {$tongLo}");
        $this->command->info("Chi tiet lo hang: {$tongCtl}");
        $this->command->info("Phieu nhap: {$tongPhieu}");
        $this->command->info("Chi tiet phieu: {$tongCtPhieu}");
        $this->command->info("Tong ton kho: " . DB::table('bien_the_san_pham')->sum('so_luong_ton'));
    }
}
