<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanHangSeedBoSungSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('chi_tiet_hoa_don')->truncate();
        DB::table('lich_su_tich_diem')->truncate();
        DB::table('hoa_don')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $nguoiDungs = DB::table('nguoi_dung')->whereNull('deleted_at')->pluck('id')->toArray();
        $khachHangs = DB::table('khach_hang')->whereNull('deleted_at')->pluck('id')->toArray();
        $variants = DB::table('bien_the_san_pham')->whereNull('deleted_at')->orderBy('id')->get();
        $khuyenMais = DB::table('khuyen_mai')->where('trang_thai', true)->get();

        if (empty($nguoiDungs) || $variants->isEmpty()) {
            $this->command->warn('[BanHangSeedBoSungSeeder] Thieu du lieu nen tang. Bo qua.');
            return;
        }

        $now = now();

        $variantIndex = [];
        foreach ($variants as $v) {
            $variantIndex[$v->id] = $v;
        }
        $variantIds = array_keys($variantIndex);

        $phuongThucs = ['tien_mat', 'payos'];
        $phuongThucWeights = [65, 35];

        $tongHoaDon = 0;
        $tongChiTiet = 0;
        $tongLichSu = 0;
        $tongTruKho = 0;

        for ($i = 0; $i < 47; $i++) {
            $ngayLap = $now->copy()->subDays(rand(1, 58))->subHours(rand(0, 23));

            $idNguoiDung = $nguoiDungs[array_rand($nguoiDungs)];
            $idKhachHang = !empty($khachHangs) && rand(1, 100) <= 70 ? $khachHangs[array_rand($khachHangs)] : null;

            $km = null;
            $tienGiamGia = 0;
            if (rand(1, 100) <= 35 && $khuyenMais->isNotEmpty()) {
                $km = $khuyenMais->random();
                $subTotal = 0;
                $soSp = rand(1, 4);
                for ($k = 0; $k < $soSp; $k++) {
                    $subTotal += (float) $variantIndex[$variantIds[array_rand($variantIds)]]->gia_ban * rand(1, 3);
                }
                if ($subTotal >= (float) ($km->don_hang_toi_thieu ?? 0)) {
                    if ($km->loai_giam_gia === 'phan_tram') {
                        $tienGiamGia = (int) min(
                            $subTotal * (float) $km->gia_tri_giam / 100,
                            $km->giam_toi_da ?? PHP_INT_MAX
                        );
                    } elseif ($km->loai_giam_gia === 'tien_mat') {
                        $tienGiamGia = (int) $km->gia_tri_giam;
                    }
                }
            }

            $phuongThuc = $this->weightedPick($phuongThucs, $phuongThucWeights);

            DB::transaction(function () use (
                $ngayLap, $idNguoiDung, $idKhachHang, $km, $tienGiamGia, $phuongThuc,
                $variantIds, $variantIndex, $now, &$tongHoaDon, &$tongChiTiet, &$tongTruKho, &$tongLichSu
            ) {
                $soSp = rand(1, 5);
                $selectedVariants = [];
                $tongTienHang = 0;

                for ($k = 0; $k < $soSp; $k++) {
                    $vId = $variantIds[array_rand($variantIds)];
                    if (isset($selectedVariants[$vId])) {
                        $selectedVariants[$vId] += rand(1, 3);
                    } else {
                        $selectedVariants[$vId] = rand(1, 3);
                    }
                }

                $rows = [];
                foreach ($selectedVariants as $vId => $qty) {
                    $v = $variantIndex[$vId];
                    $qty = min($qty, max(1, (int) $v->so_luong_ton));
                    if ($qty < 1) {
                        $qty = 1;
                    }
                    $thanhTien = (float) $v->gia_ban * $qty;
                    $tongTienHang += $thanhTien;
                    $rows[] = [
                        'variant_id' => $vId,
                        'product_id' => $v->product_id,
                        'qty' => $qty,
                        'gia_ban' => (float) $v->gia_ban,
                        'thanh_tien' => $thanhTien,
                    ];
                }

                $khachCanTra = max(0, $tongTienHang - $tienGiamGia);
                $laTheKhongTienMat = in_array($phuongThuc, ['payos', 'vnpay'], true);
                $tienKhachDua = $laTheKhongTienMat ? $khachCanTra : $khachCanTra + rand(0, 50000);
                $tienThua = $laTheKhongTienMat ? 0 : max(0, $tienKhachDua - $khachCanTra);
                $diemThuDuoc = (int) floor($khachCanTra / 10000);

                $hoaDonId = DB::table('hoa_don')->insertGetId([
                    'id_nguoi_dung' => $idNguoiDung,
                    'id_khach_hang' => $idKhachHang,
                    'id_ca_lam_viec' => null,
                    'id_khuyen_mai' => $km?->id,
                    'tong_tien_hang' => $tongTienHang,
                    'tien_giam_gia' => $tienGiamGia,
                    'khach_can_tra' => $khachCanTra,
                    'tien_khach_dua' => $tienKhachDua,
                    'tien_thua' => $tienThua,
                    'phuong_thuc_thanh_toan' => $phuongThuc,
                    'trang_thai' => 'Hoàn thành',
                    'diem_su_dung' => 0,
                    'diem_thu_duoc' => $diemThuDuoc,
                    'created_at' => $ngayLap,
                    'updated_at' => $ngayLap,
                ]);

                $chiTietRows = [];
                foreach ($rows as $r) {
                    $chiTietRows[] = [
                        'id_hoa_don' => $hoaDonId,
                        'id_san_pham' => $r['product_id'],
                        'id_chi_tiet_phieu' => null,
                        'so_luong' => $r['qty'],
                        'gia_ban' => $r['gia_ban'],
                        'thanh_tien' => $r['thanh_tien'],
                        'created_at' => $ngayLap,
                        'updated_at' => $ngayLap,
                    ];

                    DB::table('bien_the_san_pham')
                        ->where('id', $r['variant_id'])
                        ->decrement('so_luong_ton', $r['qty']);
                    $tongTruKho++;
                }

                DB::table('chi_tiet_hoa_don')->insert($chiTietRows);
                $tongChiTiet += count($chiTietRows);

                if ($idKhachHang && $diemThuDuoc > 0) {
                    DB::table('khach_hang')
                        ->where('id', $idKhachHang)
                        ->increment('diem_tich_luy', $diemThuDuoc);

                    DB::table('khach_hang')
                        ->where('id', $idKhachHang)
                        ->increment('tong_chi_tieu', $khachCanTra);

                    DB::table('lich_su_tich_diem')->insert([
                        'id_khach_hang' => $idKhachHang,
                        'id_hoa_don' => $hoaDonId,
                        'loai_bien_dong' => 'tang',
                        'so_diem' => $diemThuDuoc,
                        'ly_do' => 'Tích điểm từ hóa đơn',
                        'created_at' => $ngayLap,
                        'updated_at' => $ngayLap,
                    ]);
                    $tongLichSu++;
                }

                $tongHoaDon++;
            });
        }

        $this->command->info('[BanHangSeedBoSungSeeder] Hoan tat:');
        $this->command->info("  - Hoa don moi: {$tongHoaDon}");
        $this->command->info("  - Chi tiet hoa don: {$tongChiTiet}");
        $this->command->info("  - Lich su tich diem: {$tongLichSu}");
        $this->command->info("  - Luot tru ton kho: {$tongTruKho}");
    }

    private function weightedPick(array $items, array $weights): string
    {
        $sum = array_sum($weights);
        $r = rand(1, $sum);
        $acc = 0;
        foreach ($items as $idx => $item) {
            $acc += $weights[$idx];
            if ($r <= $acc) {
                return $item;
            }
        }
        return $items[0];
    }
}