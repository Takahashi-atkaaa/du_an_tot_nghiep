<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LichSuTichDiemSeeder extends Seeder
{
    public function run(): void
    {
        $hoaDons = DB::table('hoa_don')
            ->where('trang_thai', 'Hoàn thành')
            ->whereNotNull('id_khach_hang')
            ->where('diem_thu_duoc', '>', 0)
            ->whereNull('deleted_at')
            ->get();

        if ($hoaDons->isEmpty()) {
            $this->command->warn('[LichSuTichDiemSeeder] Khong co hoa don co khach hang. Bo qua.');
            return;
        }

        $rows = [];
        foreach ($hoaDons as $hd) {
            $existing = DB::table('lich_su_tich_diem')
                ->where('id_hoa_don', $hd->id)
                ->whereNull('deleted_at')
                ->exists();
            if ($existing) {
                continue;
            }
            $rows[] = [
                'id_khach_hang' => $hd->id_khach_hang,
                'id_hoa_don' => $hd->id,
                'loai_bien_dong' => 'tang',
                'so_diem' => (int) $hd->diem_thu_duoc,
                'ly_do' => 'Tich diem tu hoa don #' . $hd->id,
                'created_at' => $hd->created_at,
                'updated_at' => $hd->created_at,
            ];
        }

        if (!empty($rows)) {
            DB::table('lich_su_tich_diem')->insert($rows);
        }

        $this->command->info('[LichSuTichDiemSeeder] Da tao '.count($rows).' lich su tich diem.');
    }
}
