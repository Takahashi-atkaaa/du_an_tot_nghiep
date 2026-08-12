<?php

namespace App\Http\Controllers\admin\CaLam;

use App\Http\Controllers\Controller;
use App\Models\CaLamViec;
use App\Models\ChiaCaLamViec;
use App\Models\ChiTietHoaDon;
use App\Models\HoaDon;
use Illuminate\Http\Request;

class CaLam extends Controller
{
    //

public function index()
{
    $ngay_hien_tai = now()->format('Y-m-d');
    $gio_hien_tai = now()->format('H:i:s');

    $ca_hien_tai = CaLamViec::where(function ($query) use ($gio_hien_tai) {
        $query->where(function ($q) use ($gio_hien_tai) {
            // Ca không qua 00:00
            $q->whereColumn('gio_bat_dau', '<', 'gio_ket_thuc')
            ->where('gio_bat_dau', '<=', $gio_hien_tai)
            ->where('gio_ket_thuc', '>', $gio_hien_tai);
        })->orWhere(function ($q) use ($gio_hien_tai) {
            // Ca qua mốc 00:00 (ví dụ: 16:00 -> 00:00)
            $q->whereColumn('gio_bat_dau', '>', 'gio_ket_thuc')
            ->where(function ($sub) use ($gio_hien_tai) {
                $sub->where('gio_bat_dau', '<=', $gio_hien_tai)
                    ->orWhere('gio_ket_thuc', '>', $gio_hien_tai);
            });
        });
    })->first();

    if (!$ca_hien_tai) {
        return redirect()
            ->back()
            ->with('warning', 'Hiện tại không có ca làm việc.');
    }

    $tong_doanh_thu_tien_mat_cua_ca = HoaDon::where(
        'id_ca_lam_viec', $ca_hien_tai->id
        )
        ->whereDate('created_at', $ngay_hien_tai)
        ->where('phuong_thuc_thanh_toan', '!=', 'payos')
        ->where('trang_thai', 'Hoàn Thành')
        ->sum('khach_can_tra');    

    
    $tong_doanh_thu_chuyen_khoan_cua_ca = HoaDon::where(
        'id_ca_lam_viec', $ca_hien_tai->id
        )
        ->whereDate('created_at', $ngay_hien_tai)
        ->where('phuong_thuc_thanh_toan', 'payos')
        ->where('trang_thai', 'Hoàn thành')
        ->sum('khach_can_tra');

    $tong_doanh_thu_cua_ca = $tong_doanh_thu_tien_mat_cua_ca + $tong_doanh_thu_chuyen_khoan_cua_ca;

    $cac_hoa_don_bi_huy_trong_ca = HoaDon::whereDate('created_at', $ngay_hien_tai)
            ->where('id_ca_lam_viec', $ca_hien_tai->id)
            ->where('trang_thai','Hủy')
            ->count();

    $tong_nhan_vien_cua_ca = ChiaCaLamViec::where('id_ca_lam_viec', $ca_hien_tai->id)
        ->where('ngay', $ngay_hien_tai)
        ->count('id');

    $nhan_vien = ChiaCaLamViec::with([
        'nguoiDung'])
         ->where('ngay', $ngay_hien_tai)
         ->where('id_ca_lam_viec', $ca_hien_tai->id)
         ->get();

    $danh_sach_hoa_don_cua_ca = HoaDon::with('khachHang')
         ->whereDate('created_at', $ngay_hien_tai)
         ->where('id_ca_lam_viec', $ca_hien_tai->id)
         ->get();
         
        

         
    return view(
        'admin_xem_truoc.ca-lam-viec.ca-lam-viec-hien-tai.thong-tin',
        compact(
            'ngay_hien_tai',
            'gio_hien_tai',
            'ca_hien_tai',
            'tong_doanh_thu_tien_mat_cua_ca',
            'tong_doanh_thu_chuyen_khoan_cua_ca',
            'tong_doanh_thu_cua_ca',
            'cac_hoa_don_bi_huy_trong_ca',
            'tong_nhan_vien_cua_ca',
            'danh_sach_hoa_don_cua_ca',
            'nhan_vien'
        )
    );
}


//chi tiết hóa đơn
 public function show($id_hoadon){
    $hoaDon = HoaDon::with(['nguoiDung', 'caLamViec'])
        ->findOrfail($id_hoadon);

    $chiTietHoaDon = ChiTietHoaDon::with(['bienTheSanPham'])
        ->where('id_hoa_don', $id_hoadon)
        ->get();

    return view('admin_xem_truoc.ca-lam-viec.ca-lam-viec-hien-tai.chi-tiet-hoa-don', compact('chiTietHoaDon', 'hoaDon'));
 }



}
