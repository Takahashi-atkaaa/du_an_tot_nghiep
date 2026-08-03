<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DonViQuyDoiSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('don_vi_quy_doi')->count() > 0) {
            $this->command->info('[DonViQuyDoiSeeder] Bang da co du lieu. Bo qua.');
            return;
        }

        $now = now();

        $variants = DB::table('bien_the_san_pham')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        if ($variants->isEmpty()) {
            $this->command->warn('[DonViQuyDoiSeeder] Khong co variant. Bo qua.');
            return;
        }

        $count = 0;
        foreach ($variants as $v) {
            if (! $v->la_don_vi) {
                continue;
            }
            $soLuongQD = max(2, rand(6, 24));
            $tenDonVi = $this->taoTenDonVi($soLuongQD);

            DB::table('don_vi_quy_doi')->insert([
                'product_id' => $v->product_id,
                'variant_id' => $v->id,
                'don_vi_chuan_id' => null,
                'ten_don_vi' => $tenDonVi,
                'so_luong_san_pham_trong_don_vi' => $soLuongQD,
                'ma_hang' => $v->ma_hang . '-QD',
                'ma_vach' => '8934' . str_pad((string) $v->id, 8, '0', STR_PAD_LEFT),
                'gia_von_quy_doi' => (float) $v->gia_von * $soLuongQD,
                'gia_ban_quy_doi' => (float) $v->gia_ban * $soLuongQD,
                'gia_ban_si' => (float) $v->gia_ban * $soLuongQD * 0.85,
                'hinh_anh' => null,
                'la_don_vi_mac_dinh' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $count++;
        }

        $this->command->info('[DonViQuyDoiSeeder] Da tao '.$count.' don vi quy doi.');
    }

    private function taoTenDonVi(int $soLuong): string
    {
        return match (true) {
            $soLuong >= 24 => 'Thung',
            $soLuong >= 12 => 'Thung',
            $soLuong >= 6 => 'Loc',
            default => 'Bo',
        };
    }
}
