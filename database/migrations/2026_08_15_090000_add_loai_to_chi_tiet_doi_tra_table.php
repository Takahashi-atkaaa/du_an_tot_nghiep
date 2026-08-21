<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chi_tiet_doi_tra', function (Blueprint $table) {
            $table->enum('loai', ['tra_hang', 'doi_hang'])
                ->nullable()
                ->after('id_bien_the_thay_the');
        });
    }

    public function down(): void
    {
        Schema::table('chi_tiet_doi_tra', function (Blueprint $table) {
            $table->dropColumn('loai');
        });
    }
};
