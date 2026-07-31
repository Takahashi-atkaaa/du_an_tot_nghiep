<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_don_vi', function (Blueprint $table) {
            $table->id();
            $table->string('ten_don_vi');                                   // VD: "Thùng", "Lốc", "Bao"
            $table->integer('so_luong_san_pham_trong_don_vi')->unsigned(); // VD: 24, 6, 4
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['ten_don_vi', 'so_luong_san_pham_trong_don_vi'], 'danh_muc_don_vi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_don_vi');
    }
};
