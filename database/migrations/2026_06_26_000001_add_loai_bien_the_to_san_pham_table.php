<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->string('loai_bien_the', 30)->nullable()->after('san_pham_cha_id');
            $table->index('loai_bien_the');
        });
    }

    public function down(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->dropIndex(['loai_bien_the']);
            $table->dropColumn('loai_bien_the');
        });
    }
};
