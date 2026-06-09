<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lich_su_tich_diem', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_khach_hang')->constrained('khach_hang')->cascadeOnDelete(); // FK → khach_hang.id, not null
            $table->foreignId('id_hoa_don')->nullable()->constrained('hoa_don')->nullOnDelete(); // FK → hoa_don.id
            $table->string('loai_bien_dong'); // varchar(255), not null, Tăng hoặc giảm điểm
            $table->integer('so_diem'); // int, not null, Số điểm thay đổi
            $table->text('ly_do')->nullable(); // text, nullable, Lý do thay đổi điểm
            $table->timestamps(); // created_at & updated_at (ngay_tao = created_at)
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_su_tich_diem');
    }
};
