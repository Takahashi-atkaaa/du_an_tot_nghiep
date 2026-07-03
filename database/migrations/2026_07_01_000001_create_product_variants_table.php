<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bien_the_san_pham', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('san_pham')->cascadeOnDelete();
            $table->string('ten_bien_the')->nullable()->comment('VD: "Lon 330ml" hoặc "Màu Đỏ - Size M"');
            $table->string('ma_hang')->nullable();
            $table->string('ma_vach')->nullable();
            $table->decimal('gia_von', 14, 2)->default(0);
            $table->decimal('gia_ban', 14, 2)->default(0);
            $table->integer('so_luong_ton')->default(0)->comment('Tồn kho gốc - chỉ cộng trừ tại đây');
            $table->integer('dinh_muc_toi_thieu')->default(0)->comment('Định mức tối thiểu để cảnh báo');
            $table->string('hinh_anh')->nullable();
            $table->text('thuoc_tinh_ids')->nullable()->comment('JSON array: [3, 7, 12]');
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('product_id');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bien_the_san_pham');
    }
};
