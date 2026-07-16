<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phieu', function (Blueprint $table) {
            $table->enum('loai_phieu_enum', [
                'nhap_mua_hang',
                'nhap_tra_lai_tu_khach',
                'xuat_tra_hang_nha_cung_cap',
                'xuat_tieu_huy',
            ])->nullable()->after('loai_phieu');

            $table->string('ly_do')->nullable()->after('ghi_chu');
        });
    }

    public function down(): void
    {
        Schema::table('phieu', function (Blueprint $table) {
            $table->dropColumn(['loai_phieu_enum', 'ly_do']);
        });
    }
};
