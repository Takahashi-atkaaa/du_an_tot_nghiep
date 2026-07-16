<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phieu_nhap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_phieu')->constrained('phieu')->cascadeOnDelete();
            $table->enum('loai_nhap', ['mua_hang', 'tra_lai_tu_khach'])
                ->default('mua_hang');
            $table->foreignId('id_hoa_don')->nullable()->constrained('hoa_don')->nullOnDelete();
            $table->foreignId('id_phieu_xuat_goc')->nullable()->constrained('phieu')->nullOnDelete();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });

        Schema::create('phieu_xuat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_phieu')->constrained('phieu')->cascadeOnDelete();
            $table->enum('loai_xuat', ['tra_hang_nha_cung_cap', 'tieu_huy'])
                ->default('tra_hang_nha_cung_cap');
            $table->foreignId('id_phieu_nhap_lien_quan')->nullable()->constrained('phieu')->nullOnDelete();
            $table->string('ly_do')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phieu_xuat');
        Schema::dropIfExists('phieu_nhap');
    }
};
