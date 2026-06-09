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
                'ten_chuong_trinh' => 'Giảm 10% cho hóa đơn từ 500k',
                'loai_giam_gia' => 'phan_tram',
                'gia_tri_giam' => 10,
                'giam_toi_da' => 100000,
                'don_hang_toi_thieu' => 500000,
                'so_luong_sp_toi_thieu' => null,
                'ngay_bat_dau' => now()->subDays(10),
                'ngay_ket_thuc' => now()->addDays(20),
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_chuong_trinh' => 'Tặng quà cho khách hàng VIP',
                'loai_giam_gia' => 'qua_tang',
                'gia_tri_giam' => 0,
                'giam_toi_da' => null,
                'don_hang_toi_thieu' => 1000000,
                'so_luong_sp_toi_thieu' => null,
                'ngay_bat_dau' => now()->subDays(5),
                'ngay_ket_thuc' => now()->addDays(25),
                'trang_thai' => true,
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
                    'id_nguoi_dung' => $nguoiDungs[1],
                    'id_khach_hang' => $khachHangs[0],
                    'id_ca_lam_viec' => null,
                    'id_khuyen_mai' => $khuyenMais[0] ?? null,
                    'tong_tien_hang' => 1000000,
                    'tien_giam_gia' => 100000,
                    'khach_can_tra' => 900000,
                    'tien_khach_dua' => 1000000,
                    'tien_thua' => 100000,
                    'phuong_thuc_thanh_toan' => 'tien_mat',
                    'trang_thai' => 'Hoàn thành',
                    'diem_su_dung' => 0,
                    'diem_thu_duoc' => 90,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ],
                [
                    'id_nguoi_dung' => $nguoiDungs[3],
                    'id_khach_hang' => $khachHangs[1],
                    'id_ca_lam_viec' => null,
                    'id_khuyen_mai' => null,
                    'tong_tien_hang' => 500000,
                    'tien_giam_gia' => 0,
                    'khach_can_tra' => 500000,
                    'tien_khach_dua' => 500000,
                    'tien_thua' => 0,
                    'phuong_thuc_thanh_toan' => 'chuyen_khoan',
                    'trang_thai' => 'Hoàn thành',
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
                        'id_hoa_don' => $hoaDonIds[0],
                        'id_san_pham' => $sanPhams[0] ?? 1,
                        'id_chi_tiet_phieu' => null,
                        'so_luong' => 2,
                        'gia_ban' => 500000,
                        'thanh_tien' => 1000000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'id_hoa_don' => $hoaDonIds[1],
                        'id_san_pham' => $sanPhams[1] ?? 2,
                        'id_chi_tiet_phieu' => null,
                        'so_luong' => 1,
                        'gia_ban' => 500000,
                        'thanh_tien' => 500000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                // So quy
                DB::table('so_quy')->insert([
                    [
                        'id_nguoi_dung' => $nguoiDungs[1],
                        'loai_thu_chi' => 'thu',
                        'nhom_thu_chi' => 'hoa_don',
                        'so_tien' => 900000,
                        'id_khach_hang' => $khachHangs[0],
                        'id_nha_cung_cap' => null,
                        'id_hoa_don' => $hoaDonIds[0],
                        'id_phieu' => null,
                        'ly_do' => 'Thu tiền hóa đơn',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'id_nguoi_dung' => $nguoiDungs[3],
                        'loai_thu_chi' => 'thu',
                        'nhom_thu_chi' => 'hoa_don',
                        'so_tien' => 500000,
                        'id_khach_hang' => $khachHangs[1],
                        'id_nha_cung_cap' => null,
                        'id_hoa_don' => $hoaDonIds[1],
                        'id_phieu' => null,
                        'ly_do' => 'Thu tiền hóa đơn',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);

                // Lich su tich diem
                DB::table('lich_su_tich_diem')->insert([
                    [
                        'id_khach_hang' => $khachHangs[0],
                        'id_hoa_don' => $hoaDonIds[0],
                        'loai_bien_dong' => 'tang',
                        'so_diem' => 90,
                        'ly_do' => 'Tích điểm từ hóa đơn',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }
    }
}
