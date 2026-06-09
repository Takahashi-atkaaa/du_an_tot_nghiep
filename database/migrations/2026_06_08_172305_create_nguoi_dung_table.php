<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->string('ho_ten'); // varchar(255), not null, Họ và tên đầy đủ
            $table->string('email')->unique(); // varchar(255), unique, not null
            $table->string('sdt'); // varchar(255), not null, Số điện thoại
            $table->string('mat_khau'); // varchar(255), not null, Mật khẩu đã băm (bcrypt)
            $table->string('vai_tro'); // varchar(255), not null, Admin/nhân viên/trưởng ca
            $table->boolean('trang_thai')->default(true); // tinyint(1), default=true, Trạng thái hoạt động
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung');
    }
};
