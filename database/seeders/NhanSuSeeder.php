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
                'ho_ten' => 'Nguyễn Văn Admin',
                'email' => 'admin@smartmart.vn',
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
                'vai_tro' => 'quan_ly',
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
            DB::table('chia_ca_lam_viec')->insert([
                [
                    'nguoi_dung_id' => $nguoiDungIds[0],
                    'ca_lam_viec_id' => $caIds[0],
                    'ngay_lam' => now()->subDays(1)->toDateString(),
                    'gio_vao_ca_thuc_te' => '07:58:00',
                    'gio_ra_ca_thuc_te' => '12:05:00',
                    'di_muon' => false,
                    've_som' => false,
                    'ghi_chu' => 'Đi làm đúng giờ',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nguoi_dung_id' => $nguoiDungIds[1],
                    'ca_lam_viec_id' => $caIds[1],
                    'ngay_lam' => now()->subDays(1)->toDateString(),
                    'gio_vao_ca_thuc_te' => '13:05:00',
                    'gio_ra_ca_thuc_te' => '17:00:00',
                    'di_muon' => true,
                    've_som' => false,
                    'ghi_chu' => 'Đi muộn 5 phút',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $chiaCaIds = DB::table('chia_ca_lam_viec')->pluck('id')->toArray();

            DB::table('diem_danh')->insert([
                [
                    'nguoi_dung_id' => $nguoiDungIds[0],
                    'chia_ca_lam_viec_id' => $chiaCaIds[0],
                    'ngay_diem_danh' => now()->toDateString(),
                    'gio_vao' => '07:58:00',
                    'gio_ra' => '12:05:00',
                    'so_gio_di_lam_muon' => 0,
                    'so_gio_lam_them' => 0.08,
                    'ghi_chu' => 'Điểm danh đúng giờ',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nguoi_dung_id' => $nguoiDungIds[1],
                    'chia_ca_lam_viec_id' => $chiaCaIds[1],
                    'ngay_diem_danh' => now()->toDateString(),
                    'gio_vao' => '13:05:00',
                    'gio_ra' => null,
                    'so_gio_di_lam_muon' => 0.08,
                    'so_gio_lam_them' => 0,
                    'ghi_chu' => 'Đi muộn',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Thiet lap luong
            DB::table('thiet_lap_luong')->insert([
                [
                    'nguoi_dung_id' => $nguoiDungIds[0],
                    'thang_ap_dung' => now()->startOfMonth()->toDateString(),
                    'luong_co_ban' => 15000000,
                    'luong_theo_gio' => 89000,
                    'phu_cap' => 2000000,
                    'thuong' => 1000000,
                    'khau_tru' => 0,
                    'phat_di_muon' => 0,
                    'luong_tang_ca' => 0,
                    'phat_bo_ca' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nguoi_dung_id' => $nguoiDungIds[1],
                    'thang_ap_dung' => now()->startOfMonth()->toDateString(),
                    'luong_co_ban' => 10000000,
                    'luong_theo_gio' => 59000,
                    'phu_cap' => 1000000,
                    'thuong' => 500000,
                    'khau_tru' => 0,
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
