<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_phieu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phieu_id')->constrained('phieu')->cascadeOnDelete();
            $table->foreignId('san_pham_id')->constrained('san_pham')->cascadeOnDelete();
            $table->decimal('so_luong', 14, 2);
            $table->string('ma_lo')->nullable();
            $table->date('han_su_dung')->nullable();
            $table->decimal('so_luong_con_lai', 14, 2)->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_phieu');
    }
};
