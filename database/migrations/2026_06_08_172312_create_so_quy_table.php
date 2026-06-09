<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('so_quy', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->enum('loai_thu_chi', ['thu', 'chi']);
            $table->string('nhom_thu_chi')->nullable(); // hoa_don, phieu_nhap, phieu_xuat, khac
            $table->foreignId('id_khach_hang')->nullable()->constrained('khach_hang')->nullOnDelete();
            $table->foreignId('id_nha_cung_cap')->nullable()->constrained('nha_cung_cap')->nullOnDelete();
            $table->foreignId('id_hoa_don')->nullable()->constrained('hoa_don')->nullOnDelete();
            $table->foreignId('id_phieu')->nullable()->constrained('phieu')->nullOnDelete();
            $table->decimal('so_tien', 16, 2);
            $table->enum('hinh_thuc', ['tien_mat', 'chuyen_khoan', 'the'])->default('tien_mat');
            $table->string('ly_do')->nullable();
            $table->dateTime('thoi_gian')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('so_quy');
    }
};
