<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KhuyenMai;

class KhuyenMaiController extends Controller
{
    public function index(Request $request)
    {
        $items = KhuyenMai::orderBy('created_at', 'desc')->paginate(12);
        return view('admin_xem_truoc.khuyen-mai', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ten_chuong_trinh' => 'required|string|max:255',
            'loai_giam_gia' => 'required|string|max:50',
            'gia_tri_giam' => 'required|numeric',
            'giam_toi_da' => 'nullable|numeric',
            'so_luong_sp_toi_thieu' => 'nullable|integer',
            'don_hang_toi_thieu' => 'nullable|numeric',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_bat_dau',
            'trang_thai' => 'sometimes|boolean',
            'ghi_chu' => 'nullable|string',
        ]);

        $promo = KhuyenMai::create($data);

        return redirect()->back()->with('success', 'Tạo chương trình khuyến mãi thành công');
    }
}
