<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phieu_luong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('id_bang_luong')->constrained('bang_luong')->cascadeOnDelete();
            $table->decimal('luong_tam_tinh', 14, 2);
            $table->string('trang_thai');
            $table->decimal('tong_gio_lam_thuc_te', 8, 2);
            $table->decimal('tong_luong', 14, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phieu_luong');
    }
};
