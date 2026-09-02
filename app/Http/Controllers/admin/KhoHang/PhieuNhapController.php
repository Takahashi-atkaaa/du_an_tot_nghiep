<?php

namespace App\Http\Controllers\Admin\KhoHang;

use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use App\Models\PhieuNhap;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhieuNhapController extends Controller
{
    /**
     * Hiển thị trang tạo phiếu nhập (dạng trang riêng thay vì modal).
     * Truyền danh sách nhà cung cấp để chọn trong form.
     */
    public function create(Request $request): View
    {
        $nhaCungCaps = NhaCungCap::query()
            ->orderBy('ten_nha_cung_cap')
            ->get();

        return view('admin_xem_truoc.kho-hang.phieu-nhap.create', compact('nhaCungCaps'));
    }

    /**
     * Hiển thị chi tiết phiếu nhập
     */
    public function show($id): View
    {
        $phieuNhap = PhieuNhap::with([
            'phieu.nhaCungCap',
            'phieu.nguoiDung',
            'phieu.hoaDon',
            'hoaDon',
            'phieuXuatGoc',
            'chiTietPhieu' => function ($q) {
                $q->orderBy('id')
                    ->with([
                        'variant.product',
                        'loHang.nhaCungCap',
                        'chiTietLoHang',
                    ]);
            },
        ])->findOrFail($id);

        $chiTietPhieu = $phieuNhap->chiTietPhieu;
        $tongSoDong = $chiTietPhieu->count();
        $tongSoLuong = $chiTietPhieu->sum(fn ($ct) => (float) ($ct->so_luong ?? 0));
        $tongThanhTien = $chiTietPhieu->sum(fn ($ct) => (float) ($ct->so_luong ?? 0) * (float) ($ct->gia_nhap ?? 0));
        $soLoaiSanPham = $chiTietPhieu->pluck('variant_id')->filter()->unique()->count();
        $hsdSomNhat = $chiTietPhieu->pluck('han_su_dung')->filter()->sort()->first();

        return view('admin_xem_truoc.kho-hang.phieu-nhap-chi-tiet', compact(
            'phieuNhap',
            'chiTietPhieu',
            'tongSoDong',
            'tongSoLuong',
            'tongThanhTien',
            'soLoaiSanPham',
            'hsdSomNhat'
        ));
    }
}
