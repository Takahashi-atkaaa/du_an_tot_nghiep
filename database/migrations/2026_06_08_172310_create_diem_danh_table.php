<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diem_danh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('chia_ca_lam_viec_id')->constrained('chia_ca_lam_viec')->cascadeOnDelete();
            $table->date('ngay_diem_danh');
            $table->time('gio_vao')->nullable();
            $table->time('gio_ra')->nullable();
            $table->decimal('so_gio_di_lam_muon', 5, 2)->default(0);
            $table->decimal('so_gio_lam_them', 5, 2)->default(0);
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_danh');
    }
};
