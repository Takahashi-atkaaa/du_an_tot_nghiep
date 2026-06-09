<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thiet_lap_luong', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->date('thang_ap_dung');
            $table->decimal('luong_co_ban', 14, 2);
            $table->decimal('luong_theo_gio', 14, 2)->nullable();
            $table->decimal('phu_cap', 12, 2)->default(0);
            $table->decimal('thuong', 12, 2)->default(0);
            $table->decimal('khau_tru', 12, 2)->default(0);
            $table->decimal('phat_di_muon', 12, 2)->default(0);
            $table->decimal('luong_tang_ca', 12, 2)->default(0);
            $table->decimal('phat_bo_ca', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thiet_lap_luong');
    }
};
