<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {

            $table->enum('gioi_tinh', ['Nam', 'Nữ', 'Khác'])
                ->nullable()
                ->after('sdt');

            $table->string('cccd')
                ->nullable()
                ->unique()
                ->after('gioi_tinh');

            $table->string('anh_dai_dien')
                ->nullable()
                ->after('cccd');

            $table->string('anh_cccd_mat_truoc')
                ->nullable()
                ->after('anh_dai_dien');

            $table->string('anh_cccd_mat_sau')
                ->nullable()
                ->after('anh_cccd_mat_truoc');
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {

            $table->dropColumn([
                'gioi_tinh',
                'cccd',
                'anh_dai_dien',
                'anh_cccd_mat_truoc',
                'anh_cccd_mat_sau',
            ]);
        });
    }
};