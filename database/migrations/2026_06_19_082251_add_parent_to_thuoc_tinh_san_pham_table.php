<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thuoc_tinh_san_pham', function (Blueprint $table) {
            $table->foreignId('thuoc_tinh_cha_id')
                ->nullable()
                ->after('trang_thai')
                ->constrained('thuoc_tinh_san_pham')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('thuoc_tinh_san_pham', function (Blueprint $table) {
            $table->dropForeign(['thuoc_tinh_cha_id']);
            $table->dropColumn('thuoc_tinh_cha_id');
        });
    }
};
