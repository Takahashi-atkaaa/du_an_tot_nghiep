<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mở rộng enum loai_phieu_enum trên bảng phieu
        DB::statement("ALTER TABLE `phieu` MODIFY COLUMN `loai_phieu_enum` ENUM(
            'nhap_mua_hang',
            'nhap_tra_lai_tu_khach',
            'nhap_kiem_ke',
            'xuat_tra_hang_nha_cung_cap',
            'xuat_tieu_huy',
            'xuat_kiem_ke'
        ) NULL");

        // Mở rộng enum loai_nhap trên bảng phieu_nhap
        DB::statement("ALTER TABLE `phieu_nhap` MODIFY COLUMN `loai_nhap` ENUM('mua_hang','tra_lai_tu_khach','kiem_ke') DEFAULT 'mua_hang'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `phieu` MODIFY COLUMN `loai_phieu_enum` ENUM(
            'nhap_mua_hang',
            'nhap_tra_lai_tu_khach',
            'xuat_tra_hang_nha_cung_cap',
            'xuat_tieu_huy'
        ) NULL");

        DB::statement("ALTER TABLE `phieu_nhap` MODIFY COLUMN `loai_nhap` ENUM('mua_hang','tra_lai_tu_khach') DEFAULT 'mua_hang'");
    }
};