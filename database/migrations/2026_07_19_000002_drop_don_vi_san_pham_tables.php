<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('don_vi_san_pham_san_pham');
        Schema::dropIfExists('don_vi_san_pham');
    }

    public function down(): void
    {
        Schema::create('don_vi_san_pham', function (Blueprint $table) {
            $table->id();
            $table->string('ten_don_vi');
            $table->integer('so_luong_san_pham_trong_don_vi')->default(1);
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('don_vi_san_pham_san_pham', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_san_pham')->constrained('san_pham')->onDelete('cascade');
            $table->foreignId('id_don_vi')->constrained('don_vi_san_pham')->onDelete('cascade');
            $table->string('ten_don_vi');
            $table->integer('so_luong_quy_doi')->default(1);
            $table->decimal('gia_ban_le', 12, 2)->default(0);
            $table->decimal('gia_ban_si', 12, 2)->default(0);
            $table->string('ma_vach')->nullable();
            $table->string('hinh_anh')->nullable();
            $table->boolean('la_don_vi_mac_dinh')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
