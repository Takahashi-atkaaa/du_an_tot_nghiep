<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('san_pham', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_danh_muc')->constrained('danh_muc_san_pham')->cascadeOnDelete(); // FK → danh_muc_san_pham.id
            $table->string('ten_san_pham'); // varchar(255), not null
            $table->string('ma_vach')->unique(); // varchar(255), unique, not null, Mã vạch sản phẩm
            $table->string('hinh_anh')->nullable(); // varchar(255), nullable
            $table->string('thuong_hieu')->nullable(); // varchar(255), nullable
            $table->decimal('gia_ban', 14, 2); // decimal(14,2), not null
            $table->integer('so_luong_ton_kho')->default(0); // int, default=0
            $table->text('mo_ta')->nullable(); // text, nullable
            $table->foreignId('id_thuoc_tinh')->nullable()->constrained('thuoc_tinh_san_pham')->nullOnDelete(); // FK → thuoc_tinh_san_pham.id
            $table->foreignId('id_don_vi')->nullable()->constrained('don_vi_san_pham')->nullOnDelete(); // FK → don_vi_san_pham.id
            $table->integer('dinh_muc_toi_thieu')->default(0); // int, default=0
            $table->boolean('trang_thai')->default(true); // tinyint(1), default=true
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('san_pham');
    }
};
