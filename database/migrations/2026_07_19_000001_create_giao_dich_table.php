<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giao_dich', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_hoa_don')->constrained('hoa_don')->onDelete('cascade');
            
            $table->enum('phuong_thuc', ['tien_mat', 'vnpay']);
            $table->decimal('so_tien', 12, 2);
            $table->enum('trang_thai', ['cho_xac_nhan', 'thanh_cong', 'that_bai', 'hoan_tien'])
                  ->default('cho_xac_nhan');
            
            $table->string('ma_tham_chieu', 100)->nullable()->unique();
            $table->string('ma_giao_dich_doi_tac', 100)->nullable()->unique();
            $table->string('ma_phan_hoi', 20)->nullable();
            $table->string('trang_thai_doi_tac', 20)->nullable();
            $table->json('du_lieu_phan_hoi')->nullable();
            $table->string('ma_ngan_hang', 50)->nullable();
            $table->dateTime('ngay_gio_thanh_toan')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['id_hoa_don', 'trang_thai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giao_dich');
    }
};
