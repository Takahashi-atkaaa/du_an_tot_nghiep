<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('chi_tiet_lo_hang', 'id_san_pham') && !Schema::hasColumn('chi_tiet_lo_hang', 'variant_id')) {
            Schema::table('chi_tiet_lo_hang', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('id_lo_hang');
                $table->foreign('variant_id')->references('id')->on('bien_the_san_pham')->cascadeOnDelete();
                $table->index('variant_id');
            });
        }

        if (Schema::hasColumn('chi_tiet_phieu', 'id_san_pham') && !Schema::hasColumn('chi_tiet_phieu', 'variant_id')) {
            Schema::table('chi_tiet_phieu', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('id_phieu');
                $table->foreign('variant_id')->references('id')->on('bien_the_san_pham')->cascadeOnDelete();
                $table->index('variant_id');
            });
        }

        if (Schema::hasTable('don_vi_san_pham_san_pham')) {
            Schema::dropIfExists('don_vi_san_pham_san_pham');
        }
        if (Schema::hasTable('san_pham_thuoc_tinh')) {
            Schema::dropIfExists('san_pham_thuoc_tinh');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chi_tiet_lo_hang', 'variant_id')) {
            Schema::table('chi_tiet_lo_hang', function (Blueprint $table) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn('variant_id');
            });
        }

        if (Schema::hasColumn('chi_tiet_phieu', 'variant_id')) {
            Schema::table('chi_tiet_phieu', function (Blueprint $table) {
                $table->dropForeign(['variant_id']);
                $table->dropColumn('variant_id');
            });
        }

        if (!Schema::hasTable('san_pham_thuoc_tinh')) {
            Schema::create('san_pham_thuoc_tinh', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_san_pham')->constrained('san_pham')->cascadeOnDelete();
                $table->foreignId('id_thuoc_tinh')->constrained('thuoc_tinh_san_pham')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['id_san_pham', 'id_thuoc_tinh']);
            });
        }

        if (!Schema::hasTable('don_vi_san_pham_san_pham')) {
            Schema::create('don_vi_san_pham_san_pham', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_san_pham')->constrained('san_pham')->onDelete('cascade');
                $table->foreignId('id_don_vi')->constrained('don_vi_san_pham')->onDelete('cascade');
                $table->string('ten_don_vi');
                $table->integer('so_luong_quy_doi');
                $table->decimal('gia_ban_le', 15, 2);
                $table->decimal('gia_ban_si', 15, 2)->nullable();
                $table->string('ma_vach', 100)->nullable();
                $table->string('hinh_anh')->nullable();
                $table->boolean('la_don_vi_mac_dinh')->default(false);
                $table->timestamps();
                $table->unique(['id_san_pham', 'id_don_vi']);
                $table->unique(['id_san_pham', 'ma_vach']);
            });
        }
    }
};
