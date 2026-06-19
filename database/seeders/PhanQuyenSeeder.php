<?php

namespace Database\Seeders;

use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhanQuyenSeeder extends Seeder
{
    public function run(): void
    {
        $admin = VaiTro::where('ten_vai_tro', 'Admin')->first();
        $truongCa = VaiTro::where('ten_vai_tro', 'Trưởng ca')->first();
        $nhanVien = VaiTro::where('ten_vai_tro', 'Nhân viên')->first();

        $allQuyens = DB::table('quyen')->pluck('id', 'ma_quyen');

        // Admin: toàn quyền
        foreach ($allQuyens as $id) {
            DB::table('quyen_vai_tro')->insert([
                'id_vai_tro' => $admin->id,
                'id_quyen' => $id,
            ]);
        }

        // Trưởng ca
        $truongCaQuyens = [
            'xem_danh_muc', 'them_danh_muc', 'sua_danh_muc',
            'xem_san_pham', 'them_san_pham', 'sua_san_pham',
            'xem_nha_cung_cap', 'them_nha_cung_cap', 'sua_nha_cung_cap',
            'xem_lich_su_giao_dich',
            'xem_ca_lam_viec', 'them_ca_lam_viec', 'sua_ca_lam_viec',
            'xem_nguoi_dung',
            'xem_khach_hang', 'them_khach_hang', 'sua_khach_hang',
            'xem_khuyen_mai', 'them_khuyen_mai', 'sua_khuyen_mai',
            'xem_chia_ca_lam_viec', 'them_chia_ca_lam_viec', 'sua_chia_ca_lam_viec', 'xoa_chia_ca_lam_viec',
        ];
        foreach ($truongCaQuyens as $maQuyen) {
            if (isset($allQuyens[$maQuyen])) {
                DB::table('quyen_vai_tro')->insert([
                    'id_vai_tro' => $truongCa->id,
                    'id_quyen' => $allQuyens[$maQuyen],
                ]);
            }
        }

        // Nhân viên
        $nhanVienQuyens = [
            'xem_danh_muc',
            'xem_san_pham', 'them_san_pham',
            'xem_nha_cung_cap',
            'xem_ca_lam_viec',
            'xem_nguoi_dung',
            'xem_khach_hang', 'them_khach_hang', 'sua_khach_hang',
            'xem_khuyen_mai',
            'xem_chia_ca_lam_viec',
        ];
        foreach ($nhanVienQuyens as $maQuyen) {
            if (isset($allQuyens[$maQuyen])) {
                DB::table('quyen_vai_tro')->insert([
                    'id_vai_tro' => $nhanVien->id,
                    'id_quyen' => $allQuyens[$maQuyen],
                ]);
            }
        }
    }
}
