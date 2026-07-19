<?php

namespace App\Exports;

use App\Models\PhieuXuat;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PhieuXuatDanhSachExport implements FromView, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
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
            $query->whereDate('created_at', '>=', $this->filters['tu_ngay']);
        }
        if (!empty($this->filters['den_ngay'])) {
            $query->whereDate('created_at', '<=', $this->filters['den_ngay']);
        }

        $phieuXuats = $query->get();

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
                'tong_tien' => number_format($tongTien, 0, ',', '.'),
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
}
