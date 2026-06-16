<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ca_lam_viec', function (Blueprint $table) {
            if (! Schema::hasColumn('ca_lam_viec', 'so_nhan_vien_toi_thieu')) {
                $table->integer('so_nhan_vien_toi_thieu')
                    ->default(0)
                    ->after('gio_ket_thuc');
            }

            if (! Schema::hasColumn('ca_lam_viec', 'so_nhan_vien_toi_da')) {
                $table->integer('so_nhan_vien_toi_da')
                    ->default(0)
                    ->after('so_nhan_vien_toi_thieu');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ca_lam_viec', function (Blueprint $table) {
            if (Schema::hasColumn('ca_lam_viec', 'so_nhan_vien_toi_da')) {
                $table->dropColumn('so_nhan_vien_toi_da');
            }

            if (Schema::hasColumn('ca_lam_viec', 'so_nhan_vien_toi_thieu')) {
                $table->dropColumn('so_nhan_vien_toi_thieu');
            }
        });
    }
};
