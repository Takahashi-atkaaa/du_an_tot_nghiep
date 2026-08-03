<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GiaoDichSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('giao_dich')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $hoaDons = DB::table('hoa_don')
            ->where('trang_thai', 'Hoàn thành')
            ->whereNull('deleted_at')
            ->get();

        if ($hoaDons->isEmpty()) {
            $this->command->warn('[GiaoDichSeeder] Khong co hoa don hoan thanh. Bo qua.');
            return;
        }

        $rows = [];

        foreach ($hoaDons as $hd) {
            $pt = $hd->phuong_thuc_thanh_toan;

            // Chuan hoa: hoa_don co the co 'chuyen_khoan'/'vnpay' cu
            // nhung he thong chi chap nhan 'tien_mat' hoac 'payos'.
            // Mac dinh fallback la 'tien_mat'.
            if ($pt === 'chuyen_khoan' || $pt === 'vnpay') {
                $pt = 'payos';
            }

            if (!in_array($pt, ['tien_mat', 'payos'], true)) {
                continue;
            }

            $ngayThanhToan = $hd->updated_at ?? $hd->created_at;

            $row = [
                'id_hoa_don' => $hd->id,
                'phuong_thuc' => $pt,
                'so_tien' => (float) $hd->khach_can_tra,
                'trang_thai' => 'thanh_cong',
                'ma_tham_chieu' => null,
                'ma_giao_dich_doi_tac' => null,
                'ma_phan_hoi' => null,
                'trang_thai_doi_tac' => null,
                'du_lieu_phan_hoi' => null,
                'ma_ngan_hang' => null,
                'ngay_gio_thanh_toan' => $ngayThanhToan,
                'created_at' => $ngayThanhToan,
                'updated_at' => $ngayThanhToan,
                'deleted_at' => null,
            ];

            if ($pt === 'payos') {
                $orderCode = ((int) $hd->id) * 1000 + rand(1, 999);
                $row['ma_tham_chieu'] = (string) $orderCode;
                $row['ma_giao_dich_doi_tac'] = 'PAYOS-' . str_pad((string) $hd->id, 8, '0', STR_PAD_LEFT);
                $row['ma_phan_hoi'] = '00';
                $row['trang_thai_doi_tac'] = 'PAID';
                $row['ma_ngan_hang'] = ['VCB', 'TCB', 'MB', 'ACB', 'BIDV'][array_rand(['VCB', 'TCB', 'MB', 'ACB', 'BIDV'])];
                $row['du_lieu_phan_hoi'] = json_encode([
                    'order_code' => $orderCode,
                    'amount' => (float) $hd->khach_can_tra,
                    'status' => 'PAID',
                    'description' => 'Thanh toan hoa don #' . $hd->id,
                ]);
            } else {
                $row['ma_tham_chieu'] = 'TM' . str_pad((string) $hd->id, 8, '0', STR_PAD_LEFT);
                $row['du_lieu_phan_hoi'] = json_encode([
                    'amount' => (float) $hd->khach_can_tra,
                    'method' => 'cash',
                ]);
            }

            $rows[] = $row;
        }

        if (!empty($rows)) {
            DB::table('giao_dich')->insert($rows);
        }

        $this->command->info('[GiaoDichSeeder] Da tao '.count($rows).' giao dich thanh toan (chi tien_mat & payos).');
    }
}
