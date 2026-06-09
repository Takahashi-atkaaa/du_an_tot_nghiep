<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khuyen_mai', function (Blueprint $table) {
            $table->id();
            $table->string('ma_khuyen_mai')->unique();
            $table->string('ten_khuyen_mai');
            $table->enum('loai_khuyen_mai', ['phan_tram', 'co_dinh', 'qua_tang']);
            $table->decimal('gia_tri', 12, 2);
            $table->decimal('giam_toi_da', 12, 2)->nullable();
            $table->decimal('don_hang_toi_thieu', 12, 2)->nullable();
            $table->integer('so_luong_sp_toi_thieu')->nullable();
            $table->timestamp('ngay_bat_dau')->nullable();
            $table->timestamp('ngay_ket_thuc')->nullable();
            $table->enum('trang_thai', ['dang_hoat_dong', 'khong_hoat_dong', 'da_ket_thuc'])->default('dang_hoat_dong');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khuyen_mai');
    }
};
