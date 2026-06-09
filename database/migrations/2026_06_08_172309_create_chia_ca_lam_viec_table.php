<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chia_ca_lam_viec', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_ca_lam_viec')->constrained('ca_lam_viec')->cascadeOnDelete(); // FK → ca_lam_viec.id, not null
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->cascadeOnDelete(); // FK → nguoi_dung.id, not null
            $table->date('ngay'); // date, not null
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chia_ca_lam_viec');
    }
};
