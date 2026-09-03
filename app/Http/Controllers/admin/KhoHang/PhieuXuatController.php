<?php

namespace App\Http\Controllers\Admin\KhoHang;

use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use App\Models\Phieu;
use App\Models\PhieuXuat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhieuXuatController extends Controller
{
    /**
     * Hiển thị trang tạo phiếu xuất (dạng trang riêng, không dùng modal).
     * Truyền danh sách nhà cung cấp để chọn trong form khi loại xuất = trả hàng NCC.
     */
    public function create(Request $request): View
    {
        $nhaCungCaps = NhaCungCap::query()
            ->orderBy('ten_nha_cung_cap')
            ->get();

        return view('admin_xem_truoc.kho-hang.phieu-xuat.create', compact('nhaCungCaps'));
    }

    /**
     * Hiển thị chi tiết phiếu xuất trên trang riêng.
     */
    public function show(int $id): View
    {
        $phieuXuat = PhieuXuat::with([
            'phieu.nhaCungCap',
            'phieu.nguoiDung',
            'phieu.hoaDon',
            'phieuNhapLienQuan.phieuNhap',
            'chiTietPhieu' => function ($q) {
                $q->orderBy('id')
                    ->with([
                        'variant.product',
                        'chiTietLoHang.loHang',
                    ]);
            },
        ])->findOrFail($id);

        $chiTietPhieu = $phieuXuat->chiTietPhieu;
        $tongSoDong = $chiTietPhieu->count();
        $tongSoLuong = $chiTietPhieu->sum(fn ($ct) => (float) ($ct->so_luong ?? 0));
        $soLoaiSanPham = $chiTietPhieu->pluck('variant_id')->filter()->unique()->count();
        
        // Nếu id_phieu_nhap_lien_quan NULL, lấy từ lô hàng của chi tiết đầu tiên
        if (!$phieuXuat->id_phieu_nhap_lien_quan && $chiTietPhieu->isNotEmpty()) {
            $idPhieuFromLo = $chiTietPhieu->first()?->chiTietLoHang?->loHang?->id_phieu;
            if ($idPhieuFromLo) {
                $phieuXuat->phieuNhapLienQuan = Phieu::with('phieuNhap')->find($idPhieuFromLo);
            }
        }

        $phieuNhapLienQuanRecord = $phieuXuat->phieuNhapLienQuan?->phieuNhap;

        return view('admin_xem_truoc.warehouse.phieu-xuat-chi-tiet', compact(
            'phieuXuat',
            'chiTietPhieu',
            'tongSoDong',
            'tongSoLuong',
            'soLoaiSanPham',
            'phieuNhapLienQuanRecord'
        ));
    }
}
