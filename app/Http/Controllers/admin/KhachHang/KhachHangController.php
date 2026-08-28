<?php

namespace App\Http\Controllers\admin\KhachHang;

use App\Http\Controllers\Controller;
use App\Http\Requests\KhachHang\CapNhatKhachHangRequest;
use App\Http\Requests\KhachHang\ThemKhachHangRequest;
use App\Models\KhachHang;
use App\Services\RevenueStatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KhachHangController extends Controller
{
    /**
     * Danh sách khách hàng
     */
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');

        $trangThai = $request->filled('trang_thai')
            ? $request->boolean('trang_thai')
            : null;

        $query = KhachHang::query()
            ->search($keyword);

        if (!is_null($trangThai)) {
            $query->where('trang_thai', $trangThai);
        }

        $khachHangs = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin_xem_truoc.khach-hang.index', [
            'khachHangs' => $khachHangs,
            'keyword' => $keyword,
            'trangThai' => $request->input('trang_thai'),

            'tongKhachHang' => KhachHang::withTrashed()->count(),

            'dangHoatDong' => KhachHang::query()
                ->where('trang_thai', true)
                ->count(),

            'ngungHoatDong' => KhachHang::query()
                ->where('trang_thai', false)
                ->count(),

            'tongDiemTichLuy' => KhachHang::withTrashed()
                ->sum('diem_tich_luy'),
        ]);
    }


    /**
     * Form thêm khách hàng
     */
    public function create(): View
    {
        return view('admin_xem_truoc.khach-hang.create', [
            'khachHang' => new KhachHang(),
        ]);
    }


    /**
     * Thêm khách hàng
     */
    public function store(
        ThemKhachHangRequest $request
    ): RedirectResponse {

        $validated = $request->validated();

        KhachHang::create([
            'ten_khach_hang' => $validated['ten_khach_hang'],
            'so_dien_thoai' => $validated['so_dien_thoai'],
            'email' => $validated['email'] ?? null,
            'dia_chi' => $validated['dia_chi'] ?? null,

            'phi_chu' => $validated['phi_chu'] ?? null,

            'trang_thai' => $request->boolean('trang_thai'),

            'diem_tich_luy' => 0,

            'tong_chi_tieu' => 0,
        ]);

        return redirect()
            ->route('khach-hang.index')
            ->with('success', 'Đã thêm khách hàng mới.');
    }


    /**
     * ============================================================
     * CHI TIẾT KHÁCH HÀNG
     * ============================================================
     *
     * Hiển thị:
     * - Thông tin khách hàng
     * - Tổng số hóa đơn
     * - Tổng tiền đã mua
     * - Lịch sử hóa đơn
     * - Lịch sử tích điểm
     */
    public function show(
        KhachHang $khachHang,
        RevenueStatisticsService $revenueStatisticsService
    ): View {

        /*
        |--------------------------------------------------------------------------
        | LỊCH SỬ TÍCH ĐIỂM
        |--------------------------------------------------------------------------
        */

        $lichSuTichDiems = $khachHang
            ->lichSuTichDiems()
            ->with('hoaDon')
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'diem_page')
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | TỔNG TIỀN KHÁCH ĐÃ MUA
        |--------------------------------------------------------------------------
        |
        | Sử dụng RevenueStatisticsService để đồng bộ cách tính doanh thu
        | với Dashboard/Báo cáo.
        |
        */

        $tongTienDaMua = $revenueStatisticsService->sumInvoiceNetRevenue(
            $revenueStatisticsService
                ->invoiceNetRevenueQuery()
                ->where('hoa_don.id_khach_hang', $khachHang->id)
        );


        /*
        |--------------------------------------------------------------------------
        | DANH SÁCH HÓA ĐƠN
        |--------------------------------------------------------------------------
        */

        $hoaDons = $khachHang
            ->hoaDons()
            ->with([
                'nguoiDung',
                'doiTras.chiTietDoiTras',
            ])
            ->orderByDesc('created_at')
            ->paginate(
                10,
                ['*'],
                'hoa_don_page'
            )
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | TỔNG SỐ HÓA ĐƠN
        |--------------------------------------------------------------------------
        */

        $tongSoHoaDon = $khachHang
            ->hoaDons()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | TỔNG ĐIỂM TÍCH LŨY HIỆN TẠI
        |--------------------------------------------------------------------------
        */

        $diemTichLuy = (int) (
            $khachHang->diem_tich_luy ?? 0
        );


        /*
        |--------------------------------------------------------------------------
        | TỔNG CHI TIÊU
        |--------------------------------------------------------------------------
        |
        | Không lấy cứng từ tong_chi_tieu nếu hệ thống đã có logic
        | tính doanh thu ròng.
        |
        */

        $tongChiTieu = (float) $tongTienDaMua;


        /*
        |--------------------------------------------------------------------------
        | TRẢ DỮ LIỆU SANG VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'admin_xem_truoc.khach-hang.show',
            [
                'khachHang' => $khachHang,

                'lichSuTichDiems' => $lichSuTichDiems,

                'hoaDons' => $hoaDons,

                'tongSoHoaDon' => $tongSoHoaDon,

                'tongTienDaMua' => $tongTienDaMua,

                'diemTichLuy' => $diemTichLuy,

                'tongChiTieu' => $tongChiTieu,
            ]
        );
    }


    /**
     * Form chỉnh sửa khách hàng
     */
    public function edit(
        KhachHang $khachHang
    ): View {

        return view(
            'admin_xem_truoc.khach-hang.edit',
            [
                'khachHang' => $khachHang,
            ]
        );
    }


    /**
     * Cập nhật khách hàng
     */
    public function update(
        CapNhatKhachHangRequest $request,
        KhachHang $khachHang
    ): RedirectResponse {

        $validated = $request->validated();

        $khachHang->update([
            'ten_khach_hang' => $validated['ten_khach_hang'],
            'so_dien_thoai' => $validated['so_dien_thoai'],
            'email' => $validated['email'] ?? null,
            'dia_chi' => $validated['dia_chi'] ?? null,

            'phi_chu' => $validated['phi_chu'] ?? null,

            'trang_thai' => $request->boolean('trang_thai'),
        ]);

        return redirect()
            ->route('khach-hang.index')
            ->with('success', 'Đã cập nhật khách hàng.');
    }


    /**
     * Xóa mềm khách hàng
     */
    public function destroy(
        KhachHang $khachHang
    ): RedirectResponse {

        $khachHang->delete($khachHang->id);

        return redirect()
            ->route('khach-hang.index')
            ->with(
                'success',
                'Đã xóa mềm khách hàng thành công.'
            );
    }


    /**
     * Thùng rác khách hàng
     */
    public function trash(): View
    {
        $khachHangs = KhachHang::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(10);

        return view(
            'admin_xem_truoc.khach-hang.trash',
            compact('khachHangs')
        );
    }


    /**
     * Khôi phục khách hàng
     */
    public function restore(
        $id
    ): RedirectResponse {

        $khachHang = KhachHang::onlyTrashed()
            ->findOrFail($id);

        $khachHang->restore();

        return redirect()
            ->route('khach-hang.trash')
            ->with(
                'success',
                'Khôi phục khách hàng thành công.'
            );
    }


    /**
     * Xóa vĩnh viễn khách hàng
     */
    public function forceDelete(
        $id
    ): RedirectResponse {

        $khachHang = KhachHang::onlyTrashed()
            ->findOrFail($id);

        $khachHang->forceDelete();

        return redirect()
            ->route('khach-hang.trash')
            ->with(
                'success',
                'Đã xóa vĩnh viễn khách hàng.'
            );
    }
}