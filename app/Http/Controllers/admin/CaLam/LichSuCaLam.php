<?php

namespace App\Http\Controllers\admin\CaLam;

use App\Http\Controllers\Controller;
use App\Models\CaLamViec;
use App\Models\ChiaCaLamViec;
use App\Models\ChiTietHoaDon;
use App\Models\GiaoCa;
use App\Models\HoaDon;
use App\Models\NguoiDung;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LichSuCaLam extends Controller
{
    // hiển thị các ca <= ngày hiện tại
    public function index(Request $request)
    {
        $ngayHienTai = now()->format('Y-m-d');

        $query = ChiaCaLamViec::select('ngay')
            ->where('ngay', '<=', $ngayHienTai);

        // Nếu có chọn ngày thì chỉ lấy ngày đó
        if ($request->filled('ngay')) {
            $query->whereDate('ngay', $request->ngay);
        }

        $ngay2 = $query
            ->distinct()
            ->orderByDesc('ngay')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.lich-su-ca-lam',
            compact('ngay2')
        );
    }


    //các ca trong ngày
    public function cacCa($ngay, $id_ca = null){
        // Lấy danh sách các ca làm việc trong ngày
        $caLam = ChiaCaLamViec::with('caLamViec')
            ->where('ngay', $ngay)
            ->select('id_ca_lam_viec')
            ->distinct()
            ->get();

        if ($id_ca == null) {
            $id_ca = $caLam->first()?->id_ca_lam_viec;
        }


        /////////////////////////////////chi tiết từng ca
        // $caChiTiet = CaLamViec::findOrFail($id_ca);
        $caDangChon = CaLamViec::findOrFail($id_ca);

        $danhSachHoaDon = HoaDon::whereDate('created_at', $ngay)
           ->where('id_ca_lam_viec', $id_ca)
           ->get();

        $tongDoanhThuCuaCa = HoaDon::whereDate('created_at', $ngay)
           ->where('id_ca_lam_viec', $id_ca)
           ->sum('khach_can_tra');

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

        $tongTienMatCuaCa = HoaDon::whereDate('created_at', $ngay)
            ->where('id_ca_lam_viec', $id_ca)
            ->sum('khach_can_tra');

        $ngay = $ngay;

        $tongDoanhThuNgay =HoaDon::whereDate('created_at', $ngay)
            ->sum('khach_can_tra');

        $tongSoHoaDonNgay =HoaDon::whereDate('created_at', $ngay)
            ->count('id');

        //trả dữ liệu về view
        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.cac-ca-lam',compact(
            'caLam','ngay','tongDoanhThuNgay','tongSoHoaDonNgay','caDangChon',
            'danhSachHoaDon', 'tongDoanhThuCuaCa','tongHoaDoncuaCa','danhSachNhanVienTrongCa',
            'tongNhanVienTrongCa', 'danhSachDiemDanh', 'danhSachTrongCaTrongCa','giaoCa', 'tongTienMatCuaCa'
            )
        );
    }


    //giao ca
    public function tao_giao_ca($id_ca, $ngay){
        $ca = CaLamViec::findOrfail($id_ca);

        $tongTienMatCuaCa = HoaDon::whereDate('created_at', $ngay)
            ->where('id_ca_lam_viec', $id_ca)
            ->sum('khach_can_tra');

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



        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.tao-giao-ca', compact('ca', 'tongTienMatCuaCa', 'danhSachNhanVienTrongCa', 'danhSachTrongCaTrongCa', 'ngay','danhSachTruongCa','caLamViecs'));
    }


    //tạo giao ca
    public function giao_ca_store(Request $request){
        $request->validate([
            'id_truong_ca_ban_giao' => 'required|exists:nguoi_dung,id',
            'id_truong_ca_nhan_ca' => 'required|exists:nguoi_dung,id',
            'id_ca_lam_viec' => 'required|exists:ca_lam_viec,id',

            'tien_mat_dau_ca' => 'required|numeric|min:0',
            'tien_mat_cuoi_ca' => 'required|numeric|min:0',

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

            'chenh_lech' => -($request->chenh_lech),

            'thoi_gian_bat_dau_ca' => $request->thoi_gian_bat_dau_ca,
            'thoi_gian_ket_thuc_ca' => $request->thoi_gian_ket_thuc_ca,

            'trang_thai' => $request->trang_thai,
            'ghi_chu' => $request->ghi_chu,
        ]);

        return redirect()->back()
            ->with('success', 'Tạo phiếu giao ca thành công.');
    }


    // chi tiết giao ca
    public function giao_ca_chi_tiet($id){
        $giaoCa = GiaoCa::with(['truongCaBanGiao', 'truongCaNhanCa', 'caLamViec'])
            ->findOrFail($id);
        return view('admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.chi-tiet-giao-ca', compact('giaoCa'));
    }


    //sửa giao ca
    public function sua_giao_ca($id)
    {
        $giaoCa = GiaoCa::with([
            'truongCaBanGiao',
            'truongCaNhanCa',
            'caLamViec'
        ])->findOrFail($id);

        $danhSachTruongCa = NguoiDung::whereHas('vaiTro', function ($query) {
            $query->where('ten_vai_tro', 'Trưởng ca');
        })->get();

        return view(
            'admin_xem_truoc.ca-lam-viec.lich-su-ca-lam.sua-giao-ca',
            compact('giaoCa', 'danhSachTruongCa')
        );
    }

    //cập nhật giao ca
    public function cap_nhat_giao_ca(Request $request, $id)
    {
        $request->validate([
            'id_truong_ca_nhan_ca'  => 'required|exists:nguoi_dung,id',
            'tien_mat_cuoi_ca'      => 'required|numeric|min:0',
            'tien_mat_dau_ca'       => 'required|numeric|min:0',
            'ghi_chu'               => 'nullable|string',
            'thoi_gian_bat_dau_ca'  => 'required',
            'thoi_gian_ket_thuc_ca' => 'required',
        ]);

        $giaoCa = GiaoCa::findOrFail($id);

        $giaoCa->update([
            'id_truong_ca_nhan_ca'  => $request->id_truong_ca_nhan_ca,
            'tien_mat_cuoi_ca'      => $request->tien_mat_cuoi_ca,
            'tien_mat_dau_ca'       => $request->tien_mat_dau_ca,
            'chenh_lech'            => $request->chenh_lech,
            'ghi_chu'               => $request->ghi_chu,
            'thoi_gian_bat_dau_ca'  => $request->thoi_gian_bat_dau_ca,
            'thoi_gian_ket_thuc_ca' => $request->thoi_gian_ket_thuc_ca,
            'trang_thai'            => 1,  
        ]);

        return redirect()
            ->back()
            ->with('success', 'Cập nhật giao ca thành công.');
    }



    //xác nhận giao ca
    public function xac_nhan_giao_ca($id)
    {
        $giaoCa = GiaoCa::findOrFail($id);

        // Chỉ xác nhận khi đang chờ
        if ($giaoCa->trang_thai == 1) {
            return back()->with('error', 'Giao ca đã được xác nhận.');
        }

        // Kiểm tra quyền
        if (Auth::user()->id_vai_tro != 1 && Auth::id() != $giaoCa->id_truong_ca_nhan_ca) {
            abort(403, 'Bạn không có quyền xác nhận giao ca.');
        }

        $giaoCa->trang_thai = 1;

        $giaoCa->save();

        return redirect()
            ->back()
            ->with('success', 'Xác nhận giao ca thành công.');
    }

    public function tu_choi_giao_ca($id)
    {
        $giaoCa = GiaoCa::findOrFail($id);

        // Chỉ xác nhận khi đang chờ
        if ($giaoCa->trang_thai == 1) {
            return back()->with('error', 'Giao ca đã được xác nhận.');
        }

        // Kiểm tra quyền
        if (Auth::user()->id_vai_tro != 1 && Auth::id() != $giaoCa->id_truong_ca_nhan_ca) {
            abort(403, 'Bạn không có quyền xác nhận giao ca.');
        }

        $giaoCa->trang_thai = 2;

        $giaoCa->save();

        return redirect()
            ->back()
            ->with('success', 'Đã từ chối giao ca.');
    }
}
