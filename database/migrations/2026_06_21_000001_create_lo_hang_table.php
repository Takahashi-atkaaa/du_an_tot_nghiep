<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lo_hang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_phieu')->nullable()->constrained('phieu')->nullOnDelete();
            $table->foreignId('id_nha_cung_cap')->nullable()->constrained('nha_cung_cap')->nullOnDelete();
            $table->string('ma_lo')->nullable()->unique();
            $table->date('ngay_nhap');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lo_hang');
    }
};
