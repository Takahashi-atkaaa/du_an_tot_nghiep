<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khach_hang', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten');
            $table->string('so_dien_thoai')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('dia_chi')->nullable();
            $table->integer('diem_tich_luy')->default(0);
            $table->decimal('cong_no', 14, 2)->default(0);
            $table->text('phi_chu')->nullable();
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khach_hang');
    }
};
