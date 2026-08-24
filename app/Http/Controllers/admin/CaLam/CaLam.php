<?php

namespace App\Http\Controllers\admin\CaLam;

use App\Http\Controllers\Controller;
use App\Models\CaLamViec;
use App\Models\ChiaCaLamViec;
use App\Models\ChiTietHoaDon;
use App\Models\HoaDon;
use App\Services\RevenueStatisticsService;

class CaLam extends Controller
{
    public function index(RevenueStatisticsService $revenueStatisticsService)
    {
        $ngay_hien_tai = now()->format('Y-m-d');
        $gio_hien_tai = now()->format('H:i:s');
        $revenueStatuses = $revenueStatisticsService->salesRevenueStatuses();

        $ca_hien_tai = CaLamViec::where(function ($query) use ($gio_hien_tai) {
            $query->where(function ($q) use ($gio_hien_tai) {
                $q->whereColumn('gio_bat_dau', '<', 'gio_ket_thuc')
                    ->where('gio_bat_dau', '<=', $gio_hien_tai)
                    ->where('gio_ket_thuc', '>', $gio_hien_tai);
            })->orWhere(function ($q) use ($gio_hien_tai) {
                $q->whereColumn('gio_bat_dau', '>', 'gio_ket_thuc')
                    ->where(function ($sub) use ($gio_hien_tai) {
                        $sub->where('gio_bat_dau', '<=', $gio_hien_tai)
                            ->orWhere('gio_ket_thuc', '>', $gio_hien_tai);
                    });
            });
        })->first();

        if (!$ca_hien_tai) {
            return redirect()->back()->with('warning', 'Hiện tại không có ca làm việc.');
        }

        $danh_sach_hoa_don_cua_ca = HoaDon::with('khachHang')
            ->whereDate('created_at', $ngay_hien_tai)
            ->where('id_ca_lam_viec', $ca_hien_tai->id)
            ->orderByDesc('created_at')
            ->get();

        // tính tổng tiền cần trả lại cho khách của ca
        $tong_tien_tra_lai_khach = 0;
        
        foreach ($danh_sach_hoa_don_cua_ca as $hoaDon) {
            $hoaDon->tien_tra_khach = $hoaDon->doiTras
                ?->flatMap(function ($doiTra) {
                    return $doiTra->chiTietDoiTras ?? [];
                })
                ->sum('thanh_tien') ?? 0;

            $tong_tien_tra_lai_khach += $hoaDon->tien_tra_khach;
        }

        // chỉ cho hiển thị 7 hóa đơn
        $danh_sach_hoa_don_cua_ca = $danh_sach_hoa_don_cua_ca->take(7);

        $tong_doanh_thu_tien_mat_cua_ca = HoaDon::where('id_ca_lam_viec', $ca_hien_tai->id)
            ->whereDate('created_at', $ngay_hien_tai)
            ->where('phuong_thuc_thanh_toan', '=', 'Tiền mặt')
            ->whereIn('trang_thai', ['Hoàn thành', 'Đã đổi/trả hàng', 'Đã trả toàn bộ'])
            ->sum('khach_can_tra');


        $tong_doanh_thu_chuyen_khoan_cua_ca = HoaDon::where('id_ca_lam_viec', $ca_hien_tai->id)
            ->whereDate('created_at', $ngay_hien_tai)
            ->where('phuong_thuc_thanh_toan', 'payos')
            ->where('trang_thai', 'Hoàn thành')
            ->sum('khach_can_tra');

        $tong_doanh_thu_cua_ca = $tong_doanh_thu_tien_mat_cua_ca + $tong_doanh_thu_chuyen_khoan_cua_ca - $tong_tien_tra_lai_khach;

        $tong_doanh_thu_tien_mat_cua_ca -= $tong_tien_tra_lai_khach;

        $cac_hoa_don_doi_tra_trong_ca = HoaDon::whereDate('created_at', $ngay_hien_tai)
            ->where('id_ca_lam_viec', $ca_hien_tai->id)
            ->whereIn('trang_thai', ['Đã đổi/trả hàng', 'Đã trả toàn bộ'])
            ->count();

        $tong_nhan_vien_cua_ca = ChiaCaLamViec::where('id_ca_lam_viec', $ca_hien_tai->id)
            ->where('ngay', $ngay_hien_tai)
            ->count('id');

        $nhan_vien = ChiaCaLamViec::with(['nguoiDung'])
            ->where('ngay', $ngay_hien_tai)
            ->where('id_ca_lam_viec', $ca_hien_tai->id)
            ->get();


        $tong_so_hoa_don_cua_ca = HoaDon::whereDate('created_at', $ngay_hien_tai)
            ->where('id_ca_lam_viec', $ca_hien_tai->id)
            ->count();

        return view(
            'admin_xem_truoc.ca-lam-viec.ca-lam-viec-hien-tai.thong-tin',
            compact(
                'ngay_hien_tai',
                'gio_hien_tai',
                'ca_hien_tai',
                'tong_doanh_thu_tien_mat_cua_ca',
                'tong_doanh_thu_chuyen_khoan_cua_ca',
                'tong_doanh_thu_cua_ca',
                'cac_hoa_don_doi_tra_trong_ca',
                'tong_nhan_vien_cua_ca',
                'danh_sach_hoa_don_cua_ca',
                'nhan_vien',
                'tong_so_hoa_don_cua_ca'
            )
        );
    }

    public function show($id_hoadon)
    {
        $hoaDon = HoaDon::with(['nguoiDung', 'caLamViec'])->findOrFail($id_hoadon);
        $chiTietHoaDon = ChiTietHoaDon::with(['bienTheSanPham'])->where('id_hoa_don', $id_hoadon)->get();

        return view('admin_xem_truoc.ca-lam-viec.ca-lam-viec-hien-tai.chi-tiet-hoa-don', compact('chiTietHoaDon', 'hoaDon'));
    }
}
