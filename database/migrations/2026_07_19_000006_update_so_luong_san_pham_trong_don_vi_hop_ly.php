<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $units = DB::table('don_vi_quy_doi')
            ->whereNotNull('ten_don_vi')
            ->get();

        $mappings = [
            // Đơn vị lớn → số lượng quy đổi
            ['keywords' => ['thùng', 'thung'],  'value' => 24],
            ['keywords' => ['hộp', 'hộp '],     'value' => 12],
            ['keywords' => ['lốc', 'loc '],     'value' => 6],
            ['keywords' => ['bịch', 'bich '],   'value' => 6],
            ['keywords' => ['túi', 'túi '],     'value' => 10],
            ['keywords' => ['vỉ', 'vi '],       'value' => 5],
            ['keywords' => ['gói', 'gói '],     'value' => 10],
            ['keywords' => ['xấp', 'xap '],     'value' => 10],
            // Đơn vị nhỏ / cơ bản → 1
            ['keywords' => ['chai', 'lon', 'gói', 'lẻ', 'cây', 'vien', 'viên', 'lọ'], 'value' => 1],
        ];

        foreach ($units as $unit) {
            $ten = mb_strtolower(trim($unit->ten_don_vi ?? ''));
            if ($ten === '') continue;

            $updated = false;

            foreach ($mappings as $mapping) {
                foreach ($mapping['keywords'] as $keyword) {
                    if (strpos($ten, $keyword) !== false) {
                        DB::table('don_vi_quy_doi')
                            ->where('id', $unit->id)
                            ->update(['so_luong_san_pham_trong_don_vi' => $mapping['value']]);
                        $updated = true;
                        break 2;
                    }
                }
            }
        }
    }

    public function down(): void
    {
        //
    }
};
