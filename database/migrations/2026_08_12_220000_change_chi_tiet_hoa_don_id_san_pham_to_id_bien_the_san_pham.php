<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tbl = 'chi_tiet_hoa_don';

        if (!Schema::hasTable($tbl)) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            if (Schema::hasColumn($tbl, 'id_san_pham') && Schema::hasColumn($tbl, 'id_chi_tiet_phieu')) {
                DB::statement(
                    "UPDATE `{$tbl}` SET `id_san_pham` = `id_chi_tiet_phieu` WHERE `id_san_pham` IS NULL AND `id_chi_tiet_phieu` IS NOT NULL"
                );
            }

            $dropForeign = function (string $constraint) use ($tbl): void {
                try {
                    DB::statement("ALTER TABLE `{$tbl}` DROP FOREIGN KEY `{$constraint}`");
                } catch (\Throwable) {
                    // Constraint may not exist or already dropped.
                }
            };

            if (Schema::hasColumn($tbl, 'id_san_pham')) {
                $dropForeign('chi_tiet_hoa_don_id_san_pham_foreign');
            }

            if (Schema::hasColumn($tbl, 'id_chi_tiet_phieu')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn('id_chi_tiet_phieu');
                });
            }

            if (Schema::hasColumn($tbl, 'id_san_pham')) {
                DB::statement("ALTER TABLE `{$tbl}` CHANGE `id_san_pham` `id_bien_the_san_pham` BIGINT UNSIGNED NOT NULL");
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        if (!Schema::hasColumn($tbl, 'id_bien_the_san_pham')) {
            return;
        }

        $hasFk = collect(DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$tbl, 'id_bien_the_san_pham']
        ))->isNotEmpty();

        if (!$hasFk) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->foreign('id_bien_the_san_pham')
                    ->references('id')->on('bien_the_san_pham')
                    ->cascadeOnDelete();
                $table->index('id_bien_the_san_pham');
            });
        }
    }

    public function down(): void
    {
        $tbl = 'chi_tiet_hoa_don';

        if (!Schema::hasTable($tbl) || !Schema::hasColumn($tbl, 'id_bien_the_san_pham')) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $dropForeign = function (string $constraint) use ($tbl): void {
                try {
                    DB::statement("ALTER TABLE `{$tbl}` DROP FOREIGN KEY `{$constraint}`");
                } catch (\Throwable) {
                    // ignore
                }
            };

            $dropForeign('chi_tiet_hoa_don_id_bien_the_san_pham_foreign');

            DB::statement("ALTER TABLE `{$tbl}` CHANGE `id_bien_the_san_pham` `id_san_pham` BIGINT UNSIGNED NOT NULL");

            if (!Schema::hasColumn($tbl, 'id_chi_tiet_phieu')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->integer('id_chi_tiet_phieu')->nullable()->after('id_san_pham');
                });
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        Schema::table($tbl, function (Blueprint $table) {
            $table->foreign('id_san_pham')
                ->references('id')->on('san_pham')
                ->cascadeOnDelete();
        });
    }
};
