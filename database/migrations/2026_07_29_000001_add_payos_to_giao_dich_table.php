<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE giao_dich MODIFY COLUMN phuong_thuc ENUM('tien_mat', 'vnpay', 'payos') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE giao_dich SET phuong_thuc = 'tien_mat' WHERE phuong_thuc = 'payos'");
        DB::statement("ALTER TABLE giao_dich MODIFY COLUMN phuong_thuc ENUM('tien_mat', 'vnpay') NOT NULL");
    }
};