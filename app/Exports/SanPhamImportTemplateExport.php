<?php

namespace App\Exports;

use App\Models\DanhMucSanPham;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Support\Responsable;

class SanPhamImportTemplateExport implements Responsable
{
    public function toResponse($request)
    {
        $danhMucs = DanhMucSanPham::orderBy('ten_danh_muc')->get();

        $fileName = 'mau-import-san-pham.csv';
        $callback = function () use ($danhMucs) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) return;

            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Sheet 1: template rows
            fputcsv($handle, [
                '--- SHEET: Mau import san pham ---',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ]);
            fputcsv($handle, [
                'ten_san_pham',
                'danh_muc',
                'ten_bien_the',
                'ma_hang',
                'ma_vach',
                'gia_von',
                'gia_ban',
                'so_luong_ton',
                'dinh_muc_toi_thieu',
                'trang_thai',
            ]);
            fputcsv($handle, [
                'Áo thun nam',
                'Áo',
                'Size M',
                '',
                '',
                '100000',
                '200000',
                '50',
                '10',
                '1',
            ]);
            fputcsv($handle, [
                'Áo thun nam',
                'Áo',
                'Size L',
                '',
                '',
                '110000',
                '220000',
                '30',
                '10',
                '1',
            ]);
            fputcsv($handle, [
                'Quần jeans nữ',
                'Quần',
                'Size 28',
                '',
                '',
                '150000',
                '300000',
                '20',
                '5',
                '1',
            ]);

            // Blank separator
            fputcsv($handle, ['', '', '', '', '', '', '', '', '', '']);

            // Sheet 2: danh sách danh mục tham khảo
            fputcsv($handle, [
                '--- SHEET: Danh sach danh muc (tham khao) ---',
                '',
                '',
                '',
            ]);
            fputcsv($handle, ['ten_danh_muc', 'id_danh_muc', '', '']);
            foreach ($danhMucs as $dm) {
                fputcsv($handle, [$dm->ten_danh_muc, $dm->id, '', '']);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
