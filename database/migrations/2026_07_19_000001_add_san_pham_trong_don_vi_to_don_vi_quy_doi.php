<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('don_vi_quy_doi', function (Blueprint $table) {
            $table->integer('so_luong_san_pham_trong_don_vi')->default(1)->after('ty_le_quy_doi');
        });
    }

    public function down(): void
    {
        Schema::table('don_vi_quy_doi', function (Blueprint $table) {
            $table->dropColumn('so_luong_san_pham_trong_don_vi');
        });
    }
};
