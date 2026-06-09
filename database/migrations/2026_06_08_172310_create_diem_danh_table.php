<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diem_danh', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_chia_ca_lam_viec')->constrained('chia_ca_lam_viec')->cascadeOnDelete(); // FK → chia_ca_lam_viec.id, not null
            $table->timestamp('gio_vao'); // timestamp, not null
            $table->integer('so_gio_di_lam_muon')->default(0); // int, default=0
            $table->timestamp('gio_tan_ca')->nullable(); // timestamp, nullable
            $table->float('so_gio_lam_them')->default(0); // float, default=0
            $table->string('trang_thai')->nullable(); // varchar(255), nullable, Đúng giờ/đi muộn/nghỉ làm
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_danh');
    }
};
