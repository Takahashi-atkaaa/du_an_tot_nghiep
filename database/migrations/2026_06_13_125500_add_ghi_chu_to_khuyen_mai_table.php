<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('khuyen_mai', function (Blueprint $table) {
            if (!Schema::hasColumn('khuyen_mai', 'ghi_chu')) {
                $table->text('ghi_chu')->nullable()->after('trang_thai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('khuyen_mai', function (Blueprint $table) {
            if (Schema::hasColumn('khuyen_mai', 'ghi_chu')) {
                $table->dropColumn('ghi_chu');
            }
        });
    }
};
