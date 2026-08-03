<?php

namespace App\Exports;

use App\Models\PhieuNhap;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PhieuNhapChiTietExport implements FromView, ShouldAutoSize
{
    protected int $phieuNhapId;

    public function __construct(int $phieuNhapId)
    {
        $this->phieuNhapId = $phieuNhapId;
    }

    public function view(): View
    {
        $phieuNhap = PhieuNhap::with([
            'phieu' => fn($p) => $p->with('nhaCungCap', 'nguoiDung'),
            'chiTietPhieu' => fn($ct) => $ct->with('variant.product', 'chiTietLoHang.loHang'),
        ])->find($this->phieuNhapId);

        if (!$phieuNhap) {
            $phieuNhap = collect();
        }

        $chiTiet = $phieuNhap->chiTietPhieu ?? collect();

        $data = $chiTiet->map(function ($ct, $index) {
            $thanhTien = $ct->so_luong * $ct->gia_nhap;
            return [
                'stt' => $index + 1,
                'ma_phieu' => 'PN' . str_pad($ct->id_phieu, 5, '0', STR_PAD_LEFT),
                'ma_hang' => $ct->variant->ma_hang ?? '',
                'ma_vach' => $ct->variant->ma_vach ?? '',
                'ten_san_pham' => $ct->variant->product->ten_san_pham ?? '',
                'ten_bien_the' => $ct->variant->ten_bien_the ?? '',
                'so_luong' => number_format((float) $ct->so_luong, 0, ',', '.'),
                'don_gia' => self::formatVnd($ct->gia_nhap),
                'thanh_tien' => self::formatVnd($thanhTien),
                'han_su_dung' => $ct->han_su_dung ? \Carbon\Carbon::parse($ct->han_su_dung)->format('d/m/Y') : '',
                'ma_lo' => $ct->chiTietLoHang->loHang->ma_lo ?? '',
                'loai_nhap' => $ct->phieu->phieuNhap->loai_nhap ?? '',
                'ghi_chu' => $ct->ghi_chu ?? '',
            ];
        });

        $tongTien = $chiTiet->sum(fn($ct) => $ct->so_luong * $ct->gia_nhap);

        return view('exports.phieu-nhap-chi-tiet', [
            'phieuNhap' => $phieuNhap,
            'data' => $data,
            'tongTien' => self::formatVnd($tongTien),
            'exportDate' => now()->format('d/m/Y H:i:s'),
        ]);
    }

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
