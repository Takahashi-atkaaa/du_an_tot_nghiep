<?php

namespace App\Exports;

use App\Models\PhieuNhap;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Export danh sach phieu nhap (Maatwebsite/Excel).
 *
 * Class nay KHONG tu query all() - no chi su dung cac filter (loai_nhap,
 * tu_ngay, den_ngay) truyen qua constructor tu controller.
 *
 * Phuong thuc query() duoc tach rieng de co the goi tu test/observer
 * hoac tu controller neu can kiem tra truoc khi render.
 */
class PhieuNhapDanhSachExport implements FromView, ShouldAutoSize
{
    /**
     * @param array{loai_nhap: ?string, tu_ngay: ?\Carbon\Carbon, den_ngay: ?\Carbon\Carbon} $filters
     */
    public function __construct(protected array $filters = [])
    {
    }

    /**
     * Tra ve Eloquent Builder (chua get) de controller co the lay data neu can,
     * va de viec test de dang - khong can render view().
     */
    public function query()
    {
        $query = PhieuNhap::with([
            'phieu' => fn($p) => $p->with('nhaCungCap', 'nguoiDung'),
        ])
            ->whereHas('phieu', fn($p) => $p->where('loai_phieu_enum', 'like', 'nhap%'))
            ->orderByDesc('id');

        if (!empty($this->filters['loai_nhap'])) {
            $query->where('loai_nhap', $this->filters['loai_nhap']);
        }
        if (!empty($this->filters['tu_ngay'])) {
            $query->where('phieu_nhap.created_at', '>=', $this->filters['tu_ngay']);
        }
        if (!empty($this->filters['den_ngay'])) {
            $query->where('phieu_nhap.created_at', '<=', $this->filters['den_ngay']);
        }

        return $query;
    }

    public function view(): View
    {
        $phieuNhaps = $this->query()->get();

        $data = $phieuNhaps->map(function ($item) {
            $chiTiet = $item->chiTietPhieu ?? collect();
            $tongTien = $chiTiet->sum(fn($ct) => $ct->so_luong * $ct->gia_nhap);

            return [
                'id' => $item->id,
                'ma_phieu' => 'PN' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                'ngay_tao' => $item->created_at->format('d/m/Y H:i'),
                'loai_nhap' => $item->loai_nhap === 'mua_hang' ? 'Mua hàng' : 'Trả lại từ khách',
                'nha_cung_cap' => $item->phieu->nhaCungCap->ten_nha_cung_cap ?? 'Không có',
                'nguoi_tao' => $item->phieu->nguoiDung->ho_ten ?? 'N/A',
                'tong_san_pham' => $chiTiet->count(),
                'tong_so_luong' => $chiTiet->sum('so_luong'),
                'tong_tien' => self::formatVnd($tongTien),
                'ghi_chu' => $item->ghi_chu ?? '',
            ];
        });

        return view('exports.phieu-nhap-danh-sach', [
            'data' => $data,
            'filters' => $this->filters,
            'exportDate' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Format so tien theo chuan VNĐ:
     *  - Dau phay phan cach hang nghin (1,234,567)
     *  - Them hau to ' d' de nguoi doc biet la dong Viet Nam
     *  - Tra ve text thuan (khong phai number) de Excel khong tu cat/truncate.
     *
     * Luu y: number_format() trong PHP bi phu thuoc locale he thong va
     * tren mot so moi truong (Windows, locale vi_VN) hai tham so
     * $dec_point va $thousands_sep bi swap, dan den output khong nhu y.
     * Ham nay tu dinh dang chuoi de luon ra '1,234,567 d' bat ke locale.
     */
    public static function formatVnd(float|int|string $value): string
    {
        $value = (float) $value;
        $negative = $value < 0;
        $abs = abs($value);
        // Lam tron 0 chu so thap phan, phan tach hang nghin bang dau phay
        $intPart = number_format($abs, 0, '', '');
        // Chen dau phay moi 3 ky tu tu phai sang trai
        $withCommas = strrev(implode(',', str_split(strrev($intPart), 3)));
        return ($negative ? '-' : '') . $withCommas . ' đ';
    }
}