<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\ThietLapLuong;
use App\Models\VaiTro;
use App\Http\Requests\ThietLapLuong\StoreThietLapLuongRequest;
use App\Http\Requests\ThietLapLuong\UpdateThietLapLuongRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThietLapLuongController extends Controller
{
    public function index(Request $request): View
    {
        $query = ThietLapLuong::with('nguoiDung')->orderBy('created_at', 'desc');

        if ($request->filled('id_nguoi_dung')) {
            $query->where('id_nguoi_dung', $request->id_nguoi_dung);
        }

        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->whereHas('nguoiDung', function ($q) use ($keyword) {
                $q->where('ho_ten', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        $thietLapLuongs = $query->paginate(10);
        $nhanViens = NguoiDung::where('id_vai_tro', VaiTro::where('ten_vai_tro', 'Nhân viên')->first()?->id)->orderBy('ho_ten')->get();

        return view('admin_xem_truoc.nhan-su.thiet-lap-luong.index', compact('thietLapLuongs', 'nhanViens'));
    }

    public function create(): View
    {
        return view('admin_xem_truoc.nhan-su.thiet-lap-luong.create', [
            'thietLapLuong' => new ThietLapLuong(),
            'nhanViens' => NguoiDung::where('id_vai_tro', VaiTro::where('ten_vai_tro', 'Nhân viên')->first()?->id)->orderBy('ho_ten')->get(),
        ]);
    }

    public function store(StoreThietLapLuongRequest $request): RedirectResponse
    {
        ThietLapLuong::create($request->validated());

        return redirect()
            ->route('thiet-lap-luong.index')
            ->with('success', 'Đã tạo thiết lập lương mới.');
    }

    public function show(ThietLapLuong $thietLapLuong): View
    {
        $thietLapLuong->load('nguoiDung');

        return view('admin_xem_truoc.nhan-su.thiet-lap-luong.show', compact('thietLapLuong'));
    }

    public function edit(ThietLapLuong $thietLapLuong): View
    {
        $thietLapLuong->load('nguoiDung');
        $nhanViens = NguoiDung::where('id_vai_tro', VaiTro::where('ten_vai_tro', 'Nhân viên')->first()?->id)->orderBy('ho_ten')->get();

        return view('admin_xem_truoc.nhan-su.thiet-lap-luong.edit', compact('thietLapLuong', 'nhanViens'));
    }

    public function update(UpdateThietLapLuongRequest $request, ThietLapLuong $thietLapLuong): RedirectResponse
    {
        $thietLapLuong->update($request->validated());

        return redirect()
            ->route('thiet-lap-luong.index')
            ->with('success', 'Đã cập nhật thiết lập lương.');
    }

    public function destroy(ThietLapLuong $thietLapLuong): RedirectResponse
    {
        $thietLapLuong->delete();

        return redirect()
            ->route('thiet-lap-luong.index')
            ->with('success', 'Đã xóa thiết lập lương.');
    }
}
