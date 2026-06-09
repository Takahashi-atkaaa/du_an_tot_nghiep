<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thuoc_tinh_san_pham', function (Blueprint $table) {
            $table->id();
            $table->foreignId('danh_muc_id')->constrained('danh_muc_san_pham')->cascadeOnDelete();
            $table->string('ten_thuoc_tinh');
            $table->string('kieu_du_lieu')->default('text');
            $table->json('gia_tri_mac_dinh')->nullable();
            $table->boolean('bat_buoc')->default(false);
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thuoc_tinh_san_pham');
    }
};
