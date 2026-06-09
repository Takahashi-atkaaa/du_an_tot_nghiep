<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_lam_viec', function (Blueprint $table) {
            $table->id();
            $table->string('ten_ca');
            $table->time('gio_bat_dau');
            $table->time('gio_ket_thuc');
            $table->integer('so_phut_di_lam_tre_toi_da')->default(15);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_lam_viec');
    }
};
