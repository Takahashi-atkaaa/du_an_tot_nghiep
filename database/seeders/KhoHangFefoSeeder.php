<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhoHangFefoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Lay nguoi dung + NCC
        $nguoiDungs = DB::table('nguoi_dung')->pluck('id')->toArray();
        if (empty($nguoiDungs)) {
            $this->command->warn('Khong co nguoi dung. Bo qua KhoHangFefoSeeder.');
            return;
        }
        $idNguoiDung = $nguoiDungs[0];

        $nhaCungCaps = DB::table('nha_cung_cap')->pluck('id')->toArray();
        if (empty($nhaCungCaps)) {
            $this->command->warn('Khong co nha cung cap. Bo qua KhoHangFefoSeeder.');
            return;
        }
        $idNCC = $nhaCungCaps[0];

        // ==========================================================
        // Chon san pham Bia Heineken de demo FEFO
        // ==========================================================
        $sp = DB::table('san_pham')->where('ten_san_pham', 'Bia Heineken lon 330ml')->first();
        if (!$sp) {
            $this->command->warn('Khong tim thay Bia Heineken. Bo qua FEFO seeder.');
            return;
        }

        $variant = DB::table('bien_the_san_pham')->where('product_id', $sp->id)->first();
        if (!$variant) {
            $this->command->warn('Khong co variant Bia Heineken. Bo qua FEFO seeder.');
            return;
        }

        $variantId = $variant->id;
        $giaVon = $variant->gia_von;

        // Lay 2 chi tiet lo hien co (1 HSD gan, 1 HSD xa) cua san pham nay
        $chiTietLoGan = DB::table('chi_tiet_lo_hang')
            ->where('id_san_pham', $sp->id)
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc')
            ->first();

        if (!$chiTietLoGan) {
            $this->command->warn('Khong co chi tiet lo nao con ton. Bo qua FEFO seeder.');
            return;
        }

        $chiTietLoXa = DB::table('chi_tiet_lo_hang')
            ->where('id_san_pham', $sp->id)
            ->where('so_luong_ton', '>', 0)
            ->where('id', '!=', $chiTietLoGan->id)
            ->orderBy('han_su_dung', 'desc')
            ->first();

        if (!$chiTietLoXa) {
            $this->command->warn('Khong co du 2 lo. Bo qua FEFO seeder.');
            return;
        }

        $idLoA = $chiTietLoGan->id_lo_hang;
        $hsdA = $chiTietLoGan->han_su_dung;
        $idCTLoA = $chiTietLoGan->id;

        // ==========================================================
        // Xuat 30 cai tu Lo A (HSD gan) - mo phong FEFO
        // ==========================================================
        $soLuongXuat = 30;

        // Lay thong tin lo A
        $loA = DB::table('lo_hang')->where('id', $idLoA)->first();

        // Tao phieu xuat (cha)
        $idPhieuXuat = DB::table('phieu')->insertGetId([
            'loai_phieu' => 'Trả hàng NCC',
            'loai_phieu_enum' => 'xuat_tra_hang_nha_cung_cap',
            'id_nguoi_dung' => $idNguoiDung,
            'id_nha_cung_cap' => $idNCC,
            'id_hoa_don' => null,
            'ly_do' => 'Test FEFO - hàng lỗi nhà cung cấp',
            'ghi_chu' => "Xuất {$soLuongXuat} cái từ lô A (HSD gần: {$hsdA}) - hệ thống FEFO tự chọn lô HSD gần nhất",
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ]);

        DB::table('phieu_xuat')->insert([
            'id_phieu' => $idPhieuXuat,
            'loai_xuat' => 'tra_hang_nha_cung_cap',
            'id_phieu_nhap_lien_quan' => $loA->id_phieu ?? null,
            'ly_do' => 'Test FEFO - hàng lỗi',
            'ghi_chu' => "Xuất {$soLuongXuat} cái để test FEFO",
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ]);

        // Tru so_luong_ton cua chi tiet lo A (HSD gan)
        DB::table('chi_tiet_lo_hang')
            ->where('id', $idCTLoA)
            ->update([
                'so_luong_ton' => DB::raw('GREATEST(so_luong_ton - ' . $soLuongXuat . ', 0)'),
                'updated_at' => $now->toDateTimeString(),
            ]);

        // Lay so_luong_ton moi
        $ctLoASauXuat = DB::table('chi_tiet_lo_hang')->where('id', $idCTLoA)->first();
        $soLuongConLai = $ctLoASauXuat->so_luong_ton;

        // Tao chi tiet phieu xuat
        DB::table('chi_tiet_phieu')->insert([
            'id_phieu' => $idPhieuXuat,
            'id_san_pham' => $sp->id,
            'variant_id' => $variantId,
            'id_lo_hang' => $idLoA,
            'id_chi_tiet_lo_hang' => $idCTLoA,
            'so_luong' => $soLuongXuat,
            'gia_nhap' => $giaVon,
            'ma_lo' => $loA->ma_lo,
            'han_su_dung' => $hsdA,
            'so_luong_con_lai' => $soLuongConLai,
            'ghi_chu' => 'FEFO: xuất từ lô A (HSD gần nhất)',
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString(),
        ]);

        // Cap nhat so_luong_ton cua variant - trừ so_luong xuất
        DB::table('bien_the_san_pham')
            ->where('id', $variantId)
            ->update([
                'so_luong_ton' => DB::raw('GREATEST(so_luong_ton - ' . $soLuongXuat . ', 0)'),
            ]);

        $this->command->info('=== KhoHangFefoSeeder hoan thanh ===');
        $this->command->info("San pham: {$sp->ten_san_pham} (variant_id={$variantId})");
        $this->command->info("Lo A (HSD gan {$hsdA}): tru {$soLuongXuat} cai (con {$soLuongConLai})");
        $this->command->info("Khi xuat them, FEFO se tiep tuc tru tu Lo A.");
    }
}