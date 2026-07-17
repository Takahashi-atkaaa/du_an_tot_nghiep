<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop key cũ nếu còn
        try {
            DB::statement("ALTER TABLE `chi_tiet_lo_hang` DROP INDEX `chi_tiet_lo_unique`");
        } catch (\Exception $e) {
            // Key cũ có thể đã bị drop
        }

        // Tạo unique key mới với variant_id (nếu chưa có)
        $exists = DB::select("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_NAME = 'chi_tiet_lo_hang'
            AND INDEX_NAME = 'chi_tiet_lo_variant_unique'
            AND TABLE_SCHEMA = DATABASE()
            LIMIT 1
        ");

        if (empty($exists)) {
            DB::statement("
                ALTER TABLE `chi_tiet_lo_hang`
                ADD UNIQUE INDEX `chi_tiet_lo_variant_unique`
                (`id_lo_hang`, `id_san_pham`, `variant_id`, `han_su_dung`)
            ");
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `chi_tiet_lo_hang` DROP INDEX `chi_tiet_lo_variant_unique`");
        } catch (\Exception $e) {
            //
        }

        DB::statement("
            ALTER TABLE `chi_tiet_lo_hang`
            ADD UNIQUE INDEX `chi_tiet_lo_unique`
            (`id_lo_hang`, `id_san_pham`, `han_su_dung`)
        ");
    }
};