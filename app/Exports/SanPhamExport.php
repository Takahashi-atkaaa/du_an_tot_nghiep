<?php

namespace App\Exports;

use App\Models\BienTheSanPham;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Support\Responsable;

class SanPhamExport implements Responsable, ShouldQueue
{
    public function toResponse($request)
    {
        $variants = BienTheSanPham::with(['product.danhMuc', 'units'])
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'))
            ->orderBy('product_id')
            ->orderBy('ten_bien_the')
            ->get();

        $fileName = 'san-pham-' . now()->format('Ymd_His') . '.csv';
        $callback = function () use ($variants) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) return;

            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'id_san_pham',
                'ten_san_pham',
                'danh_muc',
                'id_bien_the',
                'ten_bien_the',
                'ma_hang',
                'ma_vach',
                'gia_von',
                'gia_ban',
                'so_luong_ton',
                'dinh_muc_toi_thieu',
                'trang_thai',
            ]);

            foreach ($variants as $v) {
                fputcsv($handle, [
                    $v->product_id,
                    $v->product->ten_san_pham ?? '',
                    $v->product->danhMuc->ten_danh_muc ?? '',
                    $v->id,
                    $v->ten_bien_the ?? '',
                    $v->ma_hang ?? '',
                    $v->ma_vach ?? '',
                    $v->gia_von ?? 0,
                    $v->gia_ban ?? 0,
                    $v->so_luong_ton ?? 0,
                    $v->dinh_muc_toi_thieu ?? 0,
                    $v->trang_thai ? '1' : '0',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
