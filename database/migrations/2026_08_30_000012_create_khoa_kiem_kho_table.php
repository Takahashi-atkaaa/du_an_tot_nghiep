<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bang khoa cac bien the dang duoc dem trong phieu kiem kho
        // Muc dich: chan ban hang/xuat kho khi bien the dang trong qua trinh dem
        Schema::create('khoa_kiem_kho', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_phieu_kiem_kho');
            $table->unsignedBigInteger('variant_id');
            $table->timestamp('ngay_khoa');
            $table->timestamp('ngay_mo')->nullable();
            $table->string('ly_do', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_phieu_kiem_kho')
                ->references('id')->on('phieu_kiem_kho')
                ->cascadeOnDelete();
            $table->foreign('variant_id')
                ->references('id')->on('bien_the_san_pham')
                ->cascadeOnDelete();

            // 1 phieu kiem chi tao 1 record khoa cho 1 variant
            $table->unique(['id_phieu_kiem_kho', 'variant_id'], 'khoa_kiem_phieu_variant_unique');
            // De query nhanh cac variant dang bi khoa
            $table->index(['variant_id', 'ngay_mo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khoa_kiem_kho');
    }
};