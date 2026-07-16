<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE bien_the_san_pham bts
            INNER JOIN (
                SELECT variant_id, SUM(so_luong_ton) AS tong_ton
                FROM chi_tiet_lo_hang
                WHERE variant_id IS NOT NULL
                GROUP BY variant_id
            ) ct ON ct.variant_id = bts.id
            SET bts.so_luong_ton = ct.tong_ton
        ");
    }

    public function down(): void
    {
        // Không rollback dữ liệu sống
    }
};
