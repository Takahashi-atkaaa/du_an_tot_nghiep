<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chi_tiet_doi_tra', function (Blueprint $table) {
            if (!Schema::hasColumn('chi_tiet_doi_tra', 'id_bien_the_thay_the')) {
                $table->foreignId('id_bien_the_thay_the')
                    ->nullable()
                    ->after('id_bien_the')
                    ->constrained('bien_the_san_pham')
                    ->nullOnDelete();

                $table->index('id_bien_the_thay_the');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chi_tiet_doi_tra', function (Blueprint $table) {
            if (Schema::hasColumn('chi_tiet_doi_tra', 'id_bien_the_thay_the')) {
                $table->dropConstrainedForeignId('id_bien_the_thay_the');
            }
        });
    }
};
