<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\BangLuong;
use App\Models\PhieuLuong;
use App\Http\Requests\PhieuLuong\UpdatePhieuLuongRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhieuLuongController extends Controller
{
    public function index(Request $request): View
    {
        $query = PhieuLuong::with(['nguoiDung', 'bangLuong'])->orderBy('created_at', 'desc');

        if ($request->filled('id_bang_luong')) {
            $query->where('id_bang_luong', $request->id_bang_luong);
        }

        if ($request->filled('id_nguoi_dung')) {
            $query->where('id_nguoi_dung', $request->id_nguoi_dung);
        }

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $phieuLuongs = $query->paginate(10);
        $bangLuongs = BangLuong::orderBy('created_at', 'desc')->get();

        return view('admin_xem_truoc.nhan-su.phieu-luong.index', compact('phieuLuongs', 'bangLuongs'));
    }

    public function show(PhieuLuong $phieuLuong): View
    {
        $phieuLuong->load(['nguoiDung', 'bangLuong']);

        return view('admin_xem_truoc.nhan-su.phieu-luong.show', compact('phieuLuong'));
    }

    public function edit(PhieuLuong $phieuLuong): View
    {
        $phieuLuong->load(['nguoiDung', 'bangLuong']);

        return view('admin_xem_truoc.nhan-su.phieu-luong.edit', compact('phieuLuong'));
    }

    public function update(UpdatePhieuLuongRequest $request, PhieuLuong $phieuLuong): RedirectResponse
    {
        $phieuLuong->update($request->validated());

        return redirect()
            ->route('phieu-luong.show', $phieuLuong)
            ->with('success', 'Đã cập nhật phiếu lương.');
    }
}
