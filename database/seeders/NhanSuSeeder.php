<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NhanSuSeeder extends Seeder
{
    public function run(): void
    {
        // Ca lam viec
        DB::table('ca_lam_viec')->insert([
            [
                'ten_ca' => 'Ca sáng',
                'gio_bat_dau' => '08:00:00',
                'gio_ket_thuc' => '12:00:00',
                'so_phut_di_lam_tre_toi_da' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_ca' => 'Ca chiều',
                'gio_bat_dau' => '13:00:00',
                'gio_ket_thuc' => '17:00:00',
                'so_phut_di_lam_tre_toi_da' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_ca' => 'Ca tối',
                'gio_bat_dau' => '18:00:00',
                'gio_ket_thuc' => '22:00:00',
                'so_phut_di_lam_tre_toi_da' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Nhan vien
        DB::table('nguoi_dung')->insert([
            [
                'ho_ten' => 'Nguyễn Tùng Anh',
                'email' => 'tunganh@smartmart.vn',
                'mat_khau' => bcrypt('Admin@123'),
                'sdt' => '0912345678',
                'vai_tro' => 'admin',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ho_ten' => 'Trần Thị Thu Ngân',
                'email' => 'thungan@smartmart.vn',
                'mat_khau' => bcrypt('ThuNgan@123'),
                'sdt' => '0987654321',
                'vai_tro' => 'truong_ca',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ho_ten' => 'Lê Văn Bảo',
                'email' => 'baohang@smartmart.vn',
                'mat_khau' => bcrypt('BaoHang@123'),
                'sdt' => '0977112233',
                'vai_tro' => 'nhan_vien',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ho_ten' => 'Phạm Thị Hồng',
                'email' => 'hong@smartmart.vn',
                'mat_khau' => bcrypt('Hong@123'),
                'sdt' => '0966887766',
                'vai_tro' => 'nhan_vien',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $nguoiDungIds = DB::table('nguoi_dung')->pluck('id')->toArray();
        $caIds = DB::table('ca_lam_viec')->pluck('id')->toArray();

        if (count($nguoiDungIds) && count($caIds)) {
            // Chia ca lam viec
            DB::table('chia_ca_lam_viec')->insert([
                [
                    'id_nguoi_dung' => $nguoiDungIds[0],
                    'id_ca_lam_viec' => $caIds[0],
                    'ngay' => now()->subDays(1)->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_nguoi_dung' => $nguoiDungIds[1],
                    'id_ca_lam_viec' => $caIds[1],
                    'ngay' => now()->subDays(1)->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $chiaCaIds = DB::table('chia_ca_lam_viec')->pluck('id')->toArray();

            // Diem danh
            DB::table('diem_danh')->insert([
                [
                    'id_chia_ca_lam_viec' => $chiaCaIds[0],
                    'gio_vao' => now()->subDays(1)->setTime(7, 58, 0),
                    'so_gio_di_lam_muon' => 0,
                    'gio_tan_ca' => now()->subDays(1)->setTime(12, 5, 0),
                    'so_gio_lam_them' => 0.08,
                    'trang_thai' => 'Đúng giờ',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_chia_ca_lam_viec' => $chiaCaIds[1],
                    'gio_vao' => now()->subDays(1)->setTime(13, 5, 0),
                    'so_gio_di_lam_muon' => 5,
                    'gio_tan_ca' => null,
                    'so_gio_lam_them' => 0,
                    'trang_thai' => 'Đi muộn',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Thiet lap luong
            DB::table('thiet_lap_luong')->insert([
                [
                    'id_nguoi_dung' => $nguoiDungIds[0],
                    'luong_co_ban' => 15000000,
                    'luong_theo_gio' => null,
                    'phu_cap' => 2000000,
                    'thuong' => 1000000,
                    'phat_di_muon' => 0,
                    'luong_tang_ca' => 0,
                    'phat_bo_ca' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_nguoi_dung' => $nguoiDungIds[1],
                    'luong_co_ban' => 10000000,
                    'luong_theo_gio' => null,
                    'phu_cap' => 1000000,
                    'thuong' => 500000,
                    'phat_di_muon' => 100000,
                    'luong_tang_ca' => 0,
                    'phat_bo_ca' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
