<?php

namespace App\Http\Controllers\Admin\SanPham;

use App\Http\Controllers\Controller;
use App\Models\DanhMucSanPham;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SanPhamController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $danhMucId = $request->input('danh_muc');
        $trangThai = $request->filled('trang_thai') ? $request->boolean('trang_thai') : null;

        $danhMucs = DanhMucSanPham::query()
            ->orderBy('ten_danh_muc')
            ->get();

        $sanPhams = SanPham::query()
            ->with('danhMuc')
            ->when($keyword, function ($query, $keyword) {
                $query->searchByFields($keyword, ['ten_san_pham', 'ma_vach', 'thuong_hieu']);
            })
            ->when($danhMucId, function ($query, $danhMucId) {
                $query->where('id_danh_muc', $danhMucId);
            })
            ->when(! is_null($trangThai), function ($query) use ($trangThai) {
                $query->where('trang_thai', $trangThai);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin_xem_truoc.san-pham', [
            'sanPhams' => $sanPhams,
            'danhMucs' => $danhMucs,
            'keyword' => $keyword,
            'danhMucId' => $danhMucId,
            'trangThai' => $request->input('trang_thai'),
        ]);
    }
}
