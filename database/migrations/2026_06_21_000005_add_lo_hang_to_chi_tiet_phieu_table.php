<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chi_tiet_phieu', function (Blueprint $table) {
            $table->foreignId('id_lo_hang')
                ->nullable()
                ->after('id_san_pham')
                ->constrained('lo_hang')
                ->nullOnDelete();

            $table->foreignId('id_chi_tiet_lo_hang')
                ->nullable()
                ->after('id_lo_hang')
                ->constrained('chi_tiet_lo_hang')
                ->nullOnDelete();

            $table->index('id_lo_hang');
            $table->index('id_chi_tiet_lo_hang');
        });
    }

    public function down(): void
    {
        Schema::table('chi_tiet_phieu', function (Blueprint $table) {
            $table->dropForeign(['id_lo_hang']);
            $table->dropForeign(['id_chi_tiet_lo_hang']);
            $table->dropIndex(['id_lo_hang']);
            $table->dropIndex(['id_chi_tiet_lo_hang']);
            $table->dropColumn(['id_lo_hang', 'id_chi_tiet_lo_hang']);
        });
    }
};
