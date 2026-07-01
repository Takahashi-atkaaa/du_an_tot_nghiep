<?php

namespace App\Http\Controllers\admin\BanHang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HoaDonController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->select(
                'hoa_don.*',
                'khach_hang.ten_khach_hang',
                'nguoi_dung.ho_ten as ten_nhan_vien'
            )
            ->orderByDesc('hoa_don.id');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $keyword = $request->q;
                $id = preg_replace('/[^0-9]/', '', $keyword);

                if ($id !== '') {
                    $q->orWhere('hoa_don.id', $id);
                }

                $q->orWhere('khach_hang.ten_khach_hang', 'like', "%{$keyword}%")
                  ->orWhere('nguoi_dung.ho_ten', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('ngay')) {
            $query->whereDate('hoa_don.created_at', $request->ngay);
        }

        if ($request->filled('trang_thai')) {
            $query->where('hoa_don.trang_thai', $request->trang_thai);
        }

        if ($request->filled('phuong_thuc')) {
            $query->where('hoa_don.phuong_thuc_thanh_toan', $request->phuong_thuc);
        }

        $hoaDons = $query->paginate(10)->withQueryString();

        return view('admin_xem_truoc.hoa-don', compact('hoaDons'));
    }

    public function show($id)
    {
        $hoaDon = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->leftJoin('khuyen_mai', 'hoa_don.id_khuyen_mai', '=', 'khuyen_mai.id')
            ->select(
                'hoa_don.*',
                'khach_hang.ten_khach_hang',
                'khach_hang.so_dien_thoai',
                'nguoi_dung.ho_ten as ten_nhan_vien',
                'khuyen_mai.ten_chuong_trinh as ten_khuyen_mai'
            )
            ->where('hoa_don.id', $id)
            ->first();

        abort_if(!$hoaDon, 404);

        $chiTiet = DB::table('chi_tiet_hoa_don')
            ->join('san_pham', 'chi_tiet_hoa_don.id_san_pham', '=', 'san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'san_pham.ma_vach'
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        return view('admin_xem_truoc.hoa-don-chi-tiet', compact('hoaDon', 'chiTiet'));
    }
}