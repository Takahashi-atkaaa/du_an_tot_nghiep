<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('don_vi_san_pham', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->string('ten_don_vi'); // varchar(255), not null
            $table->integer('so_luong_san_pham_trong_don_vi')->default(1); // int, default=1
            $table->boolean('trang_thai')->default(true); // tinyint(1), default=true
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('don_vi_san_pham');
    }
};
