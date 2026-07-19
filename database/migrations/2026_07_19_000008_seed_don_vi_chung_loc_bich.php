<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('don_vi_quy_doi')->insert([
            [
                'ten_don_vi' => 'Lốc',
                'so_luong_san_pham_trong_don_vi' => 6,
                'variant_id' => null,
                'product_id' => null,
                'la_don_vi_mac_dinh' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_don_vi' => 'Bịch',
                'so_luong_san_pham_trong_don_vi' => 4,
                'variant_id' => null,
                'product_id' => null,
                'la_don_vi_mac_dinh' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('don_vi_quy_doi')
            ->whereNull('variant_id')
            ->whereNull('product_id')
            ->whereIn('ten_don_vi', ['Lốc', 'Bịch'])
            ->delete();
    }
};
