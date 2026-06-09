<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thiet_lap_luong', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->cascadeOnDelete(); // FK → nguoi_dung.id, not null
            $table->decimal('luong_co_ban', 14, 2); // decimal(14,2), not null
            $table->decimal('luong_theo_gio', 14, 2)->nullable(); // decimal(14,2), nullable
            $table->decimal('phu_cap', 12, 2)->default(0); // decimal(12,2), default=0
            $table->decimal('phat_di_muon', 12, 2)->default(0); // decimal(12,2), default=0
            $table->decimal('luong_tang_ca', 12, 2)->default(0); // decimal(12,2), default=0
            $table->decimal('thuong', 12, 2)->default(0); // decimal(12,2), default=0
            $table->decimal('phat_bo_ca', 12, 2)->default(0); // decimal(12,2), default=0
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thiet_lap_luong');
    }
};
