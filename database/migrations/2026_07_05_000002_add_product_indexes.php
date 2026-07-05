<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Đảm bảo các index cần thiết trên:
 *   - bien_the_san_pham (variants): ma_vach, ma_hang, product_id, trang_thai
 *   - don_vi_quy_doi (units):     ma_vach, ma_hang, variant_id
 *   - san_pham (products):        id_danh_muc, trang_thai
 *
 * Khác với unique constraints đã drop ở migration 2026_07_01_000005,
 * migration này thêm các non-unique index để tăng tốc truy vấn lọc
 * theo mã vạch, mã hàng, và join với bảng cha.
 */
return new class extends Migration
{
    public function up(): void
    {
        // bien_the_san_pham
        $this->addIndexSafely('bien_the_san_pham', 'product_id', 'idx_bien_the_san_pham_product_id');
        $this->addIndexSafely('bien_the_san_pham', 'ma_vach', 'idx_bien_the_san_pham_ma_vach');
        $this->addIndexSafely('bien_the_san_pham', 'ma_hang', 'idx_bien_the_san_pham_ma_hang');
        $this->addIndexSafely('bien_the_san_pham', 'trang_thai', 'idx_bien_the_san_pham_trang_thai');
        $this->addIndexSafely('bien_the_san_pham', 'deleted_at', 'idx_bien_the_san_pham_deleted_at');

        // don_vi_quy_doi
        $this->addIndexSafely('don_vi_quy_doi', 'variant_id', 'idx_don_vi_quy_doi_variant_id');
        $this->addIndexSafely('don_vi_quy_doi', 'ma_vach', 'idx_don_vi_quy_doi_ma_vach');
        $this->addIndexSafely('don_vi_quy_doi', 'ma_hang', 'idx_don_vi_quy_doi_ma_hang');

        // san_pham
        $this->addIndexSafely('san_pham', 'id_danh_muc', 'idx_san_pham_id_danh_muc');
        $this->addIndexSafely('san_pham', 'trang_thai', 'idx_san_pham_trang_thai');
        $this->addIndexSafely('san_pham', 'deleted_at', 'idx_san_pham_deleted_at');
    }

    public function down(): void
    {
        $indexes = [
            ['bien_the_san_pham', 'idx_bien_the_san_pham_product_id'],
            ['bien_the_san_pham', 'idx_bien_the_san_pham_ma_vach'],
            ['bien_the_san_pham', 'idx_bien_the_san_pham_ma_hang'],
            ['bien_the_san_pham', 'idx_bien_the_san_pham_trang_thai'],
            ['bien_the_san_pham', 'idx_bien_the_san_pham_deleted_at'],
            ['don_vi_quy_doi', 'idx_don_vi_quy_doi_variant_id'],
            ['don_vi_quy_doi', 'idx_don_vi_quy_doi_ma_vach'],
            ['don_vi_quy_doi', 'idx_don_vi_quy_doi_ma_hang'],
            ['san_pham', 'idx_san_pham_id_danh_muc'],
            ['san_pham', 'idx_san_pham_trang_thai'],
            ['san_pham', 'idx_san_pham_deleted_at'],
        ];

        foreach ($indexes as [$table, $indexName]) {
            try {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropIndex($indexName);
                });
            } catch (\Throwable $e) {
            }
        }
    }

    private function addIndexSafely(string $table, string $column, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        if (!Schema::hasColumn($table, $column)) {
            return;
        }

        $exists = collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]))->isNotEmpty();
        if ($exists) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($column, $indexName) {
                $t->index($column, $indexName);
            });
        } catch (\Throwable $e) {
        }
    }
};