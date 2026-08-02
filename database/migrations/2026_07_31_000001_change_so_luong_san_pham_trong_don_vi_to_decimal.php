<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Đổi cột `so_luong_san_pham_trong_don_vi` của bảng don_vi_quy_doi sang DECIMAL(12,4).
     * Lý do: hỗ trợ trường hợp đảo đơn vị cơ bản (base ↔ conversion) → rate có thể là
     * số thập phân nhỏ hơn 1 (ví dụ 1 Cái = 1/24 Thùng ≈ 0.0417).
     */
    public function up(): void
    {
        // Dùng raw SQL để tránh phụ thuộc doctrine/dbal (Laravel cần nó cho change())
        DB::statement('ALTER TABLE don_vi_quy_doi MODIFY COLUMN so_luong_san_pham_trong_don_vi DECIMAL(12,4) NOT NULL DEFAULT 1');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE don_vi_quy_doi MODIFY COLUMN so_luong_san_pham_trong_don_vi INT NOT NULL DEFAULT 1');
    }
};