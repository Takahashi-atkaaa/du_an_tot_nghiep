<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign keys trước khi drop columns
        Schema::disableForeignKeyConstraints();

        Schema::table('san_pham', function (Blueprint $table) {
            if (Schema::hasColumn('san_pham', 'san_pham_cha_id')) {
                try {
                    $table->dropForeign(['san_pham_cha_id']);
                } catch (\Throwable) {}
                $table->dropColumn('san_pham_cha_id');
            }

            if (Schema::hasColumn('san_pham', 'la_san_pham_cha')) {
                $table->dropColumn('la_san_pham_cha');
            }

            if (Schema::hasColumn('san_pham', 'id_don_vi')) {
                try {
                    $table->dropForeign(['id_don_vi']);
                } catch (\Throwable) {}
                $table->dropColumn('id_don_vi');
            }

            $remaining = [];
            foreach (['gia_von', 'gia_ban', 'so_luong_ton_kho', 'dinh_muc_toi_thieu', 'hinh_anh'] as $col) {
                if (Schema::hasColumn('san_pham', $col)) {
                    $remaining[] = $col;
                }
            }
            if (!empty($remaining)) {
                $table->dropColumn($remaining);
            }
        });

        Schema::enableForeignKeyConstraints();

        if (Schema::hasTable('don_vi_san_pham_san_pham')) {
            Schema::dropIfExists('don_vi_san_pham_san_pham');
        }
        if (Schema::hasTable('san_pham_thuoc_tinh')) {
            Schema::dropIfExists('san_pham_thuoc_tinh');
        }
    }

    public function down(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            if (!Schema::hasColumn('san_pham', 'gia_von')) {
                $table->decimal('gia_von', 14, 2)->default(0)->after('thuong_hieu');
            }
            if (!Schema::hasColumn('san_pham', 'gia_ban')) {
                $table->decimal('gia_ban', 14, 2)->default(0)->after('gia_von');
            }
            if (!Schema::hasColumn('san_pham', 'so_luong_ton_kho')) {
                $table->integer('so_luong_ton_kho')->default(0)->after('gia_ban');
            }
            if (!Schema::hasColumn('san_pham', 'id_don_vi')) {
                $table->foreignId('id_don_vi')->nullable()->constrained('don_vi_san_pham')->nullOnDelete()->after('mo_ta');
            }
            if (!Schema::hasColumn('san_pham', 'dinh_muc_toi_thieu')) {
                $table->integer('dinh_muc_toi_thieu')->default(0)->after('so_luong_ton_kho');
            }
            if (!Schema::hasColumn('san_pham', 'hinh_anh')) {
                $table->string('hinh_anh')->nullable()->after('ma_vach');
            }
            if (!Schema::hasColumn('san_pham', 'san_pham_cha_id')) {
                $table->foreignId('san_pham_cha_id')->nullable()->after('trang_thai')->constrained('san_pham')->nullOnDelete();
            }
            if (!Schema::hasColumn('san_pham', 'la_san_pham_cha')) {
                $table->boolean('la_san_pham_cha')->default(false)->after('san_pham_cha_id');
            }
        });

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
