<?php

namespace App\Exports;

use App\Models\PhieuXuat;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PhieuXuatChiTietExport implements FromView, ShouldAutoSize
{
    protected int $phieuXuatId;

    public function __construct(int $phieuXuatId)
    {
        $this->phieuXuatId = $phieuXuatId;
    }

    public function view(): View
    {
        $phieuXuat = PhieuXuat::with([
            'phieu',
            'chiTietPhieu' => fn($ct) => $ct->with('variant.product', 'chiTietLoHang.loHang'),
        ])->find($this->phieuXuatId);

        if (!$phieuXuat) {
            $phieuXuat = collect();
        }

        $chiTiet = $phieuXuat->chiTietPhieu ?? collect();

        $data = $chiTiet->map(function ($ct, $index) {
            return [
                'stt' => $index + 1,
                'ma_phieu' => 'PX' . str_pad($ct->id_phieu, 5, '0', STR_PAD_LEFT),
                'ma_hang' => $ct->variant->ma_hang ?? '',
                'ma_vach' => $ct->variant->ma_vach ?? '',
                'ten_san_pham' => $ct->variant->product->ten_san_pham ?? '',
                'ten_bien_the' => $ct->variant->ten_bien_the ?? '',
                'so_luong' => number_format($ct->so_luong, 0, ',', '.'),
                'don_gia' => number_format($ct->gia_nhap, 0, ',', '.'),
                'thanh_tien' => number_format($ct->so_luong * $ct->gia_nhap, 0, ',', '.'),
                'han_su_dung' => $ct->han_su_dung ? \Carbon\Carbon::parse($ct->han_su_dung)->format('d/m/Y') : '',
                'ma_lo' => $ct->chiTietLoHang->loHang->ma_lo ?? '',
                'loai_xuat' => $phieuXuat->loai_xuat ?? '',
                'ly_do' => $phieuXuat->ly_do ?? '',
                'ghi_chu' => $ct->ghi_chu ?? '',
            ];
        });

        $tongTien = $chiTiet->sum(fn($ct) => $ct->so_luong * $ct->gia_nhap);

        return view('exports.phieu-xuat-chi-tiet', [
            'phieuXuat' => $phieuXuat,
            'data' => $data,
            'tongTien' => number_format($tongTien, 0, ',', '.'),
            'exportDate' => now()->format('d/m/Y H:i:s'),
        ]);
    }
}
