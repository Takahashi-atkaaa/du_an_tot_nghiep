<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_lam_viec', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->string('ten_ca'); // varchar(255), not null
            $table->time('gio_bat_dau'); // time, not null
            $table->time('gio_ket_thuc'); // time, not null
            $table->integer('so_phut_di_lam_tre_toi_da')->default(15); // int, default=15
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_lam_viec');
    }
};
