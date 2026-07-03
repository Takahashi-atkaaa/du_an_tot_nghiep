<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bang_luong', function (Blueprint $table) {
            $table->date('ngay_bat_dau')->nullable()->after('ten_bang_luong');
            $table->date('ngay_ket_thuc')->nullable()->after('ngay_bat_dau');
            $table->dropColumn('ky_lam_viec');
        });
    }

    public function down(): void
    {
        Schema::table('bang_luong', function (Blueprint $table) {
            $table->string('ky_lam_viec')->nullable()->after('ten_bang_luong');
            $table->dropColumn(['ngay_bat_dau', 'ngay_ket_thuc']);
        });
    }
};
