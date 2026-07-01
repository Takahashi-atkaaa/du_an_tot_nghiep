<?php

namespace App\Http\Controllers\nhan_vien;

use App\Http\Controllers\Controller;
use App\Http\Requests\KhachHang\CapNhatSoDienThoaiRequest;
use App\Http\Requests\KhachHang\NhanvienThemKhachHangRequest;
use App\Models\KhachHang;
use Illuminate\Http\Request;

class KhachHangController extends Controller
{
    /**
     * Danh sách khách hàng
     */
    public function index(Request $request)
{
    $keyword = $request->keyword;

    $khachHangs = KhachHang::query();

    if ($keyword) {
        $khachHangs->where('ten_khach_hang', 'like', "%$keyword%")
                   ->orWhere('so_dien_thoai', 'like', "%$keyword%")
                   ->orWhere('email', 'like', "%$keyword%");
    }

    $khachHangs = $khachHangs->paginate(10);

    return view(
        'nhan_vien_view.khach-hang.index',
        compact('khachHangs', 'keyword')
    );
}

    /**
     * Form thêm khách hàng
     */
    public function create()
    {
        return view('nhan_vien_view.khach-hang.create');
    }

    /**
     * Lưu khách hàng mới
     */
    public function store(NhanvienThemKhachHangRequest $request)
    {
        $validated = $request->validated();

        KhachHang::create([
            'ten_khach_hang' => $validated['ten_khach_hang'],
            'so_dien_thoai' => $validated['so_dien_thoai'],
            'email' => $validated['email'] ?? null,
            'dia_chi' => $validated['dia_chi'] ?? null,
            'diem_tich_luy' => 0,
            'tong_chi_tieu' => 0,
        ]);

        return redirect()
            ->route('nhan-vien.khach-hang.index')
            ->with('success', 'Thêm khách hàng thành công.');
    }

    /**
     * Chi tiết khách hàng
     */
    public function show(KhachHang $khachHang)
    {
        return view(
            'nhan_vien_view.khach-hang.show',
            compact('khachHang')
        );
    }

public function themNhanh(NhanvienThemKhachHangRequest $request)
{
    $validated = $request->validated();
    $khachHang = KhachHang::create([
        'ten_khach_hang' => $validated['ten_khach_hang'],
        'so_dien_thoai'  => $validated['so_dien_thoai'],
        'email'          => $validated['email'],
        'dia_chi'        => $validated['dia_chi'],
        'diem_tich_luy'  => 0,
        'tong_chi_tieu'  => 0,
    ]);

    return response()->json([
        'success' => true,
        'khach_hang' => $khachHang
    ]);
}

    /**
     * Form sửa số điện thoại
     */
    public function editPhone(KhachHang $khachHang)
    {
        return view(
            'nhan_vien_view.khach-hang.edit-phone',
            compact('khachHang')
        );
    }

    /**
     * Cập nhật số điện thoại
     */
    public function updatePhone(CapNhatSoDienThoaiRequest $request, KhachHang $khachHang)
    {
        $validated = $request->validated();

        $khachHang->update([
            'so_dien_thoai' => $validated['so_dien_thoai'],
        ]);

        return redirect()
            ->route('nhan-vien.khach-hang.show', $khachHang)
            ->with('success', 'Cập nhật số điện thoại thành công.');
    }
}