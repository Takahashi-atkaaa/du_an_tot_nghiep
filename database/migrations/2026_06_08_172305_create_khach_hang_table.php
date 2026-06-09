<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khach_hang', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->string('ten_khach_hang'); // varchar(255), not null, Họ và tên khách hàng
            $table->string('so_dien_thoai'); // varchar(255), not null, Số điện thoại liên hệ
            $table->string('email')->nullable(); // varchar(255), nullable=có thể null, Địa chỉ email
            $table->string('dia_chi')->nullable(); // varchar(255), nullable=có thể null, Địa chỉ thường trú
            $table->decimal('cong_no', 14, 2)->default(0); // decimal(14,2), default=0, Số dư công nợ
            $table->text('phi_chu')->nullable(); // text, nullable=có thể null, Ghi chú thêm
            $table->boolean('trang_thai')->default(true); // tinyint(1), default=true, Trạng thái hoạt động
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khach_hang');
    }
};
