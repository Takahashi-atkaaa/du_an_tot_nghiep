<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Requests\NhanSu\CapNhatNhanVienRequest;
use App\Requests\NhanSu\ThemNhanVienRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class NguoiDungController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $vaiTro = $request->input('vai_tro');
        $trangThai = $request->filled('trang_thai') ? $request->boolean('trang_thai') : null;

        $tongNhanVien = NguoiDung::withTrashed()->count();
        $dangLamViec = NguoiDung::query()->where('trang_thai', true)->count();
        $nghiPhep = NguoiDung::query()->where('trang_thai', false)->count();
        $daNghiViec = NguoiDung::onlyTrashed()->count();

        $nguoiDungs = NguoiDung::query()
            ->search($keyword)
            ->when($vaiTro, function ($query, $vaiTro) {
                $query->where('vai_tro', $vaiTro);
            })
            ->when(! is_null($trangThai), function ($query) use ($trangThai) {
                $query->where('trang_thai', $trangThai);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin_xem_truoc.nhan-su.index', [
            'nguoiDungs' => $nguoiDungs,
            'keyword' => $keyword,
            'vaiTro' => $vaiTro,
            'trangThai' => $request->input('trang_thai'),
            'tongNhanVien' => $tongNhanVien,
            'dangLamViec' => $dangLamViec,
            'nghiPhep' => $nghiPhep,
            'daNghiViec' => $daNghiViec,
            'vaiTros' => [
                'Admin',
                'Nhân viên',
                'Trưởng ca',
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin_xem_truoc.nhan-su.create', [
            'nguoiDung' => new NguoiDung(),
            'vaiTros' => [
                'Admin',
                'Nhân viên',
                'Trưởng ca',
            ],
        ]);
    }

    public function store(ThemNhanVienRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        NguoiDung::create([
            'ho_ten' => $validated['ho_ten'],
            'email' => $validated['email'],
            'sdt' => $validated['sdt'],
            'mat_khau' => Hash::make($validated['mat_khau']),
            'vai_tro' => $validated['vai_tro'],
            'trang_thai' => $request->boolean('trang_thai'),
        ]);

        return redirect('/nguoi-dung')
            ->with('success', 'Đã thêm người dùng mới.');
    }

    public function edit(NguoiDung $nguoiDung): View
    {
        return view('admin_xem_truoc.nhan-su.edit', [
            'nguoiDung' => $nguoiDung,
            'vaiTros' => [
                'Admin',
                'Nhân viên',
                'Trưởng ca',
            ],
        ]);
    }

    public function update(CapNhatNhanVienRequest $request, NguoiDung $nguoiDung): RedirectResponse
    {
        $validated = $request->validated();

        $nguoiDung->update([
            'ho_ten' => $validated['ho_ten'],
            'email' => $validated['email'],
            'sdt' => $validated['sdt'],
            'vai_tro' => $validated['vai_tro'],
            'trang_thai' => $request->boolean('trang_thai'),
        ]);

        return redirect('/nguoi-dung')
            ->with('success', 'Đã cập nhật người dùng.');
    }
}