<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThuocTinhSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('thuoc_tinh_san_pham')->count() > 0) {
            $this->command->info('[ThuocTinhSeeder] Bang da co du lieu. Bo qua.');
            return;
        }

        $now = now();

        $parentVi = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh' => 'Vi',
            'trang_thai' => true,
            'thuoc_tinh_cha_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        foreach (['Ngot', 'Cay', 'Chua', 'Man', 'Dang'] as $ten) {
            DB::table('thuoc_tinh_san_pham')->insert([
                'ten_thuoc_tinh' => $ten,
                'trang_thai' => true,
                'thuoc_tinh_cha_id' => $parentVi,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $parentDungTich = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh' => 'Dung tich',
            'trang_thai' => true,
            'thuoc_tinh_cha_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        foreach (['250ml', '330ml', '500ml', '750ml', '1L', '1.5L'] as $ten) {
            DB::table('thuoc_tinh_san_pham')->insert([
                'ten_thuoc_tinh' => $ten,
                'trang_thai' => true,
                'thuoc_tinh_cha_id' => $parentDungTich,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $parentTrongLuong = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh' => 'Trong luong',
            'trang_thai' => true,
            'thuoc_tinh_cha_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        foreach (['100g', '200g', '500g', '1kg', '2kg', '5kg'] as $ten) {
            DB::table('thuoc_tinh_san_pham')->insert([
                'ten_thuoc_tinh' => $ten,
                'trang_thai' => true,
                'thuoc_tinh_cha_id' => $parentTrongLuong,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $parentXuatXu = DB::table('thuoc_tinh_san_pham')->insertGetId([
            'ten_thuoc_tinh' => 'Xuat xu',
            'trang_thai' => true,
            'thuoc_tinh_cha_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        foreach (['Viet Nam', 'Thai Lan', 'Nhat Ban', 'Han Quoc', 'My', 'Australia'] as $ten) {
            DB::table('thuoc_tinh_san_pham')->insert([
                'ten_thuoc_tinh' => $ten,
                'trang_thai' => true,
                'thuoc_tinh_cha_id' => $parentXuatXu,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $count = DB::table('thuoc_tinh_san_pham')->count();
        $this->command->info('[ThuocTinhSeeder] Da tao '.$count.' thuoc tinh (me + con).');
    }
}
