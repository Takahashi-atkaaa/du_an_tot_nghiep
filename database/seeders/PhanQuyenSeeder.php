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
        $banHang = VaiTro::where('ten_vai_tro', 'Bán hàng')->first();

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
            'ban_hang',
            'quan_ly_ca_lam',
            'quan_ly_hoa_don',
            'quan_ly_khach_hang',
        ];
        foreach ($truongCaQuyens as $maQuyen) {
            if (isset($allQuyens[$maQuyen])) {
                DB::table('quyen_vai_tro')->insert([
                    'id_vai_tro' => $truongCa->id,
                    'id_quyen' => $allQuyens[$maQuyen],
                ]);
            }
        }

        // Bán hàng
        $banHangQuyens = [
            'ban_hang',
            'quan_ly_khach_hang', // nếu được phép thêm/sửa khách hàng khi bán
        ];

        foreach ($banHangQuyens as $maQuyen) {
            if (isset($allQuyens[$maQuyen])) {
                DB::table('quyen_vai_tro')->insert([
                    'id_vai_tro' => $banHang->id,
                    'id_quyen' => $allQuyens[$maQuyen],
                ]);
            }
        }
  
    }
}
