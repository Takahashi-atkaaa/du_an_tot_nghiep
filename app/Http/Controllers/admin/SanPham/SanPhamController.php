<?php

namespace App\Http\Controllers\Admin\SanPham;

use App\Http\Controllers\Controller;
use App\Models\DanhMucSanPham;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function show($id)
    {
        $sanPham = SanPham::with(['danhMuc', 'donVi', 'thuocTinh'])->findOrFail($id);

        $theKho = DB::table('chi_tiet_phieu')
            ->join('phieu', 'chi_tiet_phieu.id_phieu', '=', 'phieu.id')
            ->leftJoin('nha_cung_cap', 'phieu.id_nha_cung_cap', '=', 'nha_cung_cap.id')
            ->where('chi_tiet_phieu.id_san_pham', $id)
            ->select(
                'phieu.id as ma_phieu',
                'phieu.created_at as thoi_gian',
                'phieu.loai_phieu',
                'nha_cung_cap.ten_nha_cung_cap as nha_cung_cap',
                'chi_tiet_phieu.gia_nhap as gia',
                'chi_tiet_phieu.so_luong as so_luong'
            )
            ->orderByDesc('phieu.created_at')
            ->get();

        $loHang = DB::table('chi_tiet_phieu')
            ->where('id_san_pham', $id)
            ->whereNotNull('ma_lo')
            ->select(
                'ma_lo',
                'han_su_dung',
                DB::raw('COALESCE(SUM(so_luong_con_lai), SUM(so_luong)) as so_luong')
            )
            ->groupBy('ma_lo', 'han_su_dung')
            ->orderBy('han_su_dung')
            ->get();

        return view('admin_xem_truoc.san-pham-chi-tiet', compact('sanPham', 'theKho', 'loHang'));
    }
}
