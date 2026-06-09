<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chia_ca_lam_viec', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->foreignId('ca_lam_viec_id')->constrained('ca_lam_viec')->cascadeOnDelete();
            $table->date('ngay_lam');
            $table->time('gio_vao_ca_thuc_te')->nullable();
            $table->time('gio_ra_ca_thuc_te')->nullable();
            $table->boolean('di_muon')->default(false);
            $table->boolean('ve_som')->default(false);
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chia_ca_lam_viec');
    }
};
