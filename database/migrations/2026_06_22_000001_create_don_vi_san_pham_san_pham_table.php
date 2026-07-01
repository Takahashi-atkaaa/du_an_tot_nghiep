<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('don_vi_san_pham_san_pham', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_san_pham')->constrained('san_pham')->onDelete('cascade');
            $table->foreignId('id_don_vi')->constrained('don_vi_san_pham')->onDelete('cascade');
            $table->string('ten_don_vi');
            $table->integer('so_luong_quy_doi');
            $table->decimal('gia_ban_le', 15, 2);
            $table->decimal('gia_ban_si', 15, 2)->nullable();
            $table->string('ma_vach', 100)->nullable();
            $table->string('hinh_anh')->nullable();
            $table->boolean('la_don_vi_mac_dinh')->default(false);
            $table->timestamps();
            $table->unique(['id_san_pham', 'id_don_vi'], 'dvsp_sp_dv_unique');
            $table->unique(['id_san_pham', 'ma_vach'], 'dvsp_sp_mv_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('don_vi_san_pham_san_pham');
    }
};
