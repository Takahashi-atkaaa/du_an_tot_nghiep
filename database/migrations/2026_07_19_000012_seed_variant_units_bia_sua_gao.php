<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Lấy đơn vị chuẩn từ danh_muc_don_vi
        $thung24 = DB::table('danh_muc_don_vi')
            ->where('ten_don_vi', 'Thùng')->where('so_luong_san_pham_trong_don_vi', 24)->first();
        $thung12 = DB::table('danh_muc_don_vi')
            ->where('ten_don_vi', 'Thùng')->where('so_luong_san_pham_trong_don_vi', 12)->first();
        $bao6 = DB::table('danh_muc_don_vi')
            ->where('ten_don_vi', 'Bao')->where('so_luong_san_pham_trong_don_vi', 6)->first();

        if (!$thung24 || !$thung12 || !$bao6) {
            echo "Warning: Some standard units not found. Skipping seed.\n";
            return;
        }

        // Lấy variant của bia
        $biaVariants = DB::table('bien_the_san_pham')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->whereRaw('LOWER(san_pham.ten_san_pham) LIKE ?', ['%bia%'])
            ->select('bien_the_san_pham.*')->get();

        foreach ($biaVariants as $v) {
            DB::table('don_vi_quy_doi')->updateOrInsert(
                ['variant_id' => $v->id, 'ten_don_vi' => 'Thùng 24'],
                [
                    'product_id' => $v->product_id,
                    'don_vi_chuan_id' => $thung24->id,
                    'so_luong_san_pham_trong_don_vi' => 24,
                    'ma_vach' => 'BV' . str_pad($v->id, 6, '0', STR_PAD_LEFT) . '024',
                    'gia_von_quy_doi' => ($v->gia_von * 24),
                    'gia_ban_quy_doi' => ($v->gia_ban * 24),
                    'la_don_vi_mac_dinh' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ]
            );
        }

        // Lấy variant của sữa
        $suaVariants = DB::table('bien_the_san_pham')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->whereRaw('LOWER(san_pham.ten_san_pham) LIKE ?', ['%sữa%'])
            ->select('bien_the_san_pham.*')->get();

        foreach ($suaVariants as $v) {
            DB::table('don_vi_quy_doi')->updateOrInsert(
                ['variant_id' => $v->id, 'ten_don_vi' => 'Thùng 12'],
                [
                    'product_id' => $v->product_id,
                    'don_vi_chuan_id' => $thung12->id,
                    'so_luong_san_pham_trong_don_vi' => 12,
                    'ma_vach' => 'SV' . str_pad($v->id, 6, '0', STR_PAD_LEFT) . '012',
                    'gia_von_quy_doi' => ($v->gia_von * 12),
                    'gia_ban_quy_doi' => ($v->gia_ban * 12),
                    'la_don_vi_mac_dinh' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ]
            );
        }

        // Lấy variant của gạo
        $gaoVariants = DB::table('bien_the_san_pham')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->whereRaw('LOWER(san_pham.ten_san_pham) LIKE ?', ['%gạo%'])
            ->select('bien_the_san_pham.*')->get();

        foreach ($gaoVariants as $v) {
            DB::table('don_vi_quy_doi')->updateOrInsert(
                ['variant_id' => $v->id, 'ten_don_vi' => 'Bao 6'],
                [
                    'product_id' => $v->product_id,
                    'don_vi_chuan_id' => $bao6->id,
                    'so_luong_san_pham_trong_don_vi' => 6,
                    'ma_vach' => 'GV' . str_pad($v->id, 6, '0', STR_PAD_LEFT) . '006',
                    'gia_von_quy_doi' => ($v->gia_von * 6),
                    'gia_ban_quy_doi' => ($v->gia_ban * 6),
                    'la_don_vi_mac_dinh' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('don_vi_quy_doi')
            ->whereIn('ten_don_vi', ['Thùng 24', 'Thùng 12', 'Bao 6'])
            ->delete();
    }
};
