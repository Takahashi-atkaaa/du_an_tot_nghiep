<?php

namespace App\Exports;

use App\Models\PhieuXuat;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Export danh sach phieu xuat (Maatwebsite/Excel).
 *
 * Class nay KHONG tu query all() - no chi su dung cac filter (loai_xuat,
 * tu_ngay, den_ngay) truyen qua constructor tu controller.
 */
class PhieuXuatDanhSachExport implements FromView, ShouldAutoSize
{
    /**
     * @param array{loai_xuat: ?string, tu_ngay: ?\Carbon\Carbon, den_ngay: ?\Carbon\Carbon} $filters
     */
    public function __construct(protected array $filters = [])
    {
    }

    /**
     * Tra ve Eloquent Builder (chua get) de controller/test co the tai su dung.
     */
    public function query()
    {
        $query = PhieuXuat::with([
            'phieu' => fn($p) => $p->with('nguoiDung'),
        ])
            ->whereHas('phieu', fn($p) => $p->where('loai_phieu_enum', 'like', 'xuat%'))
            ->orderByDesc('id');

        if (!empty($this->filters['loai_xuat'])) {
            $query->where('loai_xuat', $this->filters['loai_xuat']);
        }
        if (!empty($this->filters['tu_ngay'])) {
            $query->where('phieu_xuat.created_at', '>=', $this->filters['tu_ngay']);
        }
        if (!empty($this->filters['den_ngay'])) {
            $query->where('phieu_xuat.created_at', '<=', $this->filters['den_ngay']);
        }

        return $query;
    }

    public function view(): View
    {
        $phieuXuats = $this->query()->get();

        $data = $phieuXuats->map(function ($item) {
            $chiTiet = $item->chiTietPhieu ?? collect();
            $tongTien = $chiTiet->sum(fn($ct) => $ct->so_luong * $ct->gia_nhap);

            return [
                'id' => $item->id,
                'ma_phieu' => 'PX' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
                'ngay_tao' => $item->created_at->format('d/m/Y H:i'),
                'loai_xuat' => $item->loai_xuat === 'tra_hang_nha_cung_cap' ? 'Trả hàng NCC' : 'Tiêu hủy',
                'nguoi_tao' => $item->phieu->nguoiDung->ho_ten ?? 'N/A',
                'tong_san_pham' => $chiTiet->count(),
                'tong_so_luong' => $chiTiet->sum('so_luong'),
                'tong_tien' => self::formatVnd($tongTien),
                'ly_do' => $item->ly_do ?? '',
                'ghi_chu' => $item->ghi_chu ?? '',
            ];
        });

        return view('exports.phieu-xuat-danh-sach', [
            'data' => $data,
            'filters' => $this->filters,
            'exportDate' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Format so tien theo chuan VNĐ (luon dung dau phay, khong phu thuoc locale).
     */
    public static function formatVnd(float|int|string $value): string
    {
        $value = (float) $value;
        $negative = $value < 0;
        $abs = abs($value);
        $intPart = number_format($abs, 0, '', '');
        $withCommas = strrev(implode(',', str_split(strrev($intPart), 3)));
        return ($negative ? '-' : '') . $withCommas . ' đ';
    }
}