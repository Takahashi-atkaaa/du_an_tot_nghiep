<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiemDanhSeeder extends Seeder
{
    public function run(): void
    {
        $nhanVienIds = DB::table('nguoi_dung')
            ->where('id_vai_tro', DB::table('vai_tro')->where('ten_vai_tro', 'Nhân viên')->value('id'))
            ->pluck('id')
            ->toArray();

        $truongCaIds = DB::table('nguoi_dung')
            ->where('id_vai_tro', DB::table('vai_tro')->where('ten_vai_tro', 'Trưởng ca')->value('id'))
            ->pluck('id')
            ->toArray();

        $caRows = DB::table('ca_lam_viec')->get();

        if (empty($nhanVienIds) || $caRows->isEmpty()) {
            return;
        }

        $allNhanVienIds = array_merge($truongCaIds, $nhanVienIds);
        $trangThaiVaoOptions = ['Đúng giờ', 'Đúng giờ', 'Đúng giờ', 'Đi muộn', 'Đi muộn'];
        $trangThaiTanOptions = ['Đúng giờ', 'Đúng giờ', 'Về sớm', 'Tăng ca'];

        $records = [];

        foreach (range(-10, -1) as $ngayOffset) {
            $ngay = Carbon::today()->addDays($ngayOffset);
            $nhanVienForDay = array_slice($allNhanVienIds, 0, rand(2, min(3, count($allNhanVienIds))));

            foreach ($nhanVienForDay as $nvId) {
                $ca = $caRows->random();
                $gioBatDau = $this->parseTimeOnDate($ngay, $ca->gio_bat_dau);
                $gioKetThuc = $this->parseTimeOnDate($ngay, $ca->gio_ket_thuc, $gioBatDau);

                $trangThaiVao = $trangThaiVaoOptions[array_rand($trangThaiVaoOptions)];
                $trangThaiTan = $trangThaiTanOptions[array_rand($trangThaiTanOptions)];

                if ($trangThaiVao === 'Đi muộn') {
                    $soPhutDiMuon = rand(10, 45);
                    $soGioDiLamMuon = $soPhutDiMuon;
                    $gioVao = $gioBatDau->copy()->addMinutes($soPhutDiMuon);
                } else {
                    $soGioDiLamMuon = 0;
                    $soPhutLechVao = rand(-5, $ca->so_phut_di_lam_tre_toi_da ?? 15);
                    $gioVao = $gioBatDau->copy()->addMinutes($soPhutLechVao);
                }

                if ($trangThaiTan === 'Về sớm') {
                    $soGioLamThem = 0;
                    $gioTanCa = $gioKetThuc->copy()->subMinutes(rand(10, 45));
                } elseif ($trangThaiTan === 'Tăng ca') {
                    $soPhutTangCa = rand(15, 90);
                    $soGioLamThem = round($soPhutTangCa / 60, 2);
                    $gioTanCa = $gioKetThuc->copy()->addMinutes($soPhutTangCa);
                } else {
                    $soGioLamThem = 0;
                    $gioTanCa = $gioKetThuc->copy()->addMinutes(rand(-10, 5));
                }

                $chiaCaId = DB::table('chia_ca_lam_viec')->insertGetId([
                    'id_nguoi_dung' => $nvId,
                    'id_ca_lam_viec' => $ca->id,
                    'ngay' => $ngay->toDateString(),
                    'deleted_at' => null,
                    'created_at' => now()->addDays($ngayOffset),
                    'updated_at' => now()->addDays($ngayOffset),
                ]);

                $records[] = [
                    'id_chia_ca_lam_viec' => $chiaCaId,
                    'gio_vao' => $gioVao,
                    'so_gio_di_lam_muon' => $soGioDiLamMuon,
                    'gio_tan_ca' => $gioTanCa,
                    'so_gio_lam_them' => $soGioLamThem,
                    'trang_thai_vao_lam' => $trangThaiVao,
                    'trang_thai_tan_ca' => $trangThaiTan,
                    'deleted_at' => null,
                    'created_at' => now()->addDays($ngayOffset),
                    'updated_at' => now()->addDays($ngayOffset),
                ];
            }
        }

        foreach (array_chunk($records, 50) as $chunk) {
            DB::table('diem_danh')->insert($chunk);
        }
    }

    private function parseTimeOnDate(Carbon $date, string $time, ?Carbon $compareTo = null): Carbon
    {
        $parts = explode(':', $time);
        $hour = (int) $parts[0];
        $min = (int) $parts[1];

        $result = $date->copy()->setTime($hour, $min, 0);

        if ($compareTo !== null && $result->lt($compareTo)) {
            $result->addDay();
        }

        return $result;
    }
}
