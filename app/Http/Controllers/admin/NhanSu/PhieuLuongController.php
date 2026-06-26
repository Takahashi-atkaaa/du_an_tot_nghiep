<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\PhieuLuong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhieuLuongController extends Controller
{
    public function index(): View
    {
        $phieuLuongs = PhieuLuong::with(['nguoiDung', 'bangLuong'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin_xem_truoc.nhan-su.phieu-luong.index', compact('phieuLuongs'));
    }

    public function create(): View
    {
        return view('admin_xem_truoc.nhan-su.phieu-luong.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_nguoi_dung' => 'required|exists:nguoi_dung,id',
            'id_bang_luong' => 'required|exists:bang_luong,id',
            'luong_tam_tinh' => 'required|numeric|min:0',
            'trang_thai' => 'nullable|string|max:255',
            'tong_gio_lam_thuc_te' => 'nullable|numeric|min:0',
            'tong_luong' => 'nullable|numeric|min:0',
        ]);

        PhieuLuong::create($data);

        return redirect()->route('phieu-luong.index')->with('success', 'Tạo phiếu lương thành công.');
    }

    public function edit(PhieuLuong $phieuLuong): View
    {
        return view('admin_xem_truoc.nhan-su.phieu-luong.edit', compact('phieuLuong'));
    }

    public function update(Request $request, PhieuLuong $phieuLuong): RedirectResponse
    {
        $data = $request->validate([
            'luong_tam_tinh' => 'required|numeric|min:0',
            'trang_thai' => 'nullable|string|max:255',
            'tong_gio_lam_thuc_te' => 'nullable|numeric|min:0',
            'tong_luong' => 'nullable|numeric|min:0',
        ]);

        $phieuLuong->update($data);

        return redirect()->route('phieu-luong.index')->with('success', 'Cập nhật phiếu lương thành công.');
    }

    public function destroy(PhieuLuong $phieuLuong): RedirectResponse
    {
        $phieuLuong->delete();

        return redirect()->route('phieu-luong.index')->with('success', 'Đã xóa phiếu lương.');
    }
}
