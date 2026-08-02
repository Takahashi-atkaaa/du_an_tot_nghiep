<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Drop key cũ `chi_tiet_lo_unique` (id_lo_hang + id_san_pham + han_su_dung)
     * tren bang chi_tiet_lo_hang.
     *
     * Ly do: key moi `chi_tiet_lo_variant_unique` da duoc them o migration
     * 2026_07_16 nhung key cu KHONG duoc drop (do try/catch nuot loi neu
     * khong ton tai) nen con song song. Hai key cung rang buoc cung 3 cot
     * (id_lo_hang, id_san_pham, han_su_dung) nen khi nhap 2 bien the khac
     * nhau cua cung san pham + cung HSD vao 1 lo -> van vi pham key cu.
     *
     * Sau migration nay, chi con dung `chi_tiet_lo_variant_unique` cho phep
     * nhieu bien the khac nhau cung (lo, san pham, HSD).
     */
    public function up(): void
    {
        // Dung SQL truc tiep de tranh phu thuoc vao doctrine/dbal (khong co
        // trong Laravel mac dinh).
        $exists = DB::select("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_NAME = 'chi_tiet_lo_hang'
            AND INDEX_NAME = 'chi_tiet_lo_unique'
            AND TABLE_SCHEMA = DATABASE()
            LIMIT 1
        ");

        if (!empty($exists)) {
            DB::statement("ALTER TABLE `chi_tiet_lo_hang` DROP INDEX `chi_tiet_lo_unique`");
        }
    }

    public function down(): void
    {
        // Khoi phuc lai key cu neu can rollback
        $exists = DB::select("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_NAME = 'chi_tiet_lo_hang'
            AND INDEX_NAME = 'chi_tiet_lo_unique'
            AND TABLE_SCHEMA = DATABASE()
            LIMIT 1
        ");

        if (empty($exists)) {
            DB::statement("
                ALTER TABLE `chi_tiet_lo_hang`
                ADD UNIQUE INDEX `chi_tiet_lo_unique`
                (`id_lo_hang`, `id_san_pham`, `han_su_dung`)
            ");
        }
    }
};
