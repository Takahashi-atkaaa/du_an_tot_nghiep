<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doi_tra', function (Blueprint $table) {
            $table->boolean('hang_loi')
                ->default(false)
                ->after('Loai');
        });
    }

    public function down(): void
    {
        Schema::table('doi_tra', function (Blueprint $table) {
            $table->dropColumn('hang_loi');
        });
    }
};
