<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('don_vi_quy_doi', function (Blueprint $table) {
            $table->foreignId('don_vi_chuan_id')
                ->nullable()
                ->after('id')
                ->constrained('danh_muc_don_vi')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('don_vi_quy_doi', function (Blueprint $table) {
            $table->dropForeign(['don_vi_chuan_id']);
            $table->dropColumn('don_vi_chuan_id');
        });
    }
};
