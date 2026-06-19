<?php

namespace App\Http\Controllers\admin\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SanPhamApiController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $sanPham = SanPham::with([
            'danhMuc',
            'donVi',
            'thuocTinhs',
            'bienThe.thuocTinhs',
        ])->find($id);

        if (!$sanPham) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
        }

        $theKho = DB::table('phieu')
            ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
            ->where('chi_tiet_phieu.id_san_pham', $id)
            ->select(
                DB::raw("CONCAT('PN-', phieu.id) as maPhieu"),
                'phieu.created_at as thoiGian',
                'phieu.loai_phieu as loaiPhieu',
                'chi_tiet_phieu.gia_nhap as gia',
                'chi_tiet_phieu.so_luong as soLuong',
                'chi_tiet_phieu.so_luong_con_lai as soLuongConLai',
                'chi_tiet_phieu.ma_lo as maLo',
                'chi_tiet_phieu.han_su_dung as hanSuDung'
            )
            ->orderByDesc('phieu.created_at')
            ->limit(20)
            ->get();

        $loHang = DB::table('chi_tiet_phieu')
            ->where('id_san_pham', $id)
            ->whereNotNull('ma_lo')
            ->select(
                'ma_lo as maLo',
                'han_su_dung as hanSuDung',
                'so_luong',
                'so_luong_con_lai as soLuongConLai'
            )
            ->groupBy('ma_lo', 'han_su_dung', 'so_luong', 'so_luong_con_lai')
            ->orderBy('han_su_dung')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sanPham' => $sanPham,
                'bienThe' => $sanPham->bienThe,
                'theKho' => $theKho,
                'loHang' => $loHang,
            ],
        ]);
    }
}
