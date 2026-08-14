<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    if (!Schema::hasColumn(
        'khuyen_mai_san_pham',
        'id_bien_the_san_pham'
    )) {
        Schema::table('khuyen_mai_san_pham', function (Blueprint $table) {
            $table->unsignedBigInteger('id_bien_the_san_pham')
                ->nullable()
                ->after('id_san_pham');
        });
    }
}

    public function down(): void
    {
        Schema::table('khuyen_mai_san_pham', function (Blueprint $table) {
            $table->dropForeign(
                ['id_bien_the_san_pham']
            );

            $table->dropColumn('id_bien_the_san_pham');
        });
    }
};