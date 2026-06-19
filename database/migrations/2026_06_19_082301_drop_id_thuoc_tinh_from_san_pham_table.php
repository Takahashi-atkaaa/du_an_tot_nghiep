<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->dropForeign(['id_thuoc_tinh']);
            $table->dropColumn('id_thuoc_tinh');
        });
    }

    public function down(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->foreignId('id_thuoc_tinh')
                ->nullable()
                ->after('mo_ta')
                ->constrained('thuoc_tinh_san_pham')
                ->nullOnDelete();
        });
    }
};
