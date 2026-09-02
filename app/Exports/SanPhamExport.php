<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Export danh sách sản phẩm (Maatwebsite/Excel).
 * 
 * Xuất tất cả sản phẩm với các biến thể, mỗi biến thể là một dòng riêng.
 * Format: STT, Tên SP, Danh mục, Biến thể, Mã hàng, Mã vạch, Đơn vị,
 * Giá vốn, Giá bán, Tồn kho, Định mức TT, Trạng thái, Thương hiệu.
 */
class SanPhamExport implements FromView, ShouldAutoSize
{
    public function view(): View
    {
        // Query all products với variants và relations
        $products = Product::with(['danhMuc', 'variants'])
            ->whereNull('deleted_at')
            ->whereHas('variants', fn($q) => $q->whereNull('deleted_at'))
            ->orderBy('ten_san_pham')
            ->get();

        // Flatten: mỗi variant → 1 row
        $data = [];
        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $data[] = [
                    'ten_san_pham' => $product->ten_san_pham,
                    'danh_muc' => $product->danhMuc->ten_danh_muc ?? 'Chưa phân loại',
                    'ten_bien_the' => $variant->ten_bien_the ?? '—',
                    'ma_hang' => $variant->ma_hang,
                    'ma_vach' => $variant->ma_vach ?? '—',
                    'ten_don_vi' => $variant->ten_don_vi ?? 'Cái',
                    'gia_von' => self::formatVnd($variant->gia_von),
                    'gia_ban' => self::formatVnd($variant->gia_ban),
                    'so_luong_ton' => $variant->so_luong_ton,
                    'dinh_muc_toi_thieu' => $variant->dinh_muc_toi_thieu ?? 0,
                    'trang_thai' => $variant->trang_thai ? 'Hoạt động' : 'Tạm ngưng',
                    'thuong_hieu' => $product->thuong_hieu ?? '—',
                ];
            }
        }

        return view('exports.san-pham-danh-sach', [
            'data' => $data,
            'exportDate' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Format số tiền theo chuẩn VNĐ:
     *  - Dấu phẩy phân cách hàng nghìn (1,234,567)
     *  - Thêm hậu tố ' đ' để người đọc biết là đồng Việt Nam
     *  - Trả về text thuần (không phải number) để Excel không tự cắt/truncate.
     */
    public static function formatVnd(float|int|string $value): string
    {
        $value = (float) $value;
        $negative = $value < 0;
        $abs = abs($value);
        // Làm tròn 0 chữ số thập phân, phân tách hàng nghìn bằng dấu phẩy
        $intPart = number_format($abs, 0, '', '');
        // Chèn dấu phẩy mỗi 3 ký tự từ phải sang trái
        $withCommas = strrev(implode(',', str_split(strrev($intPart), 3)));
        return ($negative ? '-' : '') . $withCommas . ' đ';
    }
}
