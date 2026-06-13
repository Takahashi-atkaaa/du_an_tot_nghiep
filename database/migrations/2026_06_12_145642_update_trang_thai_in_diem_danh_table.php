<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Đổi tên cột trang_thai thành trang_thai_vao_lam
        Schema::table('diem_danh', function (Blueprint $table) {
            $table->renameColumn('trang_thai', 'trang_thai_vao_lam');
        });

        // 2. Thêm cột trang_thai_tan_ca ngay sau cột trang_thai_vao_lam
        Schema::table('diem_danh', function (Blueprint $table) {
            $table->string('trang_thai_tan_ca')->nullable()->after('trang_thai_vao_lam')->comment('Tan sớm, đúng giờ, tăng ca');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diem_danh', function (Blueprint $table) {
            $table->dropColumn('trang_thai_tan_ca');
            $table->renameColumn('trang_thai_vao_lam', 'trang_thai');
        });
    }
};