<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('so_quy', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->cascadeOnDelete(); // FK → nguoi_dung.id
            $table->string('loai_thu_chi'); // varchar(255), not null, Phiếu thu hoặc Phiếu chi
            $table->string('nhom_thu_chi'); // varchar(255), not null
            $table->decimal('so_tien', 16, 2); // decimal(16,2), not null
            $table->foreignId('id_khach_hang')->nullable()->constrained('khach_hang')->nullOnDelete(); // FK → khach_hang.id
            $table->foreignId('id_nha_cung_cap')->nullable()->constrained('nha_cung_cap')->nullOnDelete(); // FK → nha_cung_cap.id
            $table->foreignId('id_hoa_don')->nullable()->constrained('hoa_don')->nullOnDelete(); // FK → hoa_don.id
            $table->foreignId('id_phieu')->nullable()->constrained('phieu')->nullOnDelete(); // FK → phieu.id
            $table->text('ly_do')->nullable(); // text, nullable
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('so_quy');
    }
};
