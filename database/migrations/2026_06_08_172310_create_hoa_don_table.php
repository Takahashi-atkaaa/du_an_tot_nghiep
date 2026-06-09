<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoa_don', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('khach_hang_id')->nullable()->constrained('khach_hang')->nullOnDelete();
            $table->foreignId('khuyen_mai_id')->nullable()->constrained('khuyen_mai')->nullOnDelete();
            $table->foreignId('ca_lam_viec_id')->nullable()->constrained('ca_lam_viec')->nullOnDelete();
            $table->string('trang_thai')->default('cho_xu_ly'); // cho_xu_ly, da_thanh_toan, da_huy
            $table->string('phuong_thuc_thanh_toan')->default('tien_mat'); // tien_mat, chuyen_khoan, the
            $table->decimal('tong_tien_hang', 16, 2)->default(0);
            $table->decimal('tong_giam_gia', 16, 2)->default(0);
            $table->decimal('tong_thanh_toan', 16, 2)->default(0);
            $table->decimal('tien_khach_tra', 16, 2)->default(0);
            $table->decimal('tien_thua', 16, 2)->default(0);
            $table->integer('diem_su_dung')->default(0);
            $table->integer('diem_thu_duoc')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoa_don');
    }
};
