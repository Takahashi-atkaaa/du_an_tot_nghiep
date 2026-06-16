<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chia_ca_lam_viec', function (Blueprint $table) {
            if (! Schema::hasColumn('chia_ca_lam_viec', 'vai_tro_trong_ca')) {
                $table->string('vai_tro_trong_ca', 50)
                    ->default('nhan_vien')
                    ->after('ngay');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chia_ca_lam_viec', function (Blueprint $table) {
            if (Schema::hasColumn('chia_ca_lam_viec', 'vai_tro_trong_ca')) {
                $table->dropColumn('vai_tro_trong_ca');
            }
        });
    }
};
