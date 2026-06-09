<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('san_pham', function (Blueprint $table) {
            $table->id();
            $table->string('ten_san_pham');
            $table->text('mo_ta')->nullable();
            $table->foreignId('id_danh_muc')->constrained('danh_muc_san_pham')->cascadeOnDelete();
            $table->foreignId('id_thuoc_tinh')->nullable()->constrained('thuoc_tinh_san_pham')->nullOnDelete();
            $table->foreignId('id_don_vi')->nullable()->constrained('don_vi_san_pham')->nullOnDelete();
            $table->decimal('gia_ban', 14, 2);
            $table->decimal('gia_nhap', 14, 2)->nullable();
            $table->decimal('so_luong_ton', 12, 2)->default(0);
            $table->decimal('dinh_muc_toi_thieu', 12, 2)->default(0);
            $table->string('hinh_anh')->nullable();
            $table->string('thuong_hieu')->nullable();
            $table->enum('trang_thai', ['dang_ban', 'ngung_ban', 'het_hang'])->default('dang_ban');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('san_pham');
    }
};
