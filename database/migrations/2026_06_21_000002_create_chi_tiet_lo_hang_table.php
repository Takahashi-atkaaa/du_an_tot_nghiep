<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_lo_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_lo_hang')->constrained('lo_hang')->cascadeOnDelete();
            $table->foreignId('id_san_pham')->constrained('san_pham')->cascadeOnDelete();
            $table->integer('so_luong_nhap');
            $table->integer('so_luong_ton')->default(0);
            $table->decimal('gia_nhap', 14, 2);
            $table->date('han_su_dung');
            $table->timestamps();
            $table->unique(['id_lo_hang', 'id_san_pham', 'han_su_dung'], 'chi_tiet_lo_unique');
            $table->index(['id_san_pham', 'han_su_dung']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_lo_hang');
    }
};
