<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhieuKiemKhoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $nguoiDungs = DB::table('nguoi_dung')->whereNull('deleted_at')->pluck('id')->toArray();
        $chiTietLoHangs = DB::table('chi_tiet_lo_hang')
            ->where('so_luong_ton', '>', 0)
            ->orderBy('id')
            ->get();

        if (empty($nguoiDungs) || $chiTietLoHangs->isEmpty()) {
            $this->command->warn('[PhieuKiemKhoSeeder] Khong co du lieu nen tang. Bo qua.');
            return;
        }

        $variantsById = DB::table('bien_the_san_pham')
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id');

        $sanPhamsById = DB::table('san_pham')
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id');

        $existingCount = DB::table('phieu_kiem_kho')->count();
        $tongPhieu = 0;
        $tongChiTiet = 0;

        for ($i = 0; $i < 10; $i++) {
            $ngayLap = $now->copy()->subDays(rand(1, 60))->subHours(rand(0, 23));
            $idNguoiDung = $nguoiDungs[array_rand($nguoiDungs)];
            $maKiemKho = 'KK' . str_pad((string) ($existingCount + $i + 1), 5, '0', STR_PAD_LEFT);

            $soDong = rand(3, 5);
            $picked = (array) array_rand($chiTietLoHangs->all(), min($soDong, $chiTietLoHangs->count()));

            $chiTietRows = [];
            $tongSlThucTe = 0;
            $tongSlLech = 0;
            $tongGiaTriLech = 0;

            foreach ($picked as $key) {
                $ct = $chiTietLoHangs[$key];
                $variant = $variantsById[$ct->variant_id] ?? null;
                $sanPham = $sanPhamsById[$ct->id_san_pham] ?? null;

                $slTon = (int) $ct->so_luong_ton;
                $lech = rand(-3, 2);
                $slThucTe = max(0, $slTon + $lech);
                $giaVon = (float) $ct->gia_nhap;
                $giaTriLech = $lech * $giaVon;

                $tongSlThucTe += $slThucTe;
                $tongSlLech += $lech;
                $tongGiaTriLech += $giaTriLech;

                $chiTietRows[] = [
                    'id_phieu_kiem_kho' => null,
                    'variant_id' => $ct->variant_id,
                    'id_chi_tiet_lo_hang' => $ct->id,
                    'ma_vach' => $variant->ma_vach ?? null,
                    'ten_san_pham' => $sanPham->ten_san_pham ?? null,
                    'ten_bien_the' => $variant->ten_bien_the ?? null,
                    'ten_don_vi' => $variant->ten_don_vi ?? null,
                    'han_su_dung' => $ct->han_su_dung,
                    'ma_lo' => 'LO-' . str_pad((string) $ct->id_lo_hang, 5, '0', STR_PAD_LEFT),
                    'so_luong_ton' => $slTon,
                    'so_luong_thuc_te' => $slThucTe,
                    'so_luong_lech' => $lech,
                    'gia_von' => $giaVon,
                    'gia_tri_lech' => $giaTriLech,
                    'created_at' => $ngayLap,
                    'updated_at' => $ngayLap,
                ];
            }

            $phieuId = DB::table('phieu_kiem_kho')->insertGetId([
                'ma_kiem_kho' => $maKiemKho,
                'id_chia_ca_lam_viec' => null,
                'id_nguoi_dung' => $idNguoiDung,
                'trang_thai' => 'hoan_thanh',
                'tong_sl_thuc_te' => $tongSlThucTe,
                'tong_sl_lech' => $tongSlLech,
                'tong_gia_tri_lech' => $tongGiaTriLech,
                'ghi_chu' => $tongSlLech < 0 ? 'Phát hiện thất thoát hàng hóa' : 'Kiểm kho định kỳ',
                'hoan_thanh_luc' => $ngayLap,
                'created_at' => $ngayLap,
                'updated_at' => $ngayLap,
            ]);
            $tongPhieu++;

            foreach ($chiTietRows as &$row) {
                $row['id_phieu_kiem_kho'] = $phieuId;
            }
            unset($row);

            DB::table('chi_tiet_kiem_kho')->insert($chiTietRows);
            $tongChiTiet += count($chiTietRows);
        }

        $this->command->info('[PhieuKiemKhoSeeder] Da tao '.$tongPhieu.' phieu kiem kho & '.$tongChiTiet.' chi tiet.');
    }
}
