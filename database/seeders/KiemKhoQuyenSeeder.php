<?php

namespace Database\Seeders;

use App\Models\Quyen;
use Illuminate\Database\Seeder;

/**
 * Seeder chuyên dụng để thêm 6 permission cho module Kiểm kho.
 * Chạy độc lập với QuyenSeeder để dễ bổ sung mà không ảnh hưởng dữ liệu khác.
 *
 * Lệnh: php artisan db:seed --class=KiemKhoQuyenSeeder
 */
class KiemKhoQuyenSeeder extends Seeder
{
    public function run(): void
    {
        $quyens = [
            ['ma_quyen' => 'xem_kiem_kho',        'ten_quyen' => 'Xem kiểm kho'],
            ['ma_quyen' => 'them_kiem_kho',       'ten_quyen' => 'Thêm phiếu kiểm kho'],
            ['ma_quyen' => 'sua_kiem_kho',        'ten_quyen' => 'Sửa phiếu kiểm kho'],
            ['ma_quyen' => 'xoa_kiem_kho',        'ten_quyen' => 'Xóa phiếu kiểm kho'],
            ['ma_quyen' => 'can_bang_kiem_kho',   'ten_quyen' => 'Cân bằng kho kiểm kê'],
            ['ma_quyen' => 'huy_kiem_kho',        'ten_quyen' => 'Hủy phiếu kiểm kho'],
        ];

        foreach ($quyens as $q) {
            Quyen::firstOrCreate(['ma_quyen' => $q['ma_quyen']], ['ten_quyen' => $q['ten_quyen']]);
        }

        $this->command?->info('Đã thêm ' . count($quyens) . ' quyền cho module Kiểm kho.');
    }
}
