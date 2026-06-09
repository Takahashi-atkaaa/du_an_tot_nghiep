<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanHangSeeder extends Seeder
{
    public function run(): void
    {
        // Khuyen mai
        DB::table('khuyen_mai')->insert([
            [
                'ma_khuyen_mai' => 'KM001',
                'ten_khuyen_mai' => 'Giảm 10% cho hóa đơn từ 500k',
                'loai_khuyen_mai' => 'phan_tram',
                'gia_tri' => 10,
                'giam_toi_da' => 100000,
                'don_hang_toi_thieu' => 500000,
                'so_luong_sp_toi_thieu' => null,
                'ngay_bat_dau' => now()->subDays(10),
                'ngay_ket_thuc' => now()->addDays(20),
                'trang_thai' => 'dang_hoat_dong',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ma_khuyen_mai' => 'KM002',
                'ten_khuyen_mai' => 'Tặng quà cho khách hàng VIP',
                'loai_khuyen_mai' => 'qua_tang',
                'gia_tri' => 0,
                'giam_toi_da' => null,
                'don_hang_toi_thieu' => 1000000,
                'so_luong_sp_toi_thieu' => null,
                'ngay_bat_dau' => now()->subDays(5),
                'ngay_ket_thuc' => now()->addDays(25),
                'trang_thai' => 'dang_hoat_dong',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $khuyenMais = DB::table('khuyen_mai')->pluck('id')->toArray();
        $khachHangs = DB::table('khach_hang')->pluck('id')->toArray();
        $nguoiDungs = DB::table('nguoi_dung')->pluck('id')->toArray();

        if (count($khachHangs) && count($nguoiDungs)) {
            // Hoa don
            DB::table('hoa_don')->insert([
                [
                    'nguoi_dung_id' => $nguoiDungs[1],
                    'khach_hang_id' => $khachHangs[0],
                    'khuyen_mai_id' => $khuyenMais[0] ?? null,
                    'ca_lam_viec_id' => null,
                    'trang_thai' => 'da_thanh_toan',
                    'phuong_thuc_thanh_toan' => 'tien_mat',
                    'tong_tien_hang' => 1000000,
                    'tong_giam_gia' => 100000,
                    'tong_thanh_toan' => 900000,
                    'tien_khach_tra' => 1000000,
                    'tien_thua' => 100000,
                    'diem_su_dung' => 0,
                    'diem_thu_duoc' => 90,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ],
                [
                    'nguoi_dung_id' => $nguoiDungs[3],
                    'khach_hang_id' => $khachHangs[1],
                    'khuyen_mai_id' => null,
                    'ca_lam_viec_id' => null,
                    'trang_thai' => 'da_thanh_toan',
                    'phuong_thuc_thanh_toan' => 'chuyen_khoan',
                    'tong_tien_hang' => 500000,
                    'tong_giam_gia' => 0,
                    'tong_thanh_toan' => 500000,
                    'tien_khach_tra' => 500000,
                    'tien_thua' => 0,
                    'diem_su_dung' => 0,
                    'diem_thu_duoc' => 50,
                    'created_at' => now()->subDays(1),
                    'updated_at' => now()->subDays(1),
                ],
            ]);

            $hoaDonIds = DB::table('hoa_don')->pluck('id')->toArray();

            if (count($hoaDonIds) >= 2) {
                $sanPhams = DB::table('san_pham')->pluck('id')->toArray();

                // Chi tiet hoa don
                DB::table('chi_tiet_hoa_don')->insert([
                    [
                        'hoa_don_id' => $hoaDonIds[0],
                        'san_pham_id' => $sanPhams[0] ?? 1,
                        'id_chi_tiet_phieu' => null,
                        'so_luong' => 2,
                        'don_gia' => 500000,
                        'thanh_tien' => 1000000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'hoa_don_id' => $hoaDonIds[1],
                        'san_pham_id' => $sanPhams[1] ?? 2,
                        'id_chi_tiet_phieu' => null,
                        'so_luong' => 1,
                        'don_gia' => 500000,
                        'thanh_tien' => 500000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                // So quy
                DB::table('so_quy')->insert([
                    [
                        'nguoi_dung_id' => $nguoiDungs[1],
                        'loai_thu_chi' => 'thu',
                        'nhom_thu_chi' => 'hoa_don',
                        'id_khach_hang' => $khachHangs[0],
                        'id_nha_cung_cap' => null,
                        'id_hoa_don' => $hoaDonIds[0],
                        'id_phieu' => null,
                        'so_tien' => 900000,
                        'hinh_thuc' => 'tien_mat',
                        'ly_do' => 'Thu tiền hóa đơn',
                        'thoi_gian' => now()->subDays(2),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nguoi_dung_id' => $nguoiDungs[3],
                        'loai_thu_chi' => 'thu',
                        'nhom_thu_chi' => 'hoa_don',
                        'id_khach_hang' => $khachHangs[1],
                        'id_nha_cung_cap' => null,
                        'id_hoa_don' => $hoaDonIds[1],
                        'id_phieu' => null,
                        'so_tien' => 500000,
                        'hinh_thuc' => 'chuyen_khoan',
                        'ly_do' => 'Thu tiền hóa đơn',
                        'thoi_gian' => now()->subDays(1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                // Lich su tich diem
                DB::table('lich_su_tich_diem')->insert([
                    [
                        'khach_hang_id' => $khachHangs[0],
                        'hoa_don_id' => $hoaDonIds[0],
                        'loai_giao_dich' => 'cong',
                        'so_diem' => 90,
                        'so_diem_con_lai' => 210,
                        'mo_ta' => 'Tích điểm từ hóa đơn',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }
    }
}
