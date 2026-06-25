<?php

namespace App\Http\Controllers\admin\KhachHang;

use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use App\Http\Requests\KhachHang\CapNhatKhachHangRequest;
use App\Http\Requests\KhachHang\ThemKhachHangRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KhachHangController extends Controller
{
	public function index(Request $request): View
	{
		$keyword = $request->input('keyword');
		$trangThai = $request->filled('trang_thai') ? $request->boolean('trang_thai') : null;

		$query = KhachHang::query()->search($keyword);

		if (! is_null($trangThai)) {
			$query->where('trang_thai', $trangThai);
		}

		$khachHangs = $query->orderByDesc('id')->paginate(10)->withQueryString();

		return view('admin_xem_truoc.khach-hang.index', [
			'khachHangs' => $khachHangs,
			'keyword' => $keyword,
			'trangThai' => $request->input('trang_thai'),
			'tongKhachHang' => KhachHang::withTrashed()->count(),
			'dangHoatDong' => KhachHang::query()->where('trang_thai', true)->count(),
			'ngungHoatDong' => KhachHang::query()->where('trang_thai', false)->count(),
			'tongCongNo' => KhachHang::withTrashed()->sum('cong_no'),
			'tongDiemTichLuy' => KhachHang::withTrashed()->sum('diem_tich_luy'),
		]);
	}

	public function create(): View
	{
		return view('admin_xem_truoc.khach-hang.create', [
			'khachHang' => new KhachHang(),
		]);
	}

	public function store(ThemKhachHangRequest $request): RedirectResponse
	{
		$validated = $request->validated();

		KhachHang::create([
			'ten_khach_hang' => $validated['ten_khach_hang'],
			'so_dien_thoai' => $validated['so_dien_thoai'],
			'email' => $validated['email'] ?? null,
			'dia_chi' => $validated['dia_chi'] ?? null,
			'cong_no' => 0,
			'phi_chu' => $validated['phi_chu'] ?? null,
			'trang_thai' => $request->boolean('trang_thai'),
			'diem_tich_luy' => 0,
			'tong_chi_tieu' => 0,
		]);

		return redirect()->route('khach-hang.index')->with('success', 'Đã thêm khách hàng mới.');
	}

	public function show(KhachHang $khachHang): View
	{
		$lichSuTichDiems = $khachHang->lichSuTichDiems()
			->with('hoaDon')
			->orderByDesc('created_at')
			->paginate(8)
			->withQueryString();

		return view('admin_xem_truoc.khach-hang.show', [
			'khachHang' => $khachHang,
			'lichSuTichDiems' => $lichSuTichDiems,
			'tongSoHoaDon' => $khachHang->hoaDons()->count(),
			'tongTienDaMua' => $khachHang->hoaDons()->sum('khach_can_tra'),
		]);
	}

	public function edit(KhachHang $khachHang): View
	{
		return view('admin_xem_truoc.khach-hang.edit', [
			'khachHang' => $khachHang,
		]);
	}

	public function update(CapNhatKhachHangRequest $request, KhachHang $khachHang): RedirectResponse
	{
		$validated = $request->validated();

		$khachHang->update([
			'ten_khach_hang' => $validated['ten_khach_hang'],
			'so_dien_thoai' => $validated['so_dien_thoai'],
			'email' => $validated['email'] ?? null,
			'dia_chi' => $validated['dia_chi'] ?? null,
			'cong_no' => $validated['cong_no'],
			'phi_chu' => $validated['phi_chu'] ?? null,
			'trang_thai' => $request->boolean('trang_thai'),
		]);

		return redirect()->route('khach-hang.index')->with('success', 'Đã cập nhật khách hàng.');
	}

	
    public function destroy(KhachHang $khachHang): RedirectResponse
{
    $khachHang->delete($khachHang->id);

    return redirect()
        ->route('khach-hang.index')
        ->with('success', 'Đã xóa mềm khách hàng thành công.');
}

public function trash()
{
    $khachHangs = KhachHang::onlyTrashed()
        ->latest('deleted_at')
        ->paginate(10);

    return view(
        'admin_xem_truoc.khach-hang.trash',
        compact('khachHangs')
    );
}
public function restore($id)
{
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
public function forceDelete($id)
{
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
