<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\BangLuong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BangLuongController extends Controller
{
    public function index(): View
    {
        $bangLuongs = BangLuong::orderByDesc('id')->paginate(15);

        return view('admin_xem_truoc.nhan-su.bang-luong.index', compact('bangLuongs'));
    }

    public function create(): View
    {
        return view('admin_xem_truoc.nhan-su.bang-luong.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ten_bang_luong' => 'required|string|max:255',
            'ky_lam_viec' => 'required|string|max:255',
            'tong_luong_tat_ca_nhan_vien' => 'nullable|numeric|min:0',
            'so_nhan_vien' => 'nullable|integer|min:0',
            'trang_thai' => 'nullable|string|max:255',
        ]);

        BangLuong::create($data);

        return redirect()->route('bang-luong.index')->with('success', 'Tạo bảng lương thành công.');
    }

    public function show(BangLuong $bangLuong): View
    {
        return view('admin_xem_truoc.nhan-su.bang-luong.show', compact('bangLuong'));
    }

    public function edit(BangLuong $bangLuong): View
    {
        return view('admin_xem_truoc.nhan-su.bang-luong.edit', compact('bangLuong'));
    }

    public function update(Request $request, BangLuong $bangLuong): RedirectResponse
    {
        $data = $request->validate([
            'ten_bang_luong' => 'required|string|max:255',
            'ky_lam_viec' => 'required|string|max:255',
            'tong_luong_tat_ca_nhan_vien' => 'nullable|numeric|min:0',
            'so_nhan_vien' => 'nullable|integer|min:0',
            'trang_thai' => 'nullable|string|max:255',
        ]);

        $bangLuong->update($data);

        return redirect()->route('bang-luong.index')->with('success', 'Cập nhật bảng lương thành công.');
    }

    public function destroy(BangLuong $bangLuong): RedirectResponse
    {
        $bangLuong->delete();

        return redirect()->route('bang-luong.index')->with('success', 'Đã xóa bảng lương.');
    }
}
