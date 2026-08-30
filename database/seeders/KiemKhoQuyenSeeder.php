<?php

namespace Database\Seeders;

use App\Models\Quyen;
use Illuminate\Database\Seeder;

class KiemKhoQuyenSeeder extends Seeder
{
    /**
     * Insert 6 quyền mới cho module kiểm kho
     */
    public function run(): void
    {
        $quyens = [
            ['ma_quyen' => 'kiem_kho_xem',        'ten_quyen' => 'Xem kiểm kho'],
            ['ma_quyen' => 'kiem_kho_tao',        'ten_quyen' => 'Tạo/Sửa phiếu kiểm kho'],
            ['ma_quyen' => 'kiem_kho_dem',        'ten_quyen' => 'Kiểm đếm hàng'],
            ['ma_quyen' => 'kiem_kho_duyet',      'ten_quyen' => 'Duyệt/Từ chối phiếu'],
            ['ma_quyen' => 'kiem_kho_dieu_chinh', 'ten_quyen' => 'Hoàn tất điều chỉnh kho'],
            ['ma_quyen' => 'kiem_kho_huy',        'ten_quyen' => 'Hủy/Xóa phiếu kiểm kho'],
        ];

        foreach ($quyens as $quyen) {
            Quyen::firstOrCreate(
                ['ma_quyen' => $quyen['ma_quyen']],
                ['ten_quyen' => $quyen['ten_quyen']]
            );
        }

        // Gan cho admin (id_vai_tro = 1) - tat ca quyen
        $adminRole = \App\Models\VaiTro::find(1);
        if ($adminRole) {
            $quyenIds = Quyen::whereIn('ma_quyen', array_column($quyens, 'ma_quyen'))->pluck('id')->all();
            $adminRole->quyens()->syncWithoutDetaching($quyenIds);
        }
        // Các vai trò khác (trưởng ca, NV kho, NV bán hàng) sẽ được phân quyền sau qua UI Phân quyền.
    }
}