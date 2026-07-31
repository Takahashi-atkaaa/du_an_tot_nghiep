<?php

namespace App\Exports;

use App\Models\Phieu;
use App\Models\PhieuNhap;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PhieuNhapDanhSachExport implements FromView, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
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
            $query->whereDate('created_at', '>=', $this->filters['tu_ngay']);
        }
        if (!empty($this->filters['den_ngay'])) {
            $query->whereDate('created_at', '<=', $this->filters['den_ngay']);
        }

        $phieuNhaps = $query->get();

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
                'tong_tien' => number_format($tongTien, 0, ',', '.'),
                'ghi_chu' => $item->ghi_chu ?? '',
            ];
        });

        return view('exports.phieu-nhap-danh-sach', [
            'data' => $data,
            'filters' => $this->filters,
            'exportDate' => now()->format('d/m/Y H:i:s'),
        ]);
    }
}
