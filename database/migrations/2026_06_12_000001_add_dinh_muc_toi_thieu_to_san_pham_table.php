<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            if (! Schema::hasColumn('san_pham', 'dinh_muc_toi_thieu')) {
                $table->integer('dinh_muc_toi_thieu')->default(0)->after('so_luong_ton_kho');
            }
        });
    }

    public function down(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->dropColumn('dinh_muc_toi_thieu');
        });
    }
};
