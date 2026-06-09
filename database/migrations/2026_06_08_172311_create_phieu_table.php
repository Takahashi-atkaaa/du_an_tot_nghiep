<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phieu', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->string('loai_phieu'); // varchar(255), not null, Nhập hàng/trả hàng NCC/tiêu hủy
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->cascadeOnDelete(); // FK → nguoi_dung.id, not null
            $table->foreignId('id_nha_cung_cap')->nullable()->constrained('nha_cung_cap')->nullOnDelete(); // FK → nha_cung_cap.id
            $table->foreignId('id_hoa_don')->nullable()->constrained('hoa_don')->nullOnDelete(); // FK → hoa_don.id
            $table->text('ghi_chu')->nullable(); // text, nullable
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phieu');
    }
};
