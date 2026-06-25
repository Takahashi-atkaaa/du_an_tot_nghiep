<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bang_luong', function (Blueprint $table) {
            $table->id();
            $table->string('ten_bang_luong');
            $table->string('ky_lam_viec');
            $table->decimal('tong_luong_tat_ca_nhan_vien', 16, 2)->default(0);
            $table->integer('so_nhan_vien');
            $table->string('trang_thai');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bang_luong');
    }
};
