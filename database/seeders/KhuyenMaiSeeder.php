<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhuyenMaiSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $khuyenMais = [
            [
                'ten_chuong_trinh' => 'Giảm 10% cho hóa đơn từ 500k',
                'loai_giam_gia' => 'phan_tram',
                'gia_tri_giam' => 10,
                'giam_toi_da' => 100000,
                'don_hang_toi_thieu' => 500000,
                'so_luong_sp_toi_thieu' => null,
                'ngay_bat_dau' => $now->copy()->subDays(30),
                'ngay_ket_thuc' => $now->copy()->addDays(30),
                'ghi_chu' => 'Áp dụng cho mọi sản phẩm, giảm tối đa 100k',
                'trang_thai' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ten_chuong_trinh' => 'Giảm 20% cho đơn hàng từ 1 triệu',
                'loai_giam_gia' => 'phan_tram',
                'gia_tri_giam' => 20,
                'giam_toi_da' => 200000,
                'don_hang_toi_thieu' => 1000000,
                'so_luong_sp_toi_thieu' => null,
                'ngay_bat_dau' => $now->copy()->subDays(20),
                'ngay_ket_thuc' => $now->copy()->addDays(40),
                'ghi_chu' => 'Khuyến mãi lớn cuối tuần',
                'trang_thai' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ten_chuong_trinh' => 'Giảm 50k cho đơn từ 300k',
                'loai_giam_gia' => 'tien_mat',
                'gia_tri_giam' => 50000,
                'giam_toi_da' => 50000,
                'don_hang_toi_thieu' => 300000,
                'so_luong_sp_toi_thieu' => null,
                'ngay_bat_dau' => $now->copy()->subDays(15),
                'ngay_ket_thuc' => $now->copy()->addDays(15),
                'ghi_chu' => 'Giảm trực tiếp 50k',
                'trang_thai' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ten_chuong_trinh' => 'Tặng quà cho khách VIP',
                'loai_giam_gia' => 'qua_tang',
                'gia_tri_giam' => 0,
                'giam_toi_da' => null,
                'don_hang_toi_thieu' => 2000000,
                'so_luong_sp_toi_thieu' => null,
                'ngay_bat_dau' => $now->copy()->subDays(10),
                'ngay_ket_thuc' => $now->copy()->addDays(50),
                'ghi_chu' => 'Tặng quà cho khách VIP mua hóa đơn trên 2 triệu',
                'trang_thai' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ten_chuong_trinh' => 'Flash sale giảm 30% đồ uống',
                'loai_giam_gia' => 'phan_tram',
                'gia_tri_giam' => 30,
                'giam_toi_da' => 50000,
                'don_hang_toi_thieu' => 100000,
                'so_luong_sp_toi_thieu' => null,
                'ngay_bat_dau' => $now->copy()->subDays(5),
                'ngay_ket_thuc' => $now->copy()->addDays(7),
                'ghi_chu' => 'Flash sale đồ uống cuối tuần',
                'trang_thai' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('khuyen_mai')->insert($khuyenMais);

        $this->command->info('[KhuyenMaiSeeder] Da tao '.count($khuyenMais).' chuong trinh khuyen mai.');
    }
}