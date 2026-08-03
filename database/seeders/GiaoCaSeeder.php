<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GiaoCaSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $nguoiDungs = DB::table('nguoi_dung')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        $caSang = DB::table('ca_lam_viec')->where('ten_ca', 'Ngay')->value('id');
        $caToi = DB::table('ca_lam_viec')->where('ten_ca', 'Toi')->value('id');
        $caDem = DB::table('ca_lam_viec')->where('ten_ca', 'Dem')->value('id');
        $caIds = array_filter([$caSang, $caToi, $caDem]);

        if ($nguoiDungs->isEmpty() || empty($caIds)) {
            $this->command->warn('[GiaoCaSeeder] Thieu nguoi dung hoac ca lam viec. Bo qua.');
            return;
        }

        $nguoiDungArr = $nguoiDungs->all();
        $tongGiaoCa = 0;

        for ($i = 0; $i < 10; $i++) {
            $ngay = $now->copy()->subDays(rand(1, 12));
            $caId = $caIds[array_rand($caIds)];

            $banGiaoIdx = array_rand($nguoiDungArr);
            $nhanCaIdx = array_rand($nguoiDungArr);
            if ($nhanCaIdx === $banGiaoIdx && count($nguoiDungArr) > 1) {
                $nhanCaIdx = ($banGiaoIdx + 1) % count($nguoiDungArr);
            }

            $banGiao = $nguoiDungArr[$banGiaoIdx];
            $nhanCa = $nguoiDungArr[$nhanCaIdx];

            $ca = DB::table('ca_lam_viec')->where('id', $caId)->first();
            $gioBatDau = $ca->gio_bat_dau;
            $gioKetThuc = $ca->gio_ket_thuc;

            $thoiGianBatDau = $ngay->copy()->setTimeFromTimeString($gioBatDau);
            $thoiGianKetThuc = $ngay->copy()->setTimeFromTimeString($gioKetThuc);
            if ($thoiGianKetThuc->lessThanOrEqualTo($thoiGianBatDau)) {
                $thoiGianKetThuc->addDay();
            }

            $tienMatDauCa = rand(500000, 2000000);
            $doanhThu = rand(2000000, 15000000);
            $tienMatCuoiCa = $tienMatDauCa + $doanhThu + rand(-100000, 200000);
            $chenhLech = $tienMatCuoiCa - ($tienMatDauCa + $doanhThu);

            DB::table('giao_ca')->insert([
                'id_truong_ca_ban_giao' => $banGiao->id,
                'id_truong_ca_nhan_ca' => $nhanCa->id,
                'id_ca_lam_viec' => $caId,
                'tien_mat_dau_ca' => $tienMatDauCa,
                'tien_mat_cuoi_ca' => $tienMatCuoiCa,
                'chenh_lech' => $chenhLech,
                'thoi_gian_bat_dau_ca' => $thoiGianBatDau,
                'thoi_gian_ket_thuc_ca' => $thoiGianKetThuc,
                'trang_thai' => 1,
                'ghi_chu' => abs($chenhLech) > 50000
                    ? 'Có chênh lệch tiền mặt cần kiểm tra'
                    : 'Bàn giao ca thành công',
                'created_at' => $thoiGianKetThuc,
                'updated_at' => $thoiGianKetThuc,
            ]);
            $tongGiaoCa++;
        }

        $this->command->info('[GiaoCaSeeder] Da tao '.$tongGiaoCa.' ban ghi giao ca.');
    }
}
