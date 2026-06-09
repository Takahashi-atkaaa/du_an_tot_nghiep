<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khuyen_mai', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->string('ten_chuong_trinh'); // varchar(255), not null, Tên chương trình khuyến mãi
            $table->string('loai_giam_gia'); // varchar(255), not null, Phần trăm hoặc số tiền trực tiếp
            $table->decimal('gia_tri_giam', 12, 2); // decimal(12,2), not null, Giá trị giảm
            $table->decimal('giam_toi_da', 12, 2)->nullable(); // decimal(12,2), nullable, Số tiền giảm tối đa
            $table->integer('so_luong_sp_toi_thieu')->nullable(); // int, nullable, Số lượng sản phẩm tối thiểu
            $table->decimal('don_hang_toi_thieu', 12, 2)->nullable(); // decimal(12,2), nullable, Giá trị đơn hàng tối thiểu
            $table->timestamp('ngay_bat_dau')->nullable(); // timestamp, nullable
            $table->timestamp('ngay_ket_thuc')->nullable(); // timestamp, nullable
            $table->boolean('trang_thai')->default(true); // tinyint(1), default=true
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khuyen_mai');
    }
};
