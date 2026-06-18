<?php

namespace Database\Seeders;

use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NhanSuSeeder extends Seeder
{
    public function run(): void
    {
        // Ca lam viec
        DB::table('ca_lam_viec')->insert([
            [
                'ten_ca' => 'SA1',
                'gio_bat_dau' => '07:00:00',
                'gio_ket_thuc' => '12:00:00',
                'so_nhan_vien_toi_thieu' => 2,
                'so_nhan_vien_toi_da' => 4,
                'so_phut_di_lam_tre_toi_da' => 15,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_ca' => 'CH1',
                'gio_bat_dau' => '12:00:00',
                'gio_ket_thuc' => '19:00:00',
                'so_nhan_vien_toi_thieu' => 2,
                'so_nhan_vien_toi_da' => 4,
                'so_phut_di_lam_tre_toi_da' => 15,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_ca' => 'TO1',
                'gio_bat_dau' => '19:00:00',
                'gio_ket_thuc' => '01:00:00',
                'so_nhan_vien_toi_thieu' => 2,
                'so_nhan_vien_toi_da' => 4,
                'so_phut_di_lam_tre_toi_da' => 15,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_ca' => 'TO2',
                'gio_bat_dau' => '01:00:00',
                'gio_ket_thuc' => '07:00:00',
                'so_nhan_vien_toi_thieu' => 2,
                'so_nhan_vien_toi_da' => 4,
                'so_phut_di_lam_tre_toi_da' => 15,
                'deleted_at' => null,
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
                'id_vai_tro' => VaiTro::where('ten_vai_tro', 'Admin')->first()->id,
                'trang_thai' => true,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ho_ten' => 'Trần Thị Thu Ngân',
                'email' => 'thungan@smartmart.vn',
                'mat_khau' => bcrypt('ThuNgan@123'),
                'sdt' => '0987654321',
                'id_vai_tro' => VaiTro::where('ten_vai_tro', 'Trưởng ca')->first()->id,
                'trang_thai' => true,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ho_ten' => 'Lê Văn Bảo',
                'email' => 'baohang@smartmart.vn',
                'mat_khau' => bcrypt('BaoHang@123'),
                'sdt' => '0977112233',
                'id_vai_tro' => VaiTro::where('ten_vai_tro', 'Nhân viên')->first()->id,
                'trang_thai' => true,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ho_ten' => 'Phạm Thị Hồng',
                'email' => 'hong@smartmart.vn',
                'mat_khau' => bcrypt('Hong@123'),
                'sdt' => '0966887766',
                'id_vai_tro' => VaiTro::where('ten_vai_tro', 'Nhân viên')->first()->id,
                'trang_thai' => true,
                'deleted_at' => null,
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
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_nguoi_dung' => $nguoiDungIds[1],
                    'id_ca_lam_viec' => $caIds[1],
                    'ngay' => now()->subDays(1)->toDateString(),
                    'deleted_at' => null,
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
                    'trang_thai_vao_lam' => 'Đúng giờ',
                    'trang_thai_tan_ca' => 'Tăng ca',
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_chia_ca_lam_viec' => $chiaCaIds[1],
                    'gio_vao' => now()->subDays(1)->setTime(13, 5, 0),
                    'so_gio_di_lam_muon' => 5,
                    'gio_tan_ca' => null,
                    'so_gio_lam_them' => 0,
                    'trang_thai_vao_lam' => 'Đi muộn',
                    'trang_thai_tan_ca' => null,
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
            // Thiet lap luong
            DB::table('thiet_lap_luong')->insert([
                [
                    'id_nguoi_dung' => $nguoiDungIds[0],
                    'luong_theo_gio' => 100000,
                    'phu_cap' => 2000000,
                    'thuong' => 1000000,
                    'phat_di_muon' => 0,
                    'luong_tang_ca' => 0,
                    'phat_bo_ca' => 0,
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id_nguoi_dung' => $nguoiDungIds[1],
                    'luong_theo_gio' => 80000,
                    'phu_cap' => 1000000,
                    'thuong' => 500000,
                    'phat_di_muon' => 100000,
                    'luong_tang_ca' => 0,
                    'phat_bo_ca' => 0,
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
