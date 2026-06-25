<?php

namespace App\Http\Controllers\admin\CaLam;

use App\Http\Controllers\Controller;
use App\Models\CaLamViec;
use App\Models\ChiaCaLamViec;
use App\Models\ChiTietHoaDon;
use App\Models\HoaDon;
use Illuminate\Http\Request;

class LichSuCaLam extends Controller
{
    // hiển thị các ca <= ngày hiện tại
    public function index(){
        $ngayHienTai = now()->format('Y-m-d');

        $ngay2 = ChiaCaLamViec::select('ngay')
            ->where('ngay','<=',$ngayHienTai)
            ->distinct()
            ->orderByDesc('ngay')
            ->paginate(20);

        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.lich-su-ca-lam', compact('ngay2'));
    }


    //các ca trong ngày
    public function cacCa($ngay){
        $caLam2 = ChiaCaLamViec::with('caLamViec')
            ->where('ngay', $ngay)
            ->select('id_ca_lam_viec')
            ->distinct()
            ->get();
        $ngay = $ngay;

        $tongDoanhThuNgay =HoaDon::whereDate('created_at', $ngay)
            ->sum('tong_tien_hang');

        $tongSoHoaDonNgay =HoaDon::whereDate('created_at', $ngay)
            ->count('id');
             
        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.cac-ca-lam', compact('caLam2', 'ngay', 'tongDoanhThuNgay', 'tongSoHoaDonNgay'));
    }


    // chi tiết lịch sử của ca 
    public function chi_tiet_ca($id_ca, $ngay){
        $ca = CaLamViec::findOrfail($id_ca);

        $danhSachHoaDon = HoaDon::whereDate('created_at', $ngay)
           ->where('id_ca_lam_viec', $id_ca)
           ->get();

        $tongDoanhThuCuaCa = HoaDon::whereDate('created_at', $ngay)
           ->where('id_ca_lam_viec', $id_ca)
           ->sum('tong_tien_hang');

        $tongHoaDoncuaCa = HoaDon::whereDate('created_at', $ngay)
           ->where('id_ca_lam_viec', $id_ca)
           ->count('id');

        $danhSachNhanVienTrongCa = ChiaCaLamViec::with('nguoiDung')
            ->where('ngay', $ngay)
            ->where('id_ca_lam_viec', $id_ca)
            ->get();

        $tongNhanVienTrongCa = ChiaCaLamViec::with('nguoiDung')
            ->where('ngay', $ngay)
            ->where('id_ca_lam_viec', $id_ca)
            ->count('id');

        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.chi-tiet-ca-lam', compact('ca', 'danhSachHoaDon', 'danhSachNhanVienTrongCa', 'tongDoanhThuCuaCa', 'tongNhanVienTrongCa', 'tongHoaDoncuaCa', 'ngay'));
    }

    //chi tiết hóa đơn trong lịch sử ca làm
    public function chi_tiet_hoa_don($id_hoadon, $ngay){
        $hoaDon = HoaDon::with(['nguoiDung', 'caLamViec'])
            ->findOrfail($id_hoadon);

        $chiTietHoaDon = ChiTietHoaDon::with(['sanPham', 'chiTietPhieu'])
            ->where('id_hoa_don', $id_hoadon)
            ->get();
        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.chi-tiet-hoa-don', compact('chiTietHoaDon', 'hoaDon', 'ngay'));
    }

}
