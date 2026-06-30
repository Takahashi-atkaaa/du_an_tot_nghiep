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

            // Thiet lap luong - cho all 4 nguoi dung
            $salaryConfigs = [
                ['id_nguoi_dung' => $nguoiDungIds[0], 'luong_theo_gio' => 100000, 'phu_cap' => 2000000, 'thuong' => 1000000, 'phat_di_muon' => 0, 'luong_tang_ca' => 50000, 'phat_bo_ca' => 0],
                ['id_nguoi_dung' => $nguoiDungIds[1], 'luong_theo_gio' => 80000, 'phu_cap' => 1000000, 'thuong' => 500000, 'phat_di_muon' => 100000, 'luong_tang_ca' => 40000, 'phat_bo_ca' => 50000],
                ['id_nguoi_dung' => $nguoiDungIds[2], 'luong_theo_gio' => 75000, 'phu_cap' => 1500000, 'thuong' => 800000, 'phat_di_muon' => 50000, 'luong_tang_ca' => 35000, 'phat_bo_ca' => 30000],
                ['id_nguoi_dung' => $nguoiDungIds[3], 'luong_theo_gio' => 70000, 'phu_cap' => 800000, 'thuong' => 600000, 'phat_di_muon' => 150000, 'luong_tang_ca' => 30000, 'phat_bo_ca' => 40000],
            ];
            foreach ($salaryConfigs as $config) {
                DB::table('thiet_lap_luong')->insert(array_merge($config, [
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            // Bang luong - 2 ky
            $bangLuongMayId = DB::table('bang_luong')->insertGetId([
                'ten_bang_luong' => 'Bảng lương tháng 05/2026',
                'ky_lam_viec' => '05/2026',
                'tong_luong_tat_ca_nhan_vien' => 85500000,
                'so_nhan_vien' => 4,
                'trang_thai' => 'Đã chi',
                'deleted_at' => null,
                'created_at' => now()->subMonth(),
                'updated_at' => now()->subMonth(),
            ]);
            $bangLuongJuneId = DB::table('bang_luong')->insertGetId([
                'ten_bang_luong' => 'Bảng lương tháng 06/2026',
                'ky_lam_viec' => '06/2026',
                'tong_luong_tat_ca_nhan_vien' => 0,
                'so_nhan_vien' => 0,
                'trang_thai' => 'Nháp',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Phieu luong - 4 nhan vien x 2 ky
            $phieuConfigsMay = [
                ['luong_tam_tinh' => 24000000, 'tong_gio_lam_thuc_te' => 176, 'tong_luong' => 27000000, 'trang_thai' => 'Đã chi'],
                ['luong_tam_tinh' => 18000000, 'tong_gio_lam_thuc_te' => 168, 'tong_luong' => 19000000, 'trang_thai' => 'Đã chi'],
                ['luong_tam_tinh' => 21000000, 'tong_gio_lam_thuc_te' => 172, 'tong_luong' => 23000000, 'trang_thai' => 'Đã chi'],
                ['luong_tam_tinh' => 16500000, 'tong_gio_lam_thuc_te' => 160, 'tong_luong' => 16500000, 'trang_thai' => 'Đã chi'],
            ];
            $phieuConfigsJune = [
                ['luong_tam_tinh' => 24000000, 'tong_gio_lam_thuc_te' => 176, 'tong_luong' => 27000000, 'trang_thai' => 'Chưa chi'],
                ['luong_tam_tinh' => 18000000, 'tong_gio_lam_thuc_te' => 168, 'tong_luong' => 19000000, 'trang_thai' => 'Chưa chi'],
                ['luong_tam_tinh' => 21000000, 'tong_gio_lam_thuc_te' => 172, 'tong_luong' => 23000000, 'trang_thai' => 'Chưa chi'],
                ['luong_tam_tinh' => 16500000, 'tong_gio_lam_thuc_te' => 160, 'tong_luong' => 16500000, 'trang_thai' => 'Chưa chi'],
            ];
            foreach ($nguoiDungIds as $index => $id) {
                DB::table('phieu_luong')->insert([
                    'id_nguoi_dung' => $id,
                    'id_bang_luong' => $bangLuongMayId,
                    'luong_tam_tinh' => $phieuConfigsMay[$index]['luong_tam_tinh'],
                    'trang_thai' => $phieuConfigsMay[$index]['trang_thai'],
                    'tong_gio_lam_thuc_te' => $phieuConfigsMay[$index]['tong_gio_lam_thuc_te'],
                    'tong_luong' => $phieuConfigsMay[$index]['tong_luong'],
                    'deleted_at' => null,
                    'created_at' => now()->subMonth(),
                    'updated_at' => now()->subMonth(),
                ]);
                DB::table('phieu_luong')->insert([
                    'id_nguoi_dung' => $id,
                    'id_bang_luong' => $bangLuongJuneId,
                    'luong_tam_tinh' => $phieuConfigsJune[$index]['luong_tam_tinh'],
                    'trang_thai' => $phieuConfigsJune[$index]['trang_thai'],
                    'tong_gio_lam_thuc_te' => $phieuConfigsJune[$index]['tong_gio_lam_thuc_te'],
                    'tong_luong' => $phieuConfigsJune[$index]['tong_luong'],
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
