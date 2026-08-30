<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Snapshot danh sach id_chi_tiet_lo_hang de audit
        Schema::table('chi_tiet_kiem_kho', function (Blueprint $table) {
            $table->json('lo_hang_snapshot')->nullable()->after('so_lo_con_ton')
                ->comment('Snapshot JSON: [{id_chi_tiet_lo_hang, so_luong_ton, gia_nhap, han_su_dung}]');
        });
    }

    public function down(): void
    {
        Schema::table('chi_tiet_kiem_kho', function (Blueprint $table) {
            $table->dropColumn('lo_hang_snapshot');
        });
    }
};