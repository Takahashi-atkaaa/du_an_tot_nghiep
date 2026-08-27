<?php

namespace App\Http\Controllers\admin\CaLam;

use App\Http\Controllers\Controller;
use App\Models\CaLamViec;
use App\Models\ChiaCaLamViec;
use App\Models\GiaoCa;
use App\Models\HoaDon;
use App\Models\NguoiDung;
use App\Services\RevenueStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LichSuCaLam extends Controller
{
    public function index(Request $request)
    {
        $ngayHienTai = now()->format('Y-m-d');

        $query = ChiaCaLamViec::select('ngay')
            ->where('ngay', '<=', $ngayHienTai);

        if ($request->filled('ngay')) {
            $query->whereDate('ngay', $request->ngay);
        }

        $ngay2 = $query->distinct()->orderByDesc('ngay')->paginate(20)->withQueryString();

        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.lich-su-ca-lam', compact('ngay2'));
    }

        public function cacCa(RevenueStatisticsService $revenueStatisticsService, $ngay, $id_ca = null)
        {
            $revenueStatuses = $revenueStatisticsService->salesRevenueStatuses();

            $caLam = ChiaCaLamViec::with('caLamViec')
                ->where('ngay', $ngay)
                ->select('id_ca_lam_viec')
                ->distinct()
                ->get();

            if ($id_ca === null) {
                $id_ca = $caLam->first()?->id_ca_lam_viec;
            }

            $caDangChon = CaLamViec::findOrFail($id_ca);

            $danhSachHoaDon = HoaDon::whereDate('created_at', $ngay)
                ->where('id_ca_lam_viec', $id_ca)
                ->whereIn('trang_thai', ['Hoàn thành', 'Đã đổi/trả hàng', 'Đã trả toàn bộ'])
                ->orderByDesc('created_at')
                ->get();

            $tongTienTraLaiKhachTrongCa = 0;

            foreach($danhSachHoaDon as $hoaDon){
                $hoaDon->tienTraKhach = $hoaDon->doiTras
                ?->flatMap(function ($doitra){
                    return $doitra->chiTietDoiTras ?? [];
                })
                ->sum('thanh_tien') ?? 0;

                $tongTienTraLaiKhachTrongCa += $hoaDon->tienTraKhach;
            }

            // chỉ hiện thị 7 hóa đơn
            $danhSachHoaDon = $danhSachHoaDon->take(7);

            $doanhThuTienMatCuaCa = HoaDon::where('id_ca_lam_viec', $id_ca)
                ->whereDate('created_at', $ngay)
                ->where('phuong_thuc_thanh_toan','Tiền mặt')
                ->whereIn('trang_thai', ['Hoàn thành', 'Đã đổi/trả hàng', 'Đã trả toàn bộ'])
                ->sum('khach_can_tra');

            $doanhThuChuyenKhoan = HoaDon::where('id_ca_lam_viec', $id_ca)
                ->whereDate('created_at', $ngay)
                ->where('phuong_thuc_thanh_toan','PayOS')
                ->whereIn('trang_thai', ['Hoàn thành', 'Đã đổi/trả hàng', 'Đã trả toàn bộ'])
                ->sum('khach_can_tra');

            $tongDoanhThuCuaCa = $doanhThuTienMatCuaCa + $doanhThuChuyenKhoan - $tongTienTraLaiKhachTrongCa;

            $doanhThuTienMatCuaCa -= $tongTienTraLaiKhachTrongCa;

            $tongHoaDoncuaCa = HoaDon::whereDate('created_at', $ngay)
                ->where('id_ca_lam_viec', $id_ca)
                ->count('id');

            $danhSachNhanVienTrongCa = ChiaCaLamViec::with('nguoiDung')
                ->whereDate('ngay', $ngay)
                ->where('id_ca_lam_viec', $id_ca)
                ->get();

            $tongNhanVienTrongCa = ChiaCaLamViec::with('nguoiDung')
                ->where('ngay', $ngay)
                ->where('id_ca_lam_viec', $id_ca)
                ->count('id');

            $danhSachDiemDanh = [];

            $danhSachTrongCaTrongCa = ChiaCaLamViec::whereDate('ngay', $ngay)
                ->where('id_ca_lam_viec', $id_ca)
                ->where('vai_tro_trong_ca', 'truong_ca')
                ->get();

            $giaoCa = GiaoCa::where('id_ca_lam_viec', $id_ca)
                ->whereDate('thoi_gian_bat_dau_ca', $ngay)
                ->first();

            $tongDoanhThuNgay = HoaDon::whereDate('created_at', $ngay)
                ->whereIn('trang_thai', [
                    'Đã trả toàn bộ','Đã đổi/trả hàng','Hoàn thành'
                ])
                ->whereIn('phuong_thuc_thanh_toan', ['Tiền mặt', 'PayOS'])
                ->sum('khach_can_tra');

            $danhSachHoaDonNgay = HoaDon::whereDate('created_at', $ngay)
                ->whereIn('trang_thai', ['Đã trả toàn bộ','Đã đổi/trả hàng','Hoàn thành'])
                ->whereIn('phuong_thuc_thanh_toan', ['Tiền mặt', 'PayOS'])
                ->get();


            //tính tiền trả lại cho khách của cả ngày
            $tienTraKhachCuaNgay = 0;

            foreach($danhSachHoaDonNgay as $hoaDon){
                $hoaDon->tienTraKhach = $hoaDon->doiTras
                ?->flatMap(function ($doitra){
                    return $doitra->chiTietDoiTras ?? [];
                })
                ->sum('thanh_tien') ?? 0;

                $tienTraKhachCuaNgay += $hoaDon->tienTraKhach;
            }

            $tongDoanhThuNgay -= $tienTraKhachCuaNgay;


            $tongSoHoaDonNgay = HoaDon::whereDate('created_at', $ngay)->count('id');

            $cacHoaDonDoiTraTrongCa = HoaDon::whereDate('created_at', $ngay)
                ->where('id_ca_lam_viec', $id_ca)
                ->whereIn('trang_thai', ['Đã đổi/trả hàng', 'Đã trả toàn bộ'])
                ->count();

            return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.cac-ca-lam', compact(
                'caLam',
                'ngay',
                'tongDoanhThuNgay',
                'tongSoHoaDonNgay',
                'caDangChon',
                'danhSachHoaDon',
                'tongDoanhThuCuaCa',
                'tongHoaDoncuaCa',
                'danhSachNhanVienTrongCa',
                'tongNhanVienTrongCa',
                'danhSachDiemDanh',
                'danhSachTrongCaTrongCa',
                'giaoCa',
                'cacHoaDonDoiTraTrongCa',
                'doanhThuTienMatCuaCa',
                'doanhThuChuyenKhoan',
                'tongTienTraLaiKhachTrongCa'
            ));
        }

    public function tao_giao_ca($id_ca, $ngay, RevenueStatisticsService $revenueStatisticsService)
    {
        $revenueStatuses = $revenueStatisticsService->salesRevenueStatuses();
        $ca = CaLamViec::findOrFail($id_ca);

        $tongTienMatCuaCa = $revenueStatisticsService->sumInvoiceNetRevenue(
            $revenueStatisticsService->invoiceNetRevenueQuery()
                ->whereDate('hoa_don.created_at', $ngay)
                ->where('hoa_don.id_ca_lam_viec', $id_ca)
                ->where('hoa_don.phuong_thuc_thanh_toan', '!=', 'payos')
                ->whereIn('hoa_don.trang_thai', $revenueStatuses)
        );

        $tongTienChuyenKhoan = $revenueStatisticsService->sumInvoiceNetRevenue(
            $revenueStatisticsService->invoiceNetRevenueQuery()
                ->whereDate('hoa_don.created_at', $ngay)
                ->where('hoa_don.id_ca_lam_viec', $id_ca)
                ->where('hoa_don.phuong_thuc_thanh_toan', 'payos')
                ->whereIn('hoa_don.trang_thai', $revenueStatuses)
        );

        $danhSachNhanVienTrongCa = ChiaCaLamViec::with('nguoiDung')
            ->whereDate('ngay', $ngay)
            ->where('id_ca_lam_viec', $id_ca)
            ->get();

        $danhSachTrongCaTrongCa = ChiaCaLamViec::whereDate('ngay', $ngay)
            ->where('id_ca_lam_viec', $id_ca)
            ->where('vai_tro_trong_ca', 'truong_ca')
            ->get();

        $danhSachTruongCa = NguoiDung::where('id_vai_tro', 2)->get();
        $caLamViecs = CaLamViec::all();

        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.tao-giao-ca', compact(
            'ca',
            'tongTienMatCuaCa',
            'tongTienChuyenKhoan',
            'danhSachNhanVienTrongCa',
            'danhSachTrongCaTrongCa',
            'ngay',
            'danhSachTruongCa',
            'caLamViecs'
        ));
    }

    public function giao_ca_store(Request $request)
    {
        $request->validate([
            'id_truong_ca_ban_giao' => 'required|exists:nguoi_dung,id',
            'id_truong_ca_nhan_ca' => 'required|exists:nguoi_dung,id',
            'id_ca_lam_viec' => 'required|exists:ca_lam_viec,id',
            'tien_mat_dau_ca' => 'required|numeric|min:0',
            'tien_mat_cuoi_ca' => 'required|numeric|min:0',
            'doanh_thu_chuyen_khoan' => 'numeric|min:0',
            'thoi_gian_bat_dau_ca' => 'required|date',
            'thoi_gian_ket_thuc_ca' => 'required|date|after:thoi_gian_bat_dau_ca',
            'trang_thai' => 'required|in:0,1',
            'ghi_chu' => 'nullable|string|max:1000',
        ]);

        GiaoCa::create([
            'id_truong_ca_ban_giao' => $request->id_truong_ca_ban_giao,
            'id_truong_ca_nhan_ca' => $request->id_truong_ca_nhan_ca,
            'id_ca_lam_viec' => $request->id_ca_lam_viec,
            'tien_mat_dau_ca' => $request->tien_mat_dau_ca,
            'tien_mat_cuoi_ca' => $request->tien_mat_cuoi_ca,
            'doanh_thu_chuyen_khoan' => $request->doanh_thu_chuyen_khoan,
            'chenh_lech' => -($request->chenh_lech),
            'thoi_gian_bat_dau_ca' => $request->thoi_gian_bat_dau_ca,
            'thoi_gian_ket_thuc_ca' => $request->thoi_gian_ket_thuc_ca,
            'trang_thai' => $request->trang_thai,
            'ghi_chu' => $request->ghi_chu,
        ]);

        return redirect()->back()->with('success', 'Tạo phiếu giao ca thành công.');
    }

    public function giao_ca_chi_tiet($id)
    {
        $giaoCa = GiaoCa::with(['truongCaBanGiao', 'truongCaNhanCa', 'caLamViec'])->findOrFail($id);

        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.chi-tiet-giao-ca', compact('giaoCa'));
    }

    public function sua_giao_ca($id)
    {
        $giaoCa = GiaoCa::with(['truongCaBanGiao', 'truongCaNhanCa', 'caLamViec'])->findOrFail($id);
        $danhSachTruongCa = NguoiDung::whereHas('vaiTro', function ($query) {
            $query->where('ten_vai_tro', 'Trưởng ca');
        })->get();

        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.sua-giao-ca', compact('giaoCa', 'danhSachTruongCa'));
    }

    public function cap_nhat_giao_ca(Request $request, $id)
    {
        $request->validate([
            'id_truong_ca_nhan_ca' => 'required|exists:nguoi_dung,id',
            'tien_mat_cuoi_ca' => 'required|numeric|min:0',
            'tien_mat_dau_ca' => 'required|numeric|min:0',
            'ghi_chu' => 'nullable|string',
            'thoi_gian_bat_dau_ca' => 'required',
            'thoi_gian_ket_thuc_ca' => 'required',
        ]);

        $giaoCa = GiaoCa::findOrFail($id);

        $giaoCa->update([
            'id_truong_ca_nhan_ca' => $request->id_truong_ca_nhan_ca,
            'tien_mat_cuoi_ca' => $request->tien_mat_cuoi_ca,
            'tien_mat_dau_ca' => $request->tien_mat_dau_ca,
            'chenh_lech' => $request->chenh_lech,
            'ghi_chu' => $request->ghi_chu,
            'thoi_gian_bat_dau_ca' => $request->thoi_gian_bat_dau_ca,
            'thoi_gian_ket_thuc_ca' => $request->thoi_gian_ket_thuc_ca,
            'trang_thai' => 1,
        ]);

        return redirect()->back()->with('success', 'Cập nhật giao ca thành công.');
    }

    public function xac_nhan_giao_ca($id)
    {
        $giaoCa = GiaoCa::findOrFail($id);

        if ($giaoCa->trang_thai == 1) {
            return back()->with('error', 'Giao ca đã được xác nhận.');
        }

        if (Auth::user()->id_vai_tro != 1 && Auth::id() != $giaoCa->id_truong_ca_nhan_ca) {
            abort(403, 'Bạn không có quyền xác nhận giao ca.');
        }

        $giaoCa->trang_thai = 1;
        $giaoCa->save();

        return redirect()->back()->with('success', 'Xác nhận giao ca thành công.');
    }

    public function tu_choi_giao_ca($id)
    {
        $giaoCa = GiaoCa::findOrFail($id);

        if ($giaoCa->trang_thai == 1) {
            return back()->with('error', 'Giao ca đã được xác nhận.');
        }

        if (Auth::user()->id_vai_tro != 1 && Auth::id() != $giaoCa->id_truong_ca_nhan_ca) {
            abort(403, 'Bạn không có quyền xác nhận giao ca.');
        }

        $giaoCa->trang_thai = 2;
        $giaoCa->save();

        return redirect()->back()->with('success', 'Đã từ chối giao ca.');
    }

    public function hoa_don_cua_ca(Request $request, $ngay, $id_ca)
    {
        $tuKhoa = $request->input('tu_khoa');

        $hoaDonCuaCa = HoaDon::whereDate('created_at', $ngay)
            ->where('id_ca_lam_viec', $id_ca)
            ->when($tuKhoa, function ($query) use ($tuKhoa) {
                $query->where(function ($q) use ($tuKhoa) {
                    $q->where('id', 'like', '%' . $tuKhoa . '%');
                });
            })
            ->latest('created_at')
            ->get();

        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.danh-sach-hoa-don-cua-ca', compact('hoaDonCuaCa', 'ngay', 'id_ca', 'tuKhoa'));
    }

    public function hoa_don_doi_tra_cua_ca(Request $request, $ngay, $id_ca)
    {
        $tuKhoa = $request->input('tu_khoa');

        $hoaDonCuaCa = HoaDon::whereDate('created_at', $ngay)
            ->where('id_ca_lam_viec', $id_ca)
            ->whereIn('trang_thai', ['Đã trả toàn bộ', 'Đã đổi/trả hàng'])
            ->when($tuKhoa, function ($query) use ($tuKhoa) {
                $query->where(function ($q) use ($tuKhoa) {
                    $q->where('id', 'like', '%' . $tuKhoa . '%');
                });
            })
            ->latest('created_at')
            ->get();

        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.danh-sach-hoa-don-doi-tra', compact('hoaDonCuaCa', 'ngay', 'id_ca', 'tuKhoa'));
    }
}
