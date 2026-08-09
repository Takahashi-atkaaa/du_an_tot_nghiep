<?php

namespace Database\Seeders;

use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NhanSuSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ca_lam_viec')->count() === 0) {
            DB::table('ca_lam_viec')->insert([
            [
                'ten_ca' => 'Ngay',
                'gio_bat_dau' => '08:00:00',
                'gio_ket_thuc' => '16:00:00',
                'so_nhan_vien_toi_thieu' => 2,
                'so_nhan_vien_toi_da' => 4,
                'so_phut_di_lam_tre_toi_da' => 15,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_ca' => 'Toi',
                'gio_bat_dau' => '16:00:00',
                'gio_ket_thuc' => '00:00:00',
                'so_nhan_vien_toi_thieu' => 2,
                'so_nhan_vien_toi_da' => 4,
                'so_phut_di_lam_tre_toi_da' => 15,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_ca' => 'Dem',
                'gio_bat_dau' => '00:00:00',
                'gio_ket_thuc' => '08:00:00',
                'so_nhan_vien_toi_thieu' => 2,
                'so_nhan_vien_toi_da' => 4,
                'so_phut_di_lam_tre_toi_da' => 15,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_ca' => 'Sang',
                'gio_bat_dau' => '06:00:00',
                'gio_ket_thuc' => '14:00:00',
                'so_nhan_vien_toi_thieu' => 3,
                'so_nhan_vien_toi_da' => 5,
                'so_phut_di_lam_tre_toi_da' => 15,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_ca' => 'Chieu',
                'gio_bat_dau' => '14:00:00',
                'gio_ket_thuc' => '22:00:00',
                'so_nhan_vien_toi_thieu' => 3,
                'so_nhan_vien_toi_da' => 5,
                'so_phut_di_lam_tre_toi_da' => 15,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        }

        // Nhan vien
        if (DB::table('nguoi_dung')->count() === 0) {
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
        }

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
        }
    }
}
