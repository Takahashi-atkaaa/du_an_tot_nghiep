<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bang_luong MODIFY COLUMN so_nhan_vien INT UNSIGNED NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE bang_luong MODIFY COLUMN trang_thai VARCHAR(50) NOT NULL DEFAULT 'cho_duyet'");
        DB::statement("ALTER TABLE thiet_lap_luong MODIFY COLUMN luong_theo_gio DECIMAL(14,2) NOT NULL DEFAULT 0");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bang_luong MODIFY COLUMN so_nhan_vien INT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE bang_luong MODIFY COLUMN trang_thai VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE thiet_lap_luong MODIFY COLUMN luong_theo_gio DECIMAL(14,2) NULL");
    }
};
