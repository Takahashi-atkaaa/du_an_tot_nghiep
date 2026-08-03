<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BangLuongSeedBoSungSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $nguoiDungs = DB::table('nguoi_dung')->whereNull('deleted_at')->orderBy('id')->get();
        if ($nguoiDungs->isEmpty()) {
            $this->command->warn('[BangLuongSeedBoSungSeeder] Khong co nguoi dung. Bo qua.');
            return;
        }

        $thietLapLuong = DB::table('thiet_lap_luong')
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id_nguoi_dung');

        $kyLuong = [
            ['thang' => 7,  'nam' => 2026, 'trang_thai' => 'Đã chi'],
            ['thang' => 8,  'nam' => 2026, 'trang_thai' => 'Đã chi'],
            ['thang' => 9,  'nam' => 2026, 'trang_thai' => 'Đã chi'],
            ['thang' => 10, 'nam' => 2026, 'trang_thai' => 'Đã chi'],
            ['thang' => 11, 'nam' => 2026, 'trang_thai' => 'Nháp'],
        ];

        $tongBang = 0;
        $tongPhieu = 0;

        foreach ($kyLuong as $ky) {
            $thang = $ky['thang'];
            $nam = $ky['nam'];

            $ngayBatDau = sprintf('%04d-%02d-01', $nam, $thang);
            $ngayKetThuc = date('Y-m-t', strtotime($ngayBatDau));

            $tongLuongKy = 0;
            $phieuRows = [];
            foreach ($nguoiDungs as $nd) {
                $cfg = $thietLapLuong[$nd->id] ?? null;
                $luongCoBan = $cfg ? (float) $cfg->luong_theo_gio : 70000;
                $phuCap = $cfg ? (float) $cfg->phu_cap : 1000000;
                $thuong = $cfg ? (float) $cfg->thuong : 500000;
                $gioThucTe = (float) rand(160, 180);
                $luongTamTinh = $luongCoBan * $gioThucTe + $phuCap;
                $tongLuong = $luongTamTinh + $thuong;
                $tongLuongKy += $tongLuong;
                $phieuRows[] = [
                    'id_nguoi_dung' => $nd->id,
                    'luong_tam_tinh' => $luongTamTinh,
                    'tong_luong' => $tongLuong,
                    'tong_gio_lam_thuc_te' => $gioThucTe,
                    'trang_thai' => $ky['trang_thai'],
                ];
            }

            $bangId = DB::table('bang_luong')->insertGetId([
                'ten_bang_luong' => sprintf('Bảng lương tháng %02d/%d', $thang, $nam),
                'ngay_bat_dau' => $ngayBatDau,
                'ngay_ket_thuc' => $ngayKetThuc,
                'tong_luong_tat_ca_nhan_vien' => $tongLuongKy,
                'so_nhan_vien' => count($nguoiDungs),
                'trang_thai' => $ky['trang_thai'],
                'created_at' => $now->copy()->subMonths(11 - $thang),
                'updated_at' => $now->copy()->subMonths(11 - $thang),
            ]);
            $tongBang++;

            foreach ($phieuRows as $row) {
                DB::table('phieu_luong')->insert([
                    'id_nguoi_dung' => $row['id_nguoi_dung'],
                    'id_bang_luong' => $bangId,
                    'luong_tam_tinh' => $row['luong_tam_tinh'],
                    'tong_luong' => $row['tong_luong'],
                    'tong_gio_lam_thuc_te' => $row['tong_gio_lam_thuc_te'],
                    'trang_thai' => $row['trang_thai'],
                    'created_at' => $now->copy()->subMonths(11 - $thang),
                    'updated_at' => $now->copy()->subMonths(11 - $thang),
                ]);
                $tongPhieu++;
            }
        }

        $this->command->info('[BangLuongSeedBoSungSeeder] Da tao '.$tongBang.' bang luong & '.$tongPhieu.' phieu luong.');
    }
}
