<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoa_don', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->cascadeOnDelete(); // FK → nguoi_dung.id, not null
            $table->foreignId('id_khach_hang')->nullable()->constrained('khach_hang')->nullOnDelete(); // FK → khach_hang.id
            $table->foreignId('id_ca_lam_viec')->nullable()->constrained('ca_lam_viec')->nullOnDelete(); // FK → ca_lam_viec.id
            $table->foreignId('id_khuyen_mai')->nullable()->constrained('khuyen_mai')->nullOnDelete(); // FK → khuyen_mai.id
            $table->decimal('tong_tien_hang', 16, 2); // decimal(16,2), not null
            $table->decimal('tien_giam_gia', 16, 2)->default(0); // decimal(16,2), default=0
            $table->decimal('khach_can_tra', 16, 2); // decimal(16,2), not null
            $table->decimal('tien_khach_dua', 16, 2); // decimal(16,2), not null
            $table->decimal('tien_thua', 16, 2); // decimal(16,2), not null
            $table->string('phuong_thuc_thanh_toan'); // varchar(255), not null
            $table->string('trang_thai'); // varchar(255), not null, Hoàn thành/hủy/nợ
            $table->integer('diem_su_dung')->default(0); // int, default=0
            $table->integer('diem_thu_duoc')->default(0); // int, default=0
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoa_don');
    }
};
