<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThanhToanHoaDonSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('thanh_toan_hoa_don')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $hoaDons = DB::table('hoa_don')
            ->where('trang_thai', 'Hoàn thành')
            ->whereNull('deleted_at')
            ->get();

        if ($hoaDons->isEmpty()) {
            $this->command->warn('[ThanhToanHoaDonSeeder] Khong co hoa don hoan thanh. Bo qua.');
            return;
        }

        $rows = [];
        $now = now();

        foreach ($hoaDons as $hd) {
            $phuongThucMap = [
                'tien_mat' => 'tien_mat',
                'payos' => 'payos',
            ];
            $pt = $phuongThucMap[$hd->phuong_thuc_thanh_toan] ?? 'tien_mat';

            $rows[] = [
                'id_hoa_don' => $hd->id,
                'phuong_thuc' => $pt,
                'so_tien' => (float) $hd->khach_can_tra,
                'trang_thai' => 'da_thanh_toan',
                'ma_giao_dich' => $pt === 'payos' ? 'PAYOS-' . str_pad((string) $hd->id, 6, '0', STR_PAD_LEFT) : null,
                'ghi_chu' => $pt === 'payos' ? 'Thanh toán PayOS' : 'Tiền mặt tại quầy',
                'thanh_toan_luc' => $hd->created_at,
                'created_at' => $hd->created_at,
                'updated_at' => $hd->created_at,
            ];
        }

        DB::table('thanh_toan_hoa_don')->insert($rows);

        $this->command->info('[ThanhToanHoaDonSeeder] Da tao '.count($rows).' ban ghi thanh toan hoa don.');
    }
}