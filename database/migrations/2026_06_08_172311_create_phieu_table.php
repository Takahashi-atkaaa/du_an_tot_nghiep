<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phieu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('nha_cung_cap_id')->nullable()->constrained('nha_cung_cap')->nullOnDelete();
            $table->foreignId('id_hoa_don')->nullable()->constrained('hoa_don')->nullOnDelete();
            $table->enum('loai_phieu', ['nhap_kho', 'xuat_kho', 'chuyen_kho', 'kiem_kho']);
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phieu');
    }
};
