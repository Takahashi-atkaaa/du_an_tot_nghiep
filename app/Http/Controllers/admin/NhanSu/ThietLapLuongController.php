<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\ThietLapLuong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThietLapLuongController extends Controller
{
    public function index(): View
    {
        $thietLapLuongs = ThietLapLuong::with('nguoiDung')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin_xem_truoc.nhan-su.thiet-lap-luong.index', compact('thietLapLuongs'));
    }

    public function create(): View
    {
        return view('admin_xem_truoc.nhan-su.thiet-lap-luong.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_nguoi_dung' => 'required|exists:nguoi_dung,id',
            'luong_theo_gio' => 'nullable|numeric|min:0',
            'phu_cap' => 'nullable|numeric|min:0',
            'phat_di_muon' => 'nullable|numeric|min:0',
            'luong_tang_ca' => 'nullable|numeric|min:0',
            'thuong' => 'nullable|numeric|min:0',
            'phat_bo_ca' => 'nullable|numeric|min:0',
        ]);

        ThietLapLuong::create($data);

        return redirect()->route('thiet-lap-luong.index')->with('success', 'Thiết lập lương thành công.');
    }

    public function edit(ThietLapLuong $thietLapLuong): View
    {
        return view('admin_xem_truoc.nhan-su.thiet-lap-luong.edit', compact('thietLapLuong'));
    }

    public function update(Request $request, ThietLapLuong $thietLapLuong): RedirectResponse
    {
        $data = $request->validate([
            'luong_theo_gio' => 'nullable|numeric|min:0',
            'phu_cap' => 'nullable|numeric|min:0',
            'phat_di_muon' => 'nullable|numeric|min:0',
            'luong_tang_ca' => 'nullable|numeric|min:0',
            'thuong' => 'nullable|numeric|min:0',
            'phat_bo_ca' => 'nullable|numeric|min:0',
        ]);

        $thietLapLuong->update($data);

        return redirect()->route('thiet-lap-luong.index')->with('success', 'Cập nhật thành công.');
    }

    public function destroy(ThietLapLuong $thietLapLuong): RedirectResponse
    {
        $thietLapLuong->delete();

        return redirect()->route('thiet-lap-luong.index')->with('success', 'Đã xóa thiết lập lương.');
    }
}
