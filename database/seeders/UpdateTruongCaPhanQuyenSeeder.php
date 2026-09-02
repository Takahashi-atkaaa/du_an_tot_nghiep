<?php

namespace Database\Seeders;

use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateTruongCaPhanQuyenSeeder extends Seeder
{
    /**
     * Cập nhật phân quyền cho Trưởng ca
     * Chỉ cấp 10 nhóm quyền được chỉ định
     */
    public function run(): void
    {
        // Lấy vai trò Trưởng ca (id = 2)
        $truongCa = VaiTro::find(2);
        
        if (!$truongCa) {
            $this->command->error('Không tìm thấy vai trò Trưởng ca (id=2)');
            return;
        }

        $allQuyens = DB::table('quyen')->pluck('id', 'ma_quyen');
        $permissionCodes = config('permissions.truong_ca', []);
        $permissionIds = collect($permissionCodes)
            ->map(fn (string $code) => $allQuyens->get($code))
            ->filter()
            ->values()
            ->all();

        $truongCa->quyens()->sync($permissionIds);
        $assignedCount = count($permissionIds);

        // Hiển thị tóm tắt
        $this->command->info("\n=== TÓM TẮT PHÂN QUYỀN TRƯỞNG CA ===");
        $this->command->info("Tổng số quyền: $assignedCount");
        $this->command->info("\nCác module được cấp quyền:");
        $this->command->info("✓ Bán hàng");
        $this->command->info("✓ Thống kê");
        $this->command->info("✓ Hóa đơn");
        $this->command->info("✓ Sản phẩm (không cấp quyền xóa mặc định)");
        $this->command->info("✓ Danh mục");
        $this->command->info("✓ Kho hàng + Kiểm kho (không cấp hủy/điều chỉnh mặc định)");
        $this->command->info("✓ Hàng lỗi");
        $this->command->info("✓ Khách hàng");
        $this->command->info("✓ Khuyến mãi");
        $this->command->info("✓ Quản lý ca làm");
        
        $this->command->info("\nCác module KHÔNG được cấp:");
        $this->command->warn("✗ Nhân sự");
        $this->command->warn("✗ Phân quyền");
        $this->command->warn("✗ Nhà cung cấp");
        $this->command->warn("✗ Cài đặt");
    }
}
